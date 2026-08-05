<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\Lesson;
use App\Services\AiCurriculumService;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function __construct(private AiCurriculumService $ai) {}

    private function authorizeTeacher(Course $course): void
    {
        abort_unless(auth()->user()->canManageCourse($course), 403);
    }

    public function generateLesson(Request $request, Course $course)
    {
        $this->authorizeTeacher($course);

        $validated = $request->validate([
            'topic' => 'required|string|max:255',
        ]);

        try {
            $result = $this->ai->generateLesson(
                $course->subject ?? $course->title,
                $course->grade_level ?? 'มัธยมศึกษา',
                $validated['topic'],
            );

            $order = ($course->lessons()->max('order') ?? 0) + 1;

            $lesson = $course->lessons()->create([
                'title' => $result['title'] ?? $validated['topic'],
                'content' => $result['content'] ?? '',
                'order' => $order,
                'ai_generated' => true,
            ]);

            return redirect()->route('admin.lessons.show', $lesson)
                ->with('success', 'AI สร้างบทเรียนสำเร็จ! ตรวจสอบเนื้อหาก่อนเผยแพร่');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function generateExam(Request $request, Course $course)
    {
        $this->authorizeTeacher($course);

        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'question_count' => 'required|integer|min:3|max:15',
        ]);

        try {
            $result = $this->ai->generateExam(
                $course->subject ?? $course->title,
                $course->grade_level ?? 'มัธยมศึกษา',
                $validated['topic'],
                $validated['question_count'],
            );

            $exam = $course->exams()->create([
                'title' => $result['title'] ?? 'ข้อสอบ: ' . $validated['topic'],
                'description' => $result['description'] ?? null,
                'type' => 'multiple_choice',
                'ai_generated' => true,
                'is_published' => false,
                'time_limit_minutes' => max(30, $validated['question_count'] * 3),
            ]);

            $added = $this->saveQuestions($exam, $result['questions'] ?? []);

            return redirect()->route('admin.exams.show', $exam)
                ->with('success', "AI สร้างข้อสอบ {$added} ข้อสำเร็จ! กด 'สร้างเพิ่ม' ได้เรื่อยๆ หรือเปิดให้นักเรียนทำ");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function appendQuestions(Request $request, Exam $exam)
    {
        abort_unless(auth()->user()->canManageCourse($exam->course), 403);

        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'question_count' => 'required|integer|min:1|max:10',
        ]);

        $course = $exam->course;

        try {
            $existing = $exam->questions()->pluck('question_text')->toArray();

            $result = $this->ai->generateMoreQuestions(
                $course->subject ?? $course->title,
                $course->grade_level ?? 'มัธยมศึกษา',
                $validated['topic'],
                $validated['question_count'],
                $existing,
            );

            $added = $this->saveQuestions($exam, $result['questions'] ?? [], $exam->questions()->max('order') ?? 0);

            return back()->with('success', "AI สร้างคำถามเพิ่ม {$added} ข้อ (รวม {$exam->questions()->count()} ข้อ)");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    private function saveQuestions(Exam $exam, array $questions, int $startOrder = 0): int
    {
        $added = 0;

        foreach ($questions as $index => $q) {
            if (empty($q['question_text'])) {
                continue;
            }

            $question = $exam->questions()->create([
                'question_text' => $q['question_text'],
                'type' => $q['type'] ?? 'multiple_choice',
                'correct_answer' => $q['correct_answer'] ?? null,
                'explanation' => $q['explanation'] ?? null,
                'order' => $startOrder + $index + 1,
            ]);

            if (($q['type'] ?? 'multiple_choice') === 'multiple_choice') {
                foreach ($q['options'] ?? [] as $opt) {
                    if (empty($opt['text'])) {
                        continue;
                    }
                    $question->options()->create([
                        'option_text' => $opt['text'],
                        'is_correct' => (bool) ($opt['is_correct'] ?? false),
                    ]);
                }
            }

            $added++;
        }

        return $added;
    }
}
