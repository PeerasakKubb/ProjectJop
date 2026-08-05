<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Services\AiCurriculumService;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function __construct(private AiCurriculumService $ai) {}

    public function index(Course $course)
    {
        $exams = $course->exams()->withCount('questions')->get();

        return view('exams.index', compact('course', 'exams'));
    }

    public function create(Course $course)
    {
        return view('exams.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:multiple_choice,analytical',
            'time_limit_minutes' => 'nullable|integer|min:1',
        ]);

        $validated['is_published'] = $request->boolean('is_published');

        $exam = $course->exams()->create($validated);

        return redirect()->route('admin.exams.show', $exam)->with('success', 'สร้างข้อสอบสำเร็จ');
    }

    public function show(Exam $exam)
    {
        $exam->load(['course', 'questions.options']);

        return view('exams.show', compact('exam'));
    }

    public function take(Exam $exam)
    {
        if (! $exam->is_published) {
            abort(403, 'ข้อสอบยังไม่เปิดให้ทำ');
        }

        $exam->load('questions.options');

        $attempt = ExamAttempt::where('user_id', auth()->id())
            ->where('exam_id', $exam->id)
            ->whereNull('submitted_at')
            ->first();

        if (! $attempt) {
            $attempt = ExamAttempt::create([
                'user_id' => auth()->id(),
                'exam_id' => $exam->id,
                'started_at' => now(),
            ]);
        }

        return view('exams.take', compact('exam', 'attempt'));
    }

    public function submit(Request $request, Exam $exam)
    {
        $attempt = ExamAttempt::where('user_id', auth()->id())
            ->where('exam_id', $exam->id)
            ->whereNull('submitted_at')
            ->firstOrFail();

        $answers = $request->input('answers', []);
        $correct = 0;
        $total = $exam->questions->count();

        foreach ($exam->questions as $question) {
            $answerText = $answers[$question->id] ?? '';
            $isCorrect = false;

            if ($question->type === 'multiple_choice') {
                $correctOption = $question->options->firstWhere('is_correct', true);
                $isCorrect = $correctOption && $correctOption->id == $answerText;
                $feedback = null;
            } else {
                $grade = $this->ai->gradeAnalyticalAnswer(
                    $question->question_text,
                    $question->correct_answer ?? '',
                    is_string($answerText) ? $answerText : ''
                );
                $isCorrect = $grade['is_correct'];
                $feedback = $grade['feedback'];
            }

            if ($isCorrect) {
                $correct++;
            }

            $attempt->answers()->create([
                'question_id' => $question->id,
                'answer_text' => is_string($answerText) ? $answerText : (string) $answerText,
                'is_correct' => $isCorrect,
                'ai_feedback' => $feedback,
            ]);
        }

        $score = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

        $attempt->update([
            'score' => $score,
            'submitted_at' => now(),
        ]);

        return redirect()->route('admin.exams.result', $attempt)->with('success', 'ส่งข้อสอบแล้ว');
    }

    public function result(ExamAttempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);

        $attempt->load(['exam.questions.options', 'answers']);

        return view('exams.result', compact('attempt'));
    }

    public function storeQuestion(Request $request, Exam $exam)
    {
        abort_unless(auth()->user()->canManageCourse($exam->course), 403);

        $validated = $request->validate([
            'question_text' => 'required|string',
            'type' => 'required|in:multiple_choice,analytical',
            'explanation' => 'nullable|string',
            'correct_answer' => 'nullable|string',
            'options' => 'required_if:type,multiple_choice|array|min:2',
            'options.*' => 'required|string',
            'correct_option' => 'required_if:type,multiple_choice|integer|min:0',
        ]);

        $order = $exam->questions()->max('order') + 1;

        $question = $exam->questions()->create([
            'question_text' => $validated['question_text'],
            'type' => $validated['type'],
            'explanation' => $validated['explanation'] ?? null,
            'correct_answer' => $validated['correct_answer'] ?? null,
            'order' => $order,
        ]);

        if ($validated['type'] === 'multiple_choice') {
            foreach ($validated['options'] as $index => $optionText) {
                $question->options()->create([
                    'option_text' => $optionText,
                    'is_correct' => $index == $validated['correct_option'],
                ]);
            }
        }

        return back()->with('success', 'เพิ่มคำถามสำเร็จ');
    }

    public function destroyQuestion(Question $question)
    {
        abort_unless(auth()->user()->canManageCourse($question->exam->course), 403);

        $exam = $question->exam;
        $question->delete();

        return redirect()->route('admin.exams.show', $exam)->with('success', 'ลบคำถามแล้ว');
    }

    public function togglePublish(Exam $exam)
    {
        abort_unless(auth()->user()->canManageCourse($exam->course), 403);

        $exam->update(['is_published' => ! $exam->is_published]);

        return back()->with('success', $exam->is_published ? 'เปิดให้นักเรียนทำข้อสอบแล้ว' : 'ปิดข้อสอบแล้ว');
    }
}
