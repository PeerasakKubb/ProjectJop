@extends('layouts.front')

@section('title', $course->title . ' — Smart Classroom')

@section('content')
    <section class="max-w-4xl mx-auto px-6 pt-16 pb-24">
        <a href="{{ route('courses.public') }}" class="text-sm text-slate-500 hover:text-cyan-400 mb-6 inline-block">← กลับรายการคอร์ส</a>

        <div class="app-card p-6 sm:p-8 mb-6">
            @if ($course->subject)
                <span class="badge-brand mb-3">{{ $course->subject }} · {{ $course->grade_level }}</span>
            @endif
            <h1 class="text-3xl sm:text-4xl font-black text-white mb-4">{{ $course->title }}</h1>
            <p class="text-slate-300 leading-relaxed mb-6">{{ $course->description }}</p>

            <div class="flex flex-wrap gap-4 text-sm text-slate-400 mb-6">
                <span>👨‍🏫 {{ $course->teacher->name }}</span>
                <span>📖 {{ $course->lessons->count() }} บทเรียน</span>
                <span class="font-bold text-violet-400 text-base">
                    {{ $course->price == 0 ? 'ฟรี' : number_format($course->price).' บาท' }}
                </span>
            </div>

            @guest
                <a href="{{ route('login') }}" class="btn-brand">เข้าสู่ระบบเพื่อลงทะเบียน →</a>
            @else
                <a href="{{ route('admin.courses.show', $course) }}" class="btn-brand">ไปเรียนคอร์สนี้ →</a>
            @endguest
        </div>

        @if ($course->lessons->isNotEmpty())
            <div class="app-card p-6">
                <h2 class="text-lg font-bold text-white mb-4">บทเรียนในคอร์ส</h2>
                <ul class="space-y-2">
                    @foreach ($course->lessons as $lesson)
                        <li class="flex items-center gap-3 p-3 rounded-xl bg-white/5 text-sm">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold text-white"
                                  style="background: linear-gradient(135deg, #7c3aed, #22d3ee);">{{ $loop->iteration }}</span>
                            <span class="text-slate-300">{{ $lesson->title }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>
@endsection
