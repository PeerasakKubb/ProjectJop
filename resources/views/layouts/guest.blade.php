<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Smart Classroom') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-200">
        <div class="min-h-screen flex relative">
            @include('layouts.classroom-wallpaper')

            {{-- Left branding --}}
            <div class="hidden lg:flex lg:w-1/2 relative z-10 items-center justify-center p-12">
                <div class="max-w-md">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-black text-white mb-8 shadow-glow"
                         style="background: linear-gradient(135deg, #7c3aed, #22d3ee);">SC</div>
                    <h1 class="text-5xl font-black text-white leading-tight mb-4">
                        Smart<br><span class="text-gradient">Classroom</span>
                    </h1>
                    <p class="text-slate-400 text-lg leading-relaxed">ระบบห้องเรียนอัจฉริยะ — RFID, IoT, เซนเซอร์, แจ้งเตือน, ข้อสอบ</p>
                    <div class="mt-8 flex flex-wrap gap-2">
                        @foreach (['RFID', 'IoT', 'MQTT', 'Telegram', 'LINE'] as $tag)
                            <span class="badge-brand text-xs">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Right form --}}
            <div class="flex-1 relative z-10 flex flex-col justify-center items-center px-6 py-12">
                <div class="lg:hidden mb-8 text-center">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-black mx-auto mb-3 shadow-glow"
                         style="background: linear-gradient(135deg, #7c3aed, #22d3ee);">SC</div>
                    <h1 class="text-2xl font-bold text-white">Smart <span class="text-gradient">Classroom</span></h1>
                </div>

                <div class="w-full sm:max-w-md">
                    <div class="app-card-solid p-8">
                        {{ $slot }}
                    </div>
                    <p class="text-center text-sm text-slate-500 mt-6">
                        <a href="{{ route('home') }}" class="hover:text-cyan-400 transition-colors">← กลับหน้าบ้าน</a>
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
