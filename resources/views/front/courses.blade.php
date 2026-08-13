@extends('layouts.front')

@section('title', 'คอร์สเรียน — Smart Classroom')

@section('content')
    <section class="max-w-6xl mx-auto px-6 pt-16 pb-8">
        <p class="text-xs font-bold uppercase tracking-widest text-gold mb-2">Course Catalog</p>
        <h1 class="text-4xl font-semibold text-white mb-2">คอร์สเรียน</h1>
        <p class="text-slate-400">เลือกคอร์สที่สนใจ แล้วลงทะเบียนเพื่อเริ่มเรียน</p>
    </section>

    <section class="max-w-6xl mx-auto px-6 pb-24">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($courses as $course)
                <div class="app-card group overflow-hidden flex flex-col">
                    <div class="h-1 bg-gold"></div>
                    <div class="p-6 flex flex-col flex-1">
                        @if ($course->subject)
                            <span class="badge-brand text-[10px] self-start mb-2">{{ $course->subject }} · {{ $course->grade_level }}</span>
                        @endif
                        <h3 class="text-lg font-semibold text-white group-hover:text-gold transition-colors mb-2">{{ $course->title }}</h3>
                        <p class="text-slate-400 text-sm mb-4 flex-1">{{ Str::limit($course->description, 100) }}</p>
                        <div class="flex flex-wrap gap-3 text-xs text-slate-500 mb-4">
                            <span>{{ $course->teacher->name }}</span>
                            <span>{{ $course->lessons_count }} บท</span>
                            <span>{{ $course->enrollments_count }} คน</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gold text-lg">
                                {{ $course->price == 0 ? 'ฟรี' : number_format($course->price).' บาท' }}
                            </span>
                            <a href="{{ route('courses.public.show', $course) }}" class="btn-brand text-sm py-2 px-4">ดูรายละเอียด</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 app-card p-16 text-center">
                    <p class="text-slate-400">ยังไม่มีคอร์สเปิดสอน</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
