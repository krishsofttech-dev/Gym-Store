{{--
    LESSON: This partial is included wherever a product grid card is needed.
    Usage: @include('products.partials.card', ['product' => $product])
    It receives $product and renders a self-contained card with:
      - Wishlist toggle (Alpine + AJAX)
      - Add to cart (AJAX)
      - Discount badge
      - Hover 3D tilt effect (CSS transform)
--}}
<div
    class="group relative bg-stone-900 border border-stone-800 rounded-2xl overflow-hidden
           hover:border-stone-600 transition-all duration-300 product-card"
    data-product-id="{{ $product->id }}"
>
    {{-- Product image --}}
    <div class="relative aspect-square bg-stone-800 overflow-hidden">
        <a href="{{ route('products.show', $product) }}">
            <img
                src="{{ $product->thumbnail_url }}"
                alt="{{ $product->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                loading="lazy"
            >
        </a>

        {{-- Badges --}}
        <div class="absolute top-3 left-3 flex flex-col gap-1.5">
            @if($product->is_new)
                <span class="bg-accent text-stone-950 text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-wide">New</span>
            @endif
            @if($product->isOnSale())
                <span class="bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">
                    -{{ $product->discount_percentage }}
                </span>
            @endif
            @if($product->isOutOfStock())
                <span class="bg-stone-700 text-stone-400 text-xs font-bold px-2.5 py-1 rounded-full">Sold Out</span>
            @endif
        </div>

        {{-- Wishlist button --}}
        @auth
        <button
            class="absolute top-3 right-3 w-8 h-8 bg-stone-900/80 backdrop-blur-sm rounded-full
                   flex items-center justify-center text-stone-400 hover:text-red-400
                   transition-all opacity-0 group-hover:opacity-100"
            onclick="toggleWishlist({{ $product->id }}, this)"
            data-wishlisted="{{ auth()->user()->hasWishlisted($product->id) ? 'true' : 'false' }}"
            title="Add to wishlist"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </button>
        @endauth
    </div>

    {{-- Product info --}}
    <div class="p-4">
        <p class="text-stone-500 text-xs uppercase tracking-widest mb-1">{{ $product->brand ?? $product->category->name }}</p>

        <a href="{{ route('products.show', $product) }}">
            <h3 class="text-white font-medium text-sm leading-snug mb-3 hover:text-accent transition-colors line-clamp-2">
                {{ $product->name }}
            </h3>
        </a>

        {{-- Rating stars --}}
        @if($product->reviews_count > 0)
            <div class="flex items-center gap-1.5 mb-3">
                <div class="flex text-accent text-xs">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= round($product->average_rating))★@else☆@endif
                    @endfor
                </div>
                <span class="text-stone-600 text-xs">({{ $product->reviews_count }})</span>
            </div>
        @endif

        {{-- Price + Add to cart --}}
        <div class="flex items-center justify-between">
            <div>
                <span class="text-white font-semibold">{{ $product->formatted_price }}</span>
                @if($product->isOnSale())
                    <span class="text-stone-600 text-xs line-through ml-1.5">{{ $product->formatted_compare_price }}</span>
                @endif
            </div>

            @if(!$product->isOutOfStock())
                <button
                    class="w-9 h-9 bg-accent text-stone-950 rounded-xl flex items-center justify-center
                           hover:bg-yellow-300 transition-all hover:scale-110 active:scale-95"
                    onclick="addToCart({{ $product->id }}, 1, this)"
                    title="Add to cart"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            @endif
        </div>
    </div>
</div>