<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    // สมัครเรียนคอร์ส
    public function store(Course $course)
    {
        $exists = Enrollment::where('user_id', auth()->id())
            ->where('course_id', $course->id)
            ->exists();

        if ($exists) {
            return redirect()->route('admin.courses.show', $course)->with('error', 'คุณลงทะเบียนคอร์สนี้ไปแล้ว');
        }

        Enrollment::create([
            'user_id' => auth()->id(),
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        return redirect()->route('admin.courses.show', $course)->with('success', 'ลงทะเบียนเรียนสำเร็จ!');
    }

    // ยกเลิกการลงทะเบียน
    public function destroy(Course $course)
    {
        Enrollment::where('user_id', auth()->id())
            ->where('course_id', $course->id)
            ->delete();

        return redirect()->route('admin.courses.show', $course)->with('success', 'ยกเลิกการลงทะเบียนแล้ว');
    }
}