@extends('layouts.front')

@section('title', 'ฟีเจอร์ — Smart Classroom')

@section('content')
    <section class="max-w-6xl mx-auto px-6 pt-16 pb-10 text-center">
        <p class="text-xs font-bold uppercase tracking-widest text-gold mb-2">Features</p>
        <h1 class="text-4xl sm:text-5xl font-semibold text-white mb-4">
            ฟีเจอร์ระบบ
        </h1>
        <p class="text-slate-400 max-w-2xl mx-auto">
            หน้าบ้านนี้แสดงภาพรวมโมดูล — การใช้งานจริงอยู่ที่หลังบ้านหลังเข้าสู่ระบบ
        </p>
    </section>

    <section class="max-w-6xl mx-auto px-6 pb-12">
        <x-system-diagram :modules="$modules" :layers="$layers" class="app-card p-6 lg:p-8" />
    </section>

    <section class="max-w-6xl mx-auto px-6 pb-24">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($modules as $module)
                @if (($module['key'] ?? '') === 'architecture')
                    @continue
                @endif
                <div class="app-card p-5">
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-slate-500 mb-1">
                            {{ $module['layer_meta']['label'] ?? '' }}
                        </p>
                        <h3 class="font-semibold text-white">{{ $module['label'] }}</h3>
                        <p class="text-sm text-slate-400 mt-1">{{ $module['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-12">
            @auth
                <a href="{{ route('admin.dashboard') }}" class="btn-brand">เข้าหลังบ้านเพื่อใช้งาน →</a>
            @else
                <a href="{{ route('login') }}" class="btn-brand">เข้าสู่ระบบเพื่อใช้งาน →</a>
            @endauth
        </div>
    </section>
@endsection
