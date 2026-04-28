@extends('layouts.app')
@section('title', 'Your Cart')

@section('content')
<div class="pt-24 min-h-screen">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <span class="section-label">Shopping</span>
    <h1 class="font-display text-5xl tracking-wide mb-10">YOUR CART</h1>

    @if($cart && $cart->items->isNotEmpty())
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

      {{-- CART ITEMS --}}
      <div class="lg:col-span-2 space-y-4">
        @foreach($cart->items as $item)
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-5 flex gap-4" id="cart-row-{{ $item->id }}">

          <a href="{{ route('products.show', $item->product) }}" class="flex-shrink-0">
            <img src="{{ $item->product->thumbnail_url }}"
                 alt="{{ $item->product->name }}"
                 class="w-20 h-20 object-cover rounded-xl bg-stone-800">
          </a>

          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
              <div>
                <p class="text-stone-500 text-xs uppercase tracking-widest">{{ $item->product->brand ?? $item->product->category->name }}</p>
                <a href="{{ route('products.show', $item->product) }}"
                   class="text-white font-medium hover:text-accent transition-colors leading-snug block mt-0.5">
                  {{ $item->product->name }}
                </a>
                <p class="text-stone-600 text-xs mt-1 font-mono">Rs. {{ number_format((float)$item->unit_price, 2) }} each</p>
              </div>
              <button onclick="removeCartItem({{ $item->id }})"
                      class="text-stone-700 hover:text-red-400 transition-colors flex-shrink-0 mt-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </div>

            <div class="flex items-center justify-between mt-3">
              <div class="flex items-center bg-stone-800 border border-stone-700 rounded-lg overflow-hidden">
                <button onclick="updateCartItem({{ $item->id }}, {{ max(1, $item->quantity - 1) }})"
                        class="px-3 py-1.5 text-stone-400 hover:text-white hover:bg-stone-700 transition-colors text-sm">−</button>
                <span class="px-3 py-1.5 text-white text-sm font-medium min-w-[2.5rem] text-center">{{ $item->quantity }}</span>
                <button onclick="updateCartItem({{ $item->id }}, {{ $item->quantity + 1 }})"
                        class="px-3 py-1.5 text-stone-400 hover:text-white hover:bg-stone-700 transition-colors text-sm">+</button>
              </div>
              <span class="text-accent font-semibold">{{ $item->formatted_line_total }}</span>
            </div>

            @if($item->product->track_quantity && $item->product->stock_quantity < 5)
              <p class="text-amber-400 text-xs mt-2">Only {{ $item->product->stock_quantity }} left in stock</p>
            @endif
          </div>
        </div>
        @endforeach

        <div class="flex justify-end">
          <form method="POST" action="{{ route('cart.clear') }}" onsubmit="return confirm('Clear your entire cart?')">
            @csrf @method('DELETE')
            <button type="submit" class="text-stone-600 hover:text-red-400 text-xs transition-colors uppercase tracking-widest">
              Clear cart
            </button>
          </form>
        </div>
      </div>

      {{-- ORDER SUMMARY --}}
      <div class="lg:col-span-1">
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-6 sticky top-24">
          <h2 class="font-display text-xl tracking-wide mb-6">ORDER SUMMARY</h2>

          {{-- Coupon — $coupon is a plain string code e.g. "GYMFIT20" or null --}}
          <div class="mb-5" x-data="{ open: {{ $coupon ? 'false' : 'true' }} }">
            @if($coupon)
              <div class="flex items-center justify-between bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3">
                <div>
                  <p class="text-emerald-400 text-xs uppercase tracking-widest font-medium">Coupon applied</p>
                  <p class="text-white text-sm font-mono mt-0.5">{{ $coupon }}</p>
                </div>
                <form method="POST" action="{{ route('coupon.remove') }}">
                  @csrf @method('DELETE')
                  <button type="submit" class="text-stone-500 hover:text-red-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </form>
              </div>
            @else
              <button @click="open = !open"
                      class="text-xs text-stone-500 hover:text-accent transition-colors uppercase tracking-widest w-full text-left flex items-center justify-between">
                <span>Have a coupon?</span>
                <svg class="w-3 h-3 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>
              <div x-show="open" x-transition class="mt-3">
                <form method="POST" action="{{ route('coupon.apply') }}" class="flex gap-2">
                  @csrf
                  <input type="text" name="code" placeholder="GYMFIT20"
                         class="input-dark flex-1 text-sm py-2 uppercase font-mono" required>
                  <button type="submit"
                          class="bg-accent text-stone-950 text-sm font-semibold px-4 rounded-xl hover:bg-yellow-300 transition-colors">
                    Apply
                  </button>
                </form>
              </div>
            @endif
          </div>

          {{-- Totals --}}
          @php
            $subtotal    = $cart->subtotal;
            $couponModel = $coupon ? \App\Models\Coupon::findByCode($coupon) : null;
            $discount    = $couponModel ? $couponModel->calculateDiscount($subtotal) : 0;
            $shipping    = ($subtotal - $discount) >= 5000 ? 0 : 350;
            $total       = $subtotal - $discount + $shipping;
          @endphp

          <div class="space-y-3 py-4 border-t border-stone-800">
            <div class="flex justify-between text-sm">
              <span class="text-stone-500">Subtotal ({{ $cart->item_count }} items)</span>
              <span class="text-white">{{ $cart->formatted_subtotal }}</span>
            </div>
            @if($discount > 0)
            <div class="flex justify-between text-sm">
              <span class="text-emerald-400">Discount</span>
              <span class="text-emerald-400">− Rs. {{ number_format($discount, 2) }}</span>
            </div>
            @endif
            <div class="flex justify-between text-sm">
              <span class="text-stone-500">Shipping</span>
              <span class="{{ $shipping === 0 ? 'text-emerald-400' : 'text-white' }}">
                {{ $shipping === 0 ? 'Free' : 'Rs. ' . number_format($shipping, 2) }}
              </span>
            </div>
          </div>

          <div class="flex justify-between border-t border-stone-800 pt-4 mb-6">
            <span class="font-semibold text-white">Total</span>
            <span class="font-display text-2xl text-white">Rs. {{ number_format($total, 2) }}</span>
          </div>

          <a href="{{ route('checkout.index') }}"
             class="block w-full bg-accent text-stone-950 text-center font-semibold py-4 rounded-xl
                    hover:bg-yellow-300 transition-all hover:scale-[1.02] uppercase tracking-wide text-sm">
            Proceed to Checkout
          </a>

          <a href="{{ route('products.index') }}"
             class="block text-center text-stone-500 text-sm mt-3 hover:text-white transition-colors">
            ← Continue Shopping
          </a>

          @if($shipping > 0)
          @php $needed = 5000 - ($subtotal - $discount); @endphp
          <div class="mt-5 pt-5 border-t border-stone-800">
            <p class="text-xs text-stone-500 mb-2">
              Add <span class="text-white">Rs. {{ number_format($needed, 2) }}</span> more for free shipping
            </p>
            <div class="h-1.5 bg-stone-800 rounded-full overflow-hidden">
              <div class="h-full bg-accent rounded-full transition-all"
                   style="width: {{ min(100, (($subtotal - $discount) / 5000) * 100) }}%"></div>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>

    @else
    <div class="flex flex-col items-center justify-center py-32 text-center">
      <div class="w-24 h-24 bg-stone-900 rounded-full flex items-center justify-center mb-6 border border-stone-800">
        <svg class="w-10 h-10 text-stone-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 9H4L5 9z"/>
        </svg>
      </div>
      <h2 class="font-display text-3xl tracking-wide mb-2">YOUR CART IS EMPTY</h2>
      <p class="text-stone-500 mb-8">Add some products and come back here.</p>
      <a href="{{ route('products.index') }}" class="btn-primary">Start Shopping</a>
    </div>
    @endif

  </div>
</div>
@endsection