<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function create(Course $course)
    {
        abort_unless(auth()->id() === $course->teacher_id, 403);

        return view('lessons.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        abort_unless(auth()->id() === $course->teacher_id, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['order'] = $validated['order'] ?? ($course->lessons()->max('order') + 1);

        $course->lessons()->create($validated);

        return redirect()->route('admin.courses.show', $course)->with('success', 'เพิ่มบทเรียนสำเร็จ');
    }

    public function show(Lesson $lesson)
    {
        $lesson->load('course');

        $progress = null;
        if (auth()->check()) {
            $progress = LessonProgress::firstOrCreate(
                ['user_id' => auth()->id(), 'lesson_id' => $lesson->id],
                ['progress_percent' => 0]
            );
        }

        return view('lessons.show', compact('lesson', 'progress'));
    }

    public function updateProgress(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'progress_percent' => 'required|integer|min:0|max:100',
        ]);

        LessonProgress::updateOrCreate(
            ['user_id' => auth()->id(), 'lesson_id' => $lesson->id],
            [
                'progress_percent' => $validated['progress_percent'],
                'last_viewed_at' => now(),
            ]
        );

        return response()->json(['success' => true]);
    }

    public function destroy(Lesson $lesson)
    {
        abort_unless(auth()->id() === $lesson->course->teacher_id, 403);

        $course = $lesson->course;
        $lesson->delete();

        return redirect()->route('admin.courses.show', $course)->with('success', 'ลบบทเรียนแล้ว');
    }
}
