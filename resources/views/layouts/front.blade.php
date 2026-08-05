<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Smart Classroom'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased overflow-x-hidden text-slate-200">
    @include('layouts.classroom-wallpaper')

    <div class="relative z-10 min-h-screen flex flex-col" x-data="{ mobileOpen: false }">
        <nav class="glass-nav max-w-6xl mx-auto w-[calc(100%-1.5rem)] sm:w-[calc(100%-3rem)] px-4 sm:px-6 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-white shadow-glow text-sm"
                             style="background: linear-gradient(135deg, #7c3aed, #22d3ee);">SC</div>
                        <span class="font-bold text-lg text-white hidden sm:block">Smart <span class="text-gradient">Classroom</span></span>
                    </a>

                    <div class="hidden md:flex items-center gap-1">
                        @foreach ([
                            ['home', 'หน้าแรก'],
                            ['courses.public', 'คอร์สเรียน'],
                            ['features', 'ฟีเจอร์'],
                            ['about', 'เกี่ยวกับ'],
                        ] as [$route, $label])
                            <a href="{{ route($route) }}"
                               class="px-3 py-2 text-sm font-semibold rounded-lg transition-colors {{ request()->routeIs($route) || request()->routeIs(str_replace('.public', '.public.*', $route)) ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white' }}">
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
                    <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-lg text-slate-400 hover:bg-white/5" aria-label="เมนู">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div x-show="mobileOpen" x-transition class="md:hidden pt-3 pb-1 border-t border-white/5 mt-3 space-y-1">
                @foreach ([
                    ['home', 'หน้าแรก'],
                    ['courses.public', 'คอร์สเรียน'],
                    ['features', 'ฟีเจอร์'],
                    ['about', 'เกี่ยวกับ'],
                ] as [$route, $label])
                    <a href="{{ route($route) }}" class="block px-3 py-2 text-sm font-semibold text-slate-300 hover:text-white rounded-lg">{{ $label }}</a>
                @endforeach
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 text-sm font-bold text-violet-300">เข้าหลังบ้าน →</a>
                @else
                    <a href="{{ route('login') }}" class="block px-3 py-2 text-sm text-slate-300">เข้าสู่ระบบ</a>
                    <a href="{{ route('register') }}" class="block px-3 py-2 text-sm text-cyan-400">สมัครสมาชิก</a>
                @endauth
            </div>
        </nav>

        <main class="flex-1">
            @yield('content')
        </main>

        <footer class="border-t border-white/5 py-8 text-center text-sm text-slate-600">
            {{ $site['school_name'] ?? 'Smart Classroom Platform' }}
            <span class="mx-2 text-slate-700">·</span>
            <a href="{{ route('home') }}" class="hover:text-cyan-400 transition-colors">หน้าบ้าน</a>
            @auth
                <span class="mx-2 text-slate-700">·</span>
                <a href="{{ route('admin.dashboard') }}" class="hover:text-cyan-400 transition-colors">หลังบ้าน</a>
            @endauth
        </footer>
    </div>
</body>
</html>
