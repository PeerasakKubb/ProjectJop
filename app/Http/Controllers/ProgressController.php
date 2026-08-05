<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\LessonProgress;

class ProgressController extends Controller
{
    public function index(Course $course)
    {
        abort_unless(auth()->user()->canManageCourse($course), 403);

        $course->load(['lessons', 'enrollments.user']);

        $studentIds = $course->enrollments->pluck('user_id');
        $lessonIds = $course->lessons->pluck('id');

        $progressData = LessonProgress::whereIn('user_id', $studentIds)
            ->whereIn('lesson_id', $lessonIds)
            ->get()
            ->groupBy('user_id');

        $students = $course->enrollments->map(function ($enrollment) use ($course, $progressData) {
            $user = $enrollment->user;
            $userProgress = $progressData->get($user->id, collect());

            $lessonStats = $course->lessons->map(function ($lesson) use ($userProgress) {
                $p = $userProgress->firstWhere('lesson_id', $lesson->id);

                return [
                    'lesson' => $lesson,
                    'percent' => $p?->progress_percent ?? 0,
                    'last_viewed' => $p?->last_viewed_at,
                ];
            });

            $avg = $lessonStats->avg('percent') ?? 0;

            return [
                'user' => $user,
                'lessons' => $lessonStats,
                'average' => round($avg),
            ];
        });

        return view('progress.index', compact('course', 'students'));
    }
}
