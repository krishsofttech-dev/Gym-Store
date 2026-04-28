<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- LESSON: csrf-token meta tag is read by our JS fetch() calls
         to include the CSRF token in POST requests automatically --}}

    <title>{{ config('app.name', 'GymStore') }} — @yield('title', 'Premium Gym Equipment')</title>
    <meta name="description" content="@yield('meta_description', 'Premium gym accessories and equipment delivered to your door.')">

    {{-- Fonts: Bebas Neue for headers, DM Sans for body — strong industrial feel --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    {{-- Vite: compiles Tailwind CSS + Alpine.js + our custom CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Page-specific head content (SEO tags, etc.) --}}
    @stack('head')
</head>

{{--
    LESSON: x-data="gymStore()" initialises Alpine.js on the body.
    All global state (cart count, nav open, search open) lives here.
    Child components can read/write it via Alpine's reactivity system.
--}}
<body
    class="bg-stone-950 text-stone-100 font-body antialiased"
    x-data="gymStore()"
    x-init="initStore()"
>

    {{-- ======================================================
         LOADING SCREEN — shown briefly on first page load
         Three.js needs a moment to initialise the 3D scene
    ====================================================== --}}
    <div
        id="loading-screen"
        class="fixed inset-0 z-[999] bg-stone-950 flex items-center justify-center"
        x-show="loading"
        x-transition:leave="transition duration-700"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="text-center">
            <div class="loader-ring mx-auto mb-4"></div>
            <p class="font-display text-2xl tracking-widest text-accent">GYMSTORE</p>
        </div>
    </div>

    {{-- Cart count for Alpine.js initialisation --}}
    <meta id="cart-count-data" data-count="{{ $globalCartCount ?? 0 }}">

    {{-- ======================================================
         NAVIGATION
    ====================================================== --}}
    @include('layouts.partials.navbar')

    {{-- ======================================================
         FLASH MESSAGES
         LESSON: with('success', '...') in controllers sets these.
         They auto-dismiss after 4 seconds via Alpine.
    ====================================================== --}}
    @if(session('success') || session('error'))
        <div
            class="fixed top-20 right-4 z-50 max-w-sm"
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 4000)"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            @if(session('success'))
                <div class="bg-emerald-500 text-white px-5 py-3 rounded-lg shadow-xl flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-500 text-white px-5 py-3 rounded-lg shadow-xl flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif
        </div>
    @endif

    {{-- ======================================================
         MAIN CONTENT — each page's @section('content') goes here
    ====================================================== --}}
    <main>
        @yield('content')
    </main>

    {{-- ======================================================
         CART DRAWER — slides in from the right
         Controlled by Alpine: $dispatch('open-cart')
    ====================================================== --}}
    @include('layouts.partials.cart-drawer')

    {{-- ======================================================
         FOOTER
    ====================================================== --}}
    @include('layouts.partials.footer')

    {{-- Page-specific scripts (Three.js scenes, etc.) --}}
    @stack('scripts')

</body>
</html>