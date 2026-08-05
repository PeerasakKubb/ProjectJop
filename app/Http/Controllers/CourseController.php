<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        $query = Course::with('teacher')->withCount('enrollments')->latest();

        if (auth()->user()->isTeacher() && ! auth()->user()->isAdmin()) {
            $query->where('teacher_id', auth()->id());
        }

        $courses = $query->get();

        return view('courses.index', compact('courses'));
    }

    public function create(): View
    {
        return view('courses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'subject' => 'nullable|string|max:100',
            'grade_level' => 'nullable|string|max:50',
            'is_published' => 'boolean',
        ]);

        $validated['teacher_id'] = auth()->id();
        $validated['is_published'] = $request->boolean('is_published');

        Course::create($validated);

        return redirect()->route('admin.courses.index')->with('success', 'สร้างคอร์สสำเร็จ!');
    }

    public function show(Course $course): View
    {
        $course->load('lessons', 'teacher');

        $isEnrolled = auth()->user()->enrollments()
            ->where('course_id', $course->id)
            ->exists();

        return view('courses.show', compact('course', 'isEnrolled'));
    }

    public function edit(Course $course): View
    {
        abort_unless(auth()->user()->canManageCourse($course), 403);

        return view('courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        abort_unless(auth()->user()->canManageCourse($course), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'subject' => 'nullable|string|max:100',
            'grade_level' => 'nullable|string|max:50',
            'is_published' => 'boolean',
        ]);

        $validated['is_published'] = $request->boolean('is_published');

        $course->update($validated);

        return redirect()->route('admin.courses.show', $course)->with('success', 'อัปเดตคอร์สแล้ว');
    }

    public function destroy(Course $course): RedirectResponse
    {
        abort_unless(auth()->user()->canManageCourse($course), 403);

        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', 'ลบคอร์สแล้ว');
    }
}
