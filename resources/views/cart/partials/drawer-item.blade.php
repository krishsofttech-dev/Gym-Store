{{-- Single item row inside the cart drawer --}}
<div class="flex gap-3 py-4 border-b border-stone-800 last:border-0" id="drawer-item-{{ $item->id }}">
    <img
        src="{{ $item->product->thumbnail_url ?? asset('images/product-placeholder.jpg') }}"
        alt="{{ $item->product->name ?? $item->product_name }}"
        class="w-16 h-16 object-cover rounded-lg bg-stone-800 flex-shrink-0"
    >
    <div class="flex-1 min-w-0">
        <p class="text-white text-sm font-medium leading-snug truncate">
            {{ $item->product->name ?? 'Product' }}
        </p>
        <p class="text-stone-500 text-xs mt-0.5">Rs. {{ number_format((float)$item->unit_price, 2) }} each</p>
        <div class="flex items-center justify-between mt-2">
            <div class="flex items-center gap-2">
                <button onclick="updateCartItem({{ $item->id }}, {{ max(1, $item->quantity - 1) }})"
                        class="w-6 h-6 bg-stone-800 rounded text-stone-400 hover:text-white hover:bg-stone-700 text-xs transition-colors">−</button>
                <span class="text-white text-sm font-medium w-5 text-center">{{ $item->quantity }}</span>
                <button onclick="updateCartItem({{ $item->id }}, {{ $item->quantity + 1 }})"
                        class="w-6 h-6 bg-stone-800 rounded text-stone-400 hover:text-white hover:bg-stone-700 text-xs transition-colors">+</button>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-accent text-sm font-semibold">{{ $item->formatted_line_total }}</span>
                <button onclick="removeCartItem({{ $item->id }})"
                        class="text-stone-600 hover:text-red-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>