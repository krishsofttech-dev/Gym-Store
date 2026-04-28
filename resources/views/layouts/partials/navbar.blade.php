{{--
    LESSON: This partial is included in app.blade.php via @include().
    It reads Alpine.js state from the parent x-data="gymStore()" on <body>.
    $cartCount, $searchOpen etc. are all reactive — updating them
    anywhere on the page instantly updates the navbar too.
--}}
<header
    class="fixed top-0 left-0 right-0 z-40 transition-all duration-300"
    :class="scrolled ? 'bg-stone-950/95 backdrop-blur-md border-b border-stone-800' : 'bg-transparent'"
    x-data="{ scrolled: false }"
    x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 20)"
>
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                <span class="font-display text-2xl lg:text-3xl tracking-widest text-white group-hover:text-accent transition-colors">
                    GYM<span class="text-accent">STORE</span>
                </span>
            </a>

            {{-- Desktop nav links --}}
            <div class="hidden lg:flex items-center gap-8">
                <a href="{{ route('products.index') }}"
                   class="nav-link text-sm font-medium tracking-wide text-stone-300 hover:text-white transition-colors uppercase">
                    Shop
                </a>

                {{-- Categories dropdown --}}
                <div class="relative" x-data="{ open: false }" @mouseenter="open=true" @mouseleave="open=false">
                    <button class="nav-link text-sm font-medium tracking-wide text-stone-300 hover:text-white transition-colors uppercase flex items-center gap-1">
                        Categories
                        <svg class="w-3 h-3 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="absolute top-full left-0 mt-2 w-52 bg-stone-900 border border-stone-700 rounded-xl shadow-2xl overflow-hidden"
                    >
                        {{-- LESSON: We cache categories in the layout using
                             View::share() in AppServiceProvider, or pass via
                             a View Composer. For now we use a hardcoded list
                             — we'll wire up the DB in the service provider step. --}}
                        @php
                            $navCategories = \App\Models\Category::active()->topLevel()->ordered()->get();
                        @endphp
                        @foreach($navCategories as $cat)
                            <a href="{{ route('categories.show', $cat) }}"
                               class="block px-4 py-3 text-sm text-stone-300 hover:bg-stone-800 hover:text-white transition-colors">
                                {{ $cat->name }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <a href="{{ route('products.index') }}?sort=popular"
                   class="nav-link text-sm font-medium tracking-wide text-stone-300 hover:text-white transition-colors uppercase">
                    Best Sellers
                </a>
            </div>

            {{-- Right side actions --}}
            <div class="flex items-center gap-3 lg:gap-4">

                {{-- Search button --}}
                <button
                    @click="searchOpen = !searchOpen"
                    class="p-2 text-stone-400 hover:text-white transition-colors"
                    aria-label="Search"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                </button>

                {{-- Auth links --}}
                @auth
                    <a href="{{ route('profile.show') }}" class="hidden lg:block p-2 text-stone-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </a>
                    <a href="{{ route('wishlist.index') }}" class="hidden lg:block p-2 text-stone-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden lg:block text-sm font-medium text-stone-400 hover:text-white transition-colors">
                        Login
                    </a>
                @endauth

                {{-- Cart button with item count badge --}}
                <button
                    @click="$dispatch('open-cart')"
                    class="relative p-2 text-stone-400 hover:text-white transition-colors"
                    aria-label="Open cart"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 9H4L5 9z"/>
                    </svg>
                    {{-- Badge: only show if cartCount > 0 --}}
                    <span
                        x-show="cartCount > 0"
                        x-text="cartCount"
                        class="absolute -top-1 -right-1 bg-accent text-stone-950 text-xs font-bold w-4 h-4 rounded-full flex items-center justify-center leading-none"
                    ></span>
                </button>

                {{-- Mobile menu button --}}
                <button
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="lg:hidden p-2 text-stone-400 hover:text-white transition-colors"
                >
                    <svg x-show="!mobileMenuOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileMenuOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Search bar — slides down when searchOpen is true --}}
        <div
            x-show="searchOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            @click.away="searchOpen = false"
            class="pb-4"
        >
            <form action="{{ route('search') }}" method="GET" class="relative">
                <input
                    type="text"
                    name="q"
                    placeholder="Search dumbbells, protein, apparel..."
                    class="w-full bg-stone-800 border border-stone-700 rounded-xl px-5 py-3 pr-12 text-white placeholder-stone-500 focus:outline-none focus:border-accent transition-colors"
                    x-ref="searchInput"
                    x-init="$watch('searchOpen', val => val && $nextTick(() => $refs.searchInput.focus()))"
                >
                <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-stone-400 hover:text-accent transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                </button>
            </form>
        </div>

        {{-- Mobile menu --}}
        <div x-show="mobileMenuOpen" x-transition class="lg:hidden pb-4 border-t border-stone-800 pt-4 space-y-3">
            <a href="{{ route('products.index') }}" class="block text-stone-300 hover:text-white py-2">Shop All</a>
            <a href="{{ route('products.index') }}?sort=popular" class="block text-stone-300 hover:text-white py-2">Best Sellers</a>
            @auth
                <a href="{{ route('profile.show') }}" class="block text-stone-300 hover:text-white py-2">My Account</a>
                <a href="{{ route('wishlist.index') }}" class="block text-stone-300 hover:text-white py-2">Wishlist</a>
                <a href="{{ route('orders.index') }}" class="block text-stone-300 hover:text-white py-2">My Orders</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block text-stone-400 hover:text-white py-2 text-left">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block text-stone-300 hover:text-white py-2">Login</a>
                <a href="{{ route('register') }}" class="block text-accent py-2">Register</a>
            @endauth
        </div>
    </nav>
</header>