@extends('layouts.app')
@section('title', 'Order Confirmed!')

@section('content')
<div class="pt-24 min-h-screen flex items-center">
  <div class="max-w-2xl mx-auto px-4 py-16 text-center w-full">

    {{-- Animated checkmark --}}
    <div class="w-20 h-20 bg-emerald-500/10 border border-emerald-500/30 rounded-full flex items-center justify-center mx-auto mb-6 success-pop">
      <svg class="w-9 h-9 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
      </svg>
    </div>

    <span class="section-label">Thank you!</span>
    <h1 class="font-display text-5xl tracking-wide mb-3">ORDER CONFIRMED</h1>
    <p class="text-stone-500 mb-2">Order <span class="text-white font-mono">{{ $order->order_number }}</span></p>
    <p class="text-stone-500 text-sm mb-10">
      A confirmation email has been sent to <span class="text-stone-300">{{ $order->shipping_email }}</span>
    </p>

    {{-- Order items summary --}}
    <div class="bg-stone-900 border border-stone-800 rounded-2xl p-6 text-left mb-8">
      <h3 class="font-display text-lg tracking-wide mb-4 text-stone-400">ORDER DETAILS</h3>
      <div class="space-y-3">
        @foreach($order->items as $item)
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-stone-800 rounded-lg flex-shrink-0 overflow-hidden">
            @if($item->product_image)
              <img src="{{ asset('storage/'.$item->product_image) }}" class="w-full h-full object-cover">
            @endif
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-white text-sm truncate">{{ $item->product_name }}</p>
            <p class="text-stone-500 text-xs">Qty: {{ $item->quantity }}</p>
          </div>
          <span class="text-white text-sm font-medium">{{ $item->formatted_subtotal }}</span>
        </div>
        @endforeach
      </div>
      <div class="border-t border-stone-800 mt-4 pt-4 flex justify-between">
        <span class="text-stone-500 text-sm">Total paid</span>
        <span class="font-display text-xl text-white">{{ $order->formatted_total }}</span>
      </div>
    </div>

    {{-- Shipping info --}}
    <div class="bg-stone-900/50 border border-stone-800 rounded-xl p-4 text-left mb-8 text-sm">
      <p class="text-stone-500 text-xs uppercase tracking-widest mb-2">Delivering to</p>
      <p class="text-white">{{ $order->shipping_name }}</p>
      <p class="text-stone-400">{{ $order->shipping_address }}</p>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
      <a href="{{ route('orders.show', $order) }}" class="btn-secondary">Track Order</a>
      <a href="{{ route('products.index') }}" class="btn-primary">Continue Shopping</a>
    </div>
  </div>
</div>

<style>
.success-pop { animation: pop .5s cubic-bezier(.175,.885,.32,1.275) both; }
@keyframes pop { from { transform: scale(0); opacity: 0; } to { transform: scale(1); opacity: 1; } }
</style>
@endsection