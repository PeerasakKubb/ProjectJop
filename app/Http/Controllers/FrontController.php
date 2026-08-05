<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Support\SiteContent;
use Illuminate\View\View;

class FrontController extends Controller
{
    public function home(): View
    {
        $featuredCourses = Course::query()
            ->where('is_published', true)
            ->with('teacher')
            ->withCount('enrollments')
            ->latest()
            ->limit(3)
            ->get();

        $stats = [
            'students' => User::where('role', 'student')->count(),
            'courses' => Course::where('is_published', true)->count(),
            'teachers' => User::where('role', 'teacher')->count(),
        ];

        return view('front.home', compact('featuredCourses', 'stats'));
    }

    public function features(): View
    {
        $guestModules = collect(config('smart_classroom.modules', []))
            ->map(fn (array $module, string $key) => array_merge($module, [
                'key' => $key,
                'layer_meta' => config('smart_classroom.layers.'.$module['layer'], []),
                'active' => false,
                'url' => '#',
            ]))
            ->all();

        return view('front.features', [
            'modules' => $guestModules,
            'layers' => config('smart_classroom.layers'),
        ]);
    }

    public function about(): View
    {
        return view('front.about');
    }

    public function courses(): View
    {
        $courses = Course::query()
            ->where('is_published', true)
            ->with('teacher')
            ->withCount(['lessons', 'enrollments'])
            ->latest()
            ->get();

        return view('front.courses', compact('courses'));
    }

    public function courseShow(Course $course): View
    {
        abort_unless($course->is_published, 404);

        $course->load(['teacher', 'lessons' => fn ($q) => $q->select('id', 'course_id', 'title', 'order')]);

        return view('front.course-show', compact('course'));
    }
}
