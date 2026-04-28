{{--
    LESSON: This is a "drawer" component — a panel that slides in from
    the right side. It listens for the 'open-cart' Alpine event dispatched
    by the cart icon in the navbar.
    $dispatch('open-cart') from any element on the page opens this.

    FIX: Uses $globalCart (shared by AppServiceProvider) instead of $cart
    so it works on every page, not just the cart page.
--}}
<div
    x-data="{ open: false }"
    @open-cart.window="open = true"
    @keydown.escape.window="open = false"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50"
    ></div>

    {{-- Drawer panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed right-0 top-0 h-full w-full max-w-md bg-stone-900 border-l border-stone-700 z-50 flex flex-col"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-stone-800">
            <h2 class="font-display text-xl tracking-widest">YOUR CART
                <span x-show="cartCount > 0" class="text-accent">(<span x-text="cartCount"></span>)</span>
            </h2>
            <button @click="open = false" class="text-stone-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Cart items — scrollable --}}
        <div class="flex-1 overflow-y-auto px-6 py-4" id="cart-drawer-items">
            @if($globalCart && $globalCart->items->isNotEmpty())
                @foreach($globalCart->items as $item)
                    @include('cart.partials.drawer-item', ['item' => $item])
                @endforeach
            @else
                <div class="flex flex-col items-center justify-center h-full text-center py-16">
                    <div class="w-16 h-16 bg-stone-800 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-stone-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 9H4L5 9z"/>
                        </svg>
                    </div>
                    <p class="text-stone-500 text-sm">Your cart is empty</p>
                    <a href="{{ route('products.index') }}" @click="open = false"
                       class="mt-4 text-accent text-sm hover:underline">
                        Start shopping 
                    </a>
                </div>
            @endif
        </div>

        {{-- Footer with totals + checkout --}}
        @if($globalCart && $globalCart->items->isNotEmpty())
        <div class="px-6 py-5 border-t border-stone-800 space-y-4">
            <div class="flex justify-between text-sm text-stone-400">
                <span>Subtotal</span>
                <span class="text-white font-medium" id="drawer-subtotal">
                    {{ $globalCart->formatted_subtotal }}
                </span>
            </div>
            <div class="flex justify-between text-xs text-stone-500">
                <span>Shipping</span>
                <span>{{ $globalCart->subtotal >= 5000 ? 'Free' : 'Rs. 350' }}</span>
            </div>
            <a
                href="{{ route('checkout.index') }}"
                @click="open = false"
                class="block w-full bg-accent text-stone-950 text-center font-semibold py-4 rounded-xl hover:bg-yellow-300 transition-colors tracking-wide uppercase text-sm"
            >
                Checkout
            </a>
            <a href="{{ route('cart.index') }}" @click="open = false"
               class="block text-center text-stone-400 text-sm hover:text-white transition-colors">
                View full cart
            </a>
        </div>
        @endif
    </div>
</div>