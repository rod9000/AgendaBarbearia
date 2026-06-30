<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ dark: localStorage.getItem('dark') === 'true' }" x-init="() => { if (dark) { document.documentElement.classList.add('dark'); } $watch('dark', val => { localStorage.setItem('dark', val); document.documentElement.classList.toggle('dark', val); }); }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
        <link rel="stylesheet" href="{{ asset('css/app.css') . '?nocache=' . env('APP_VERSION', '1.0') }}">
        <style>[x-cloak]{display:none!important}</style>
        @stack('styles')
        <script src="{{ asset('js/app.js') . '?nocache=' . env('APP_VERSION', '1.0') }}" defer></script>
    </head>
    <body class="font-sans antialiased bg-white dark:bg-stone-900 dark:text-stone-200">
        <div class="min-h-screen bg-white dark:bg-stone-900">
            @include('layouts.navigation')

            @if (isset($header))
                <header class="bg-white/80 backdrop-blur-sm shadow-sm dark:bg-stone-800/80 dark:shadow-none">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            @hasSection('header')
                <header class="bg-white/80 backdrop-blur-sm shadow-sm dark:bg-stone-800/80 dark:shadow-none">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        @yield('header')
                    </div>
                </header>
            @endif

            <main>
                @if (isset($slot))
                    {{ $slot }}
                @endif
                @yield('content')
            </main>
        </div>

        @stack('scripts')
    </body>
</html>
