<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'GymStore') }} — @yield('title', 'Welcome')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-950 text-stone-100 font-body antialiased min-h-screen flex flex-col">

    {{-- Simple header --}}
    <div class="flex justify-center pt-10 pb-6">
        <a href="{{ route('home') }}" class="font-display text-3xl tracking-widest">
            GYM<span class="text-accent">STORE</span>
        </a>
    </div>

    {{-- Page content (login form, register form, etc.) --}}
    <main class="flex-1 flex items-start justify-center px-4 pt-4 pb-16">
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </main>

    {{-- Footer link --}}
    <div class="text-center pb-8">
        <a href="{{ route('home') }}" class="text-stone-600 hover:text-stone-400 text-xs transition-colors">
            ← Back to store
        </a>
    </div>

</body>
</html>