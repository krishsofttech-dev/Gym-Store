@extends('layouts.app')
@section('title', 'Order ' . $order->order_number)
@section('content')
<div class="pt-24 min-h-screen">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <a href="{{ route('orders.index') }}" class="text-stone-500 hover:text-white text-sm transition-colors mb-6 inline-block">← Back to orders</a>

    <div class="flex items-start justify-between mb-8">
      <div>
        <h1 class="font-display text-4xl tracking-wide">{{ $order->order_number }}</h1>
        <p class="text-stone-500 text-sm mt-1">Placed {{ $order->created_at->format('d M Y') }}</p>
      </div>
      @php $sl = $order->status_label; @endphp
      <span class="text-sm font-semibold px-4 py-1.5 rounded-full
        {{ match($sl['color']) {
          'green'  => 'bg-emerald-500/20 text-emerald-400',
          'blue'   => 'bg-blue-500/20 text-blue-400',
          'yellow' => 'bg-yellow-500/20 text-yellow-400',
          'red'    => 'bg-red-500/20 text-red-400',
          default  => 'bg-stone-700 text-stone-400',
        } }}">
        {{ $sl['label'] }}
      </span>
    </div>

    {{-- Items --}}
    <div class="bg-stone-900 border border-stone-800 rounded-2xl p-6 mb-6">
      <h2 class="font-display text-lg tracking-wide mb-4 text-stone-400">ITEMS</h2>
      <div class="space-y-4">
        @foreach($order->items as $item)
        <div class="flex gap-4">
          <div class="w-14 h-14 bg-stone-800 rounded-xl overflow-hidden flex-shrink-0">
            @if($item->product_image)
              <img src="{{ asset('storage/'.$item->product_image) }}" class="w-full h-full object-cover">
            @endif
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-white font-medium text-sm">{{ $item->product_name }}</p>
            <p class="text-stone-500 text-xs mt-0.5 font-mono">{{ $item->product_sku }}</p>
            <p class="text-stone-500 text-xs">Rs. {{ number_format((float)$item->unit_price, 2) }} × {{ $item->quantity }}</p>
          </div>
          <span class="text-white font-semibold text-sm flex-shrink-0">{{ $item->formatted_subtotal }}</span>
        </div>
        @endforeach
      </div>

      <div class="border-t border-stone-800 mt-5 pt-5 space-y-2">
        <div class="flex justify-between text-sm"><span class="text-stone-500">Subtotal</span><span>Rs. {{ number_format((float)$order->subtotal, 2) }}</span></div>
        @if((float)$order->discount_amount > 0)
        <div class="flex justify-between text-sm"><span class="text-emerald-400">Discount</span><span class="text-emerald-400">− Rs. {{ number_format((float)$order->discount_amount, 2) }}</span></div>
        @endif
        <div class="flex justify-between text-sm"><span class="text-stone-500">Shipping</span><span>{{ (float)$order->shipping_amount == 0 ? 'Free' : 'Rs. '.number_format((float)$order->shipping_amount,2) }}</span></div>
        <div class="flex justify-between border-t border-stone-800 pt-3 mt-1">
          <span class="font-semibold">Total</span>
          <span class="font-display text-2xl">{{ $order->formatted_total }}</span>
        </div>
      </div>
    </div>

    {{-- Shipping + Tracking --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
      <div class="bg-stone-900 border border-stone-800 rounded-2xl p-5">
        <h3 class="text-xs uppercase tracking-widest text-stone-500 mb-3">Shipping Address</h3>
        <p class="text-white text-sm">{{ $order->shipping_name }}</p>
        <p class="text-stone-400 text-sm mt-1">{{ $order->shipping_address }}</p>
        @if($order->shipping_phone)
          <p class="text-stone-500 text-xs mt-2">{{ $order->shipping_phone }}</p>
        @endif
      </div>
      <div class="bg-stone-900 border border-stone-800 rounded-2xl p-5">
        <h3 class="text-xs uppercase tracking-widest text-stone-500 mb-3">Tracking</h3>
        @if($order->tracking_number)
          <p class="text-white font-mono text-sm">{{ $order->tracking_number }}</p>
          <p class="text-stone-500 text-xs mt-1">{{ $order->shipping_carrier }}</p>
          @if($order->shipped_at)
            <p class="text-stone-600 text-xs mt-2">Shipped {{ $order->shipped_at->format('d M Y') }}</p>
          @endif
        @else
          <p class="text-stone-600 text-sm">Tracking not yet available</p>
        @endif
      </div>
    </div>

    {{-- Cancel --}}
    @if($order->isCancellable())
    <div class="text-center">
      <form method="POST" action="{{ route('orders.cancel', $order) }}"
            onsubmit="return confirm('Are you sure you want to cancel this order?')">
        @csrf
        <button type="submit" class="text-stone-600 hover:text-red-400 text-sm transition-colors uppercase tracking-widest">
          Cancel Order
        </button>
      </form>
    </div>
    @endif

  </div>
</div>
@endsection