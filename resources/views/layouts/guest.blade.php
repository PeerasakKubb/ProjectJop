<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Smart Classroom') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=sarabun:300,400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-200">
        <div class="min-h-screen flex relative">
            @include('layouts.classroom-wallpaper')

            <div class="hidden lg:flex lg:w-1/2 relative z-10 items-center justify-center p-12 border-r border-gold/30">
                <div class="max-w-md">
                    <div class="w-16 h-16 flex items-center justify-center text-xl font-bold text-gold mb-8 border-2 border-gold">SC</div>
                    <h1 class="text-5xl font-semibold text-white leading-tight mb-4">
                        Smart Classroom
                    </h1>
                    <p class="text-slate-400 text-lg leading-relaxed">ระบบห้องเรียนอัจฉริยะ — RFID, IoT, เซนเซอร์, แจ้งเตือน, ข้อสอบ</p>
                    <div class="mt-8 flex flex-wrap gap-2">
                        @foreach (['RFID', 'IoT', 'MQTT', 'Telegram', 'LINE'] as $tag)
                            <span class="badge-brand text-xs">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex-1 relative z-10 flex flex-col justify-center items-center px-6 py-12">
                <div class="lg:hidden mb-8 text-center">
                    <div class="w-12 h-12 flex items-center justify-center text-gold font-bold mx-auto mb-3 border border-gold">SC</div>
                    <h1 class="text-2xl font-semibold text-white">Smart Classroom</h1>
                </div>

                <div class="w-full sm:max-w-md">
                    <div class="app-card-solid p-8">
                        {{ $slot }}
                    </div>
                    <p class="text-center text-sm text-slate-500 mt-6">
                        <a href="{{ route('home') }}" class="hover:text-gold transition-colors">← กลับหน้าบ้าน</a>
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
