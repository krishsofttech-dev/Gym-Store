{{-- resources/views/orders/index.blade.php --}}
@extends('layouts.app')
@section('title', 'My Orders')
@section('content')
<div class="pt-24 min-h-screen">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <span class="section-label">Account</span>
    <h1 class="font-display text-5xl tracking-wide mb-10">MY ORDERS</h1>

    @forelse($orders as $order)
    <div class="bg-stone-900 border border-stone-800 rounded-2xl p-5 mb-4 hover:border-stone-600 transition-colors">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div>
          <p class="text-white font-mono font-medium">{{ $order->order_number }}</p>
          <p class="text-stone-500 text-xs mt-0.5">{{ $order->created_at->format('d M Y, h:i A') }}</p>
        </div>
        <div class="flex items-center gap-3">
          {{-- Status badge --}}
          @php $sl = $order->status_label; @endphp
          <span class="text-xs font-semibold px-3 py-1 rounded-full
            {{ match($sl['color']) {
              'green'  => 'bg-emerald-500/20 text-emerald-400',
              'blue'   => 'bg-blue-500/20 text-blue-400',
              'yellow' => 'bg-yellow-500/20 text-yellow-400',
              'red'    => 'bg-red-500/20 text-red-400',
              'purple' => 'bg-purple-500/20 text-purple-400',
              default  => 'bg-stone-700 text-stone-400',
            } }}">
            {{ $sl['label'] }}
          </span>
          <span class="font-display text-lg text-white">{{ $order->formatted_total }}</span>
        </div>
      </div>

      {{-- Items preview --}}
      <div class="flex items-center gap-2 mb-3">
        @foreach($order->items->take(4) as $item)
          <div class="w-10 h-10 bg-stone-800 rounded-lg overflow-hidden flex-shrink-0">
            @if($item->product_image)
              <img src="{{ asset('storage/'.$item->product_image) }}" class="w-full h-full object-cover">
            @endif
          </div>
        @endforeach
        @if($order->items->count() > 4)
          <span class="text-stone-600 text-xs">+{{ $order->items->count() - 4 }} more</span>
        @endif
      </div>

      <div class="flex gap-3">
        <a href="{{ route('orders.show', $order) }}"
           class="text-xs text-accent hover:underline">View details </a>
        @if($order->isCancellable())
          <form method="POST" action="{{ route('orders.cancel', $order) }}"
                onsubmit="return confirm('Cancel this order?')">
            @csrf
            <button type="submit" class="text-xs text-stone-600 hover:text-red-400 transition-colors">Cancel</button>
          </form>
        @endif
      </div>
    </div>
    @empty
      <div class="text-center py-20">
        <p class="text-stone-500 mb-4">You haven't placed any orders yet.</p>
        <a href="{{ route('products.index') }}" class="btn-primary">Start Shopping</a>
      </div>
    @endforelse

    <div class="mt-6">{{ $orders->links('pagination::tailwind') }}</div>
  </div>
</div>
@endsection