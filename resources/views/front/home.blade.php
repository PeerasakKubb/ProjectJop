@extends('layouts.front')

@section('title', 'Smart Classroom — ระบบห้องเรียนอัจฉริยะ')

@section('content')
    <section class="max-w-6xl mx-auto px-6 pt-16 sm:pt-20 pb-12 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold uppercase tracking-widest mb-8"
             style="background: rgba(196,163,90,0.12); border: 1px solid rgba(196,163,90,0.4); color: #e6d5a8;">
            <span class="w-2 h-2 rounded-full bg-gold"></span>
            {{ $site['hero_badge'] ?? 'IoT · RFID · AI Ready' }}
        </div>

        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-semibold leading-[1.1] mb-6 text-white">
            {{ $site['hero_title_1'] ?? 'ห้องเรียน' }}
            {{ $site['hero_title_highlight'] ?? 'อัจฉริยะ' }}
            {{ $site['hero_title_2'] ?? 'ยุคใหม่' }}
        </h1>

        <p class="text-lg sm:text-xl text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed">
            {{ $site['hero_subtitle'] ?? 'เช็คชื่อ RFID · ควบคุม IoT · เซนเซอร์ · แจ้งเตือน · LMS' }}
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            @auth
                <a href="{{ route('admin.dashboard') }}" class="btn-brand px-10 py-4 text-base">เข้าหลังบ้าน →</a>
                <a href="{{ route('courses.public') }}" class="btn-outline px-10 py-4 text-base">ดูคอร์สเรียน</a>
            @else
                <a href="{{ route('register') }}" class="btn-brand px-10 py-4 text-base">เริ่มเรียนฟรี →</a>
                <a href="{{ route('courses.public') }}" class="btn-outline px-10 py-4 text-base">ดูคอร์สเรียน</a>
            @endauth
        </div>
    </section>

    <section class="max-w-6xl mx-auto px-6 pb-16">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ([
                [$stats['students'], 'นักเรียน', $site['stat_students'] ?? null],
                [$stats['courses'], 'คอร์สเปิดสอน', $site['stat_courses'] ?? null],
                [$stats['teachers'], 'ครูผู้สอน', $site['stat_teachers'] ?? null],
                [$site['stat_satisfaction'] ?? '98%', 'ความพึงพอใจ', null],
            ] as [$live, $label, $display])
                <div class="app-card p-5 text-center">
                    <p class="text-3xl font-semibold text-white">{{ $display ?? $live }}</p>
                    <p class="text-sm text-slate-500 mt-1">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </section>

    @if ($featuredCourses->isNotEmpty())
        <section class="max-w-6xl mx-auto px-6 pb-20">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-gold mb-1">Courses</p>
                    <h2 class="text-3xl font-semibold text-white">คอร์สแนะนำ</h2>
                </div>
                <a href="{{ route('courses.public') }}" class="btn-ghost">ดูทั้งหมด →</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @foreach ($featuredCourses as $course)
                    <a href="{{ route('courses.public.show', $course) }}" class="app-card group overflow-hidden block">
                        <div class="h-1 bg-gold"></div>
                        <div class="p-6">
                            @if ($course->subject)
                                <span class="badge-brand text-[10px] mb-2">{{ $course->subject }} · {{ $course->grade_level }}</span>
                            @endif
                            <h3 class="font-semibold text-lg text-white group-hover:text-gold transition-colors mb-2">{{ $course->title }}</h3>
                            <p class="text-sm text-slate-400 line-clamp-2 mb-4">{{ Str::limit($course->description, 80) }}</p>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-500">{{ $course->teacher->name }}</span>
                                <span class="font-bold text-gold">{{ $course->price == 0 ? 'ฟรี' : number_format($course->price).' บ.' }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="max-w-6xl mx-auto px-6 pb-20">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-semibold text-white">ทำไมต้อง Smart Classroom</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ([
                ['เช็คชื่อ RFID', 'แตะบัตร → บันทึกอัตโนมัติ'],
                ['ควบคุม IoT', 'เว็บ · Telegram · LINE · MQTT'],
                ['แจ้งเตือนอัตโนมัติ', 'อุณหภูมิ · ขาดเรียน · ออฟไลน์'],
                ['เซนเซอร์ Real-time', 'กราฟสภาพแวดล้อม 24 ชม.'],
                ['LMS + ข้อสอบ', 'บทเรียน · ติดตามความคืบหน้า'],
                ['Dashboard ครบ', 'ภาพรวมทุกอย่างในที่เดียว'],
            ] as [$title, $desc])
                <div class="app-card p-6">
                    <h3 class="font-semibold text-white mb-1">{{ $title }}</h3>
                    <p class="text-sm text-slate-400">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="max-w-4xl mx-auto px-6 pb-24 text-center">
        <div class="app-card p-10 relative overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-2xl sm:text-3xl font-semibold text-white mb-3">พร้อมเริ่มเรียนแล้วหรือยัง?</h2>
                <p class="text-slate-400 mb-6">สมัครฟรี แล้วเข้าเรียนคอร์สได้ทันที</p>
                @auth
                    <a href="{{ route('admin.courses.index') }}" class="btn-brand px-8 py-3">ไปที่คอร์สของฉัน →</a>
                @else
                    <a href="{{ route('register') }}" class="btn-brand px-8 py-3">สมัครสมาชิกฟรี →</a>
                @endauth
            </div>
        </div>
    </section>
@endsection
