<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Smart Classroom'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sarabun:300,400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased overflow-x-hidden text-slate-200">
    @include('layouts.classroom-wallpaper')

    <div class="relative z-10 min-h-screen flex flex-col" x-data="{ mobileOpen: false }">
        <div class="h-1 bg-gold"></div>

        <nav class="glass-nav">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 h-[72px] flex items-center justify-between">
                <div class="flex items-center gap-6 min-w-0">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 flex items-center justify-center text-gold text-sm font-bold border border-gold shrink-0">SC</div>
                        <span class="font-semibold text-white hidden sm:block truncate">
                            Smart Classroom
                        </span>
                    </a>

                    <div class="hidden md:flex items-center gap-1">
                        @foreach ([
                            ['home', 'หน้าแรก'],
                            ['courses.public', 'คอร์สเรียน'],
                            ['features', 'ฟีเจอร์'],
                            ['about', 'เกี่ยวกับ'],
                        ] as [$route, $label])
                            <a href="{{ route($route) }}"
                               class="px-3 py-2 text-sm font-medium {{ request()->routeIs($route) || request()->routeIs(str_replace('.public', '.public.*', $route)) ? 'text-gold border-b-2 border-gold' : 'text-slate-400 hover:text-white' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="btn-brand text-sm hidden sm:inline-flex">หลังบ้าน →</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-400 hover:text-white px-3 py-2 hidden sm:block">เข้าสู่ระบบ</a>
                        <a href="{{ route('register') }}" class="btn-brand text-sm hidden sm:inline-flex">เริ่มเลย</a>
                    @endauth
                    <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 text-slate-400" aria-label="เมนู">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div x-show="mobileOpen" x-cloak x-transition class="md:hidden border-t border-white/10 px-4 py-3 space-y-1">
                @foreach ([
                    ['home', 'หน้าแรก'],
                    ['courses.public', 'คอร์สเรียน'],
                    ['features', 'ฟีเจอร์'],
                    ['about', 'เกี่ยวกับ'],
                ] as [$route, $label])
                    <a href="{{ route($route) }}" class="block px-3 py-2 text-sm font-semibold text-slate-300">{{ $label }}</a>
                @endforeach
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 text-sm font-bold text-gold">เข้าหลังบ้าน →</a>
                @else
                    <a href="{{ route('login') }}" class="block px-3 py-2 text-sm text-slate-300">เข้าสู่ระบบ</a>
                    <a href="{{ route('register') }}" class="block px-3 py-2 text-sm text-gold">สมัครสมาชิก</a>
                @endauth
            </div>
        </nav>

        <main class="flex-1">
            @yield('content')
        </main>

        <footer class="border-t border-gold/30 bg-[#071018]/90 py-8 text-center text-sm text-slate-500">
            {{ $site['school_name'] ?? 'Smart Classroom Platform' }}
            <span class="mx-2 text-slate-700">·</span>
            <a href="{{ route('home') }}" class="hover:text-gold transition-colors">หน้าบ้าน</a>
            @auth
                <span class="mx-2 text-slate-700">·</span>
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gold transition-colors">หลังบ้าน</a>
            @endauth
        </footer>
    </div>
    <style>[x-cloak]{display:none!important}</style>
</body>
</html>
