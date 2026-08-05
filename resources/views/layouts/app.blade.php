<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Smart Classroom') }} · หลังบ้าน</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans">
        <div class="app-shell">
            @include('layouts.classroom-wallpaper')

            <div class="app-shell__layout">
                @auth
                    @include('layouts.sidebar')
                @endauth

                <div class="app-shell__main">
                    @guest
                        @include('layouts.navigation')
                    @endguest

                    @isset($header)
                        <header class="page-header">
                            <div class="page-content py-5 animate-fade-in">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <main class="animate-fade-in">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
