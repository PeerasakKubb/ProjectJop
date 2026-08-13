@extends('layouts.front')

@section('title', 'เกี่ยวกับ — Smart Classroom')

@section('content')
    <section class="max-w-3xl mx-auto px-6 pt-16 pb-24">
        <p class="text-xs font-bold uppercase tracking-widest text-gold mb-2">About</p>
        <h1 class="text-4xl sm:text-5xl font-semibold text-white mb-6">
            เกี่ยวกับ{{ $site['school_name'] ?? 'Smart Classroom' }}
        </h1>

        <div class="app-card p-6 sm:p-8 space-y-6 text-slate-300 leading-relaxed">
            <p>{{ $site['about_intro'] ?? 'Smart Classroom Platform เป็นระบบห้องเรียนอัจฉริยะ' }}</p>

            <div>
                <h2 class="text-lg font-semibold text-white mb-2">หน้าบ้าน vs หลังบ้าน</h2>
                <ul class="space-y-2 text-sm">
                    <li class="p-3 bg-white/5">
                        <strong class="text-white">หน้าบ้าน</strong> — โชว์คอร์ส ฟีเจอร์ ข้อมูลโรงเรียน (ไม่ต้อง login)
                    </li>
                    <li class="p-3 bg-white/5">
                        <strong class="text-white">หลังบ้าน (/admin)</strong> — Admin แก้ไขเว็บ จัดการผู้ใช้ คอร์ส IoT RFID
                    </li>
                </ul>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-white mb-2">ติดต่อ</h2>
                <div class="space-y-1 text-sm">
                    <p>อีเมล {{ $site['contact_email'] ?? 'admin@school.local' }}</p>
                    <p>โทรศัพท์ {{ $site['contact_phone'] ?? '02-xxx-xxxx' }}</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach (['Laravel', 'RFID', 'IoT', 'MQTT', 'Telegram', 'LINE', 'LMS'] as $tag)
                    <span class="badge-brand">{{ $tag }}</span>
                @endforeach
            </div>
        </div>

        <div class="flex flex-wrap gap-3 mt-8">
            <a href="{{ route('courses.public') }}" class="btn-outline">ดูคอร์สเรียน</a>
            @auth
                <a href="{{ route('admin.dashboard') }}" class="btn-brand">เข้าหลังบ้าน →</a>
            @else
                <a href="{{ route('login') }}" class="btn-brand">เข้าสู่ระบบ →</a>
            @endauth
        </div>
    </section>
@endsection
