@extends('admin.layouts.admin')
@section('title', $order->order_number)
@section('content')

<a href="{{ route('admin.orders.index') }}" class="text-stone-500 hover:text-white text-sm transition-colors mb-6 inline-block">← Back to orders</a>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Items + customer --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Order items --}}
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-5">
            <h2 class="font-display text-lg tracking-wide mb-4 text-stone-400">ORDER ITEMS</h2>
            <div class="space-y-4">
                @foreach($order->items as $item)
                <div class="flex gap-3">
                    <div class="w-12 h-12 bg-stone-800 rounded-xl overflow-hidden flex-shrink-0">
                        @if($item->product_image)
                            <img src="{{ asset('storage/'.$item->product_image) }}" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-medium">{{ $item->product_name }}</p>
                        <p class="text-stone-500 text-xs font-mono">{{ $item->product_sku ?? '—' }}</p>
                        <p class="text-stone-500 text-xs">Rs. {{ number_format((float)$item->unit_price,2) }} × {{ $item->quantity }}</p>
                    </div>
                    <span class="text-white text-sm font-semibold flex-shrink-0">{{ $item->formatted_subtotal }}</span>
                </div>
                @endforeach
            </div>
            <div class="border-t border-stone-800 mt-5 pt-4 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-stone-500">Subtotal</span><span>Rs. {{ number_format((float)$order->subtotal,2) }}</span></div>
                @if((float)$order->discount_amount > 0)
                <div class="flex justify-between"><span class="text-emerald-400">Discount</span><span class="text-emerald-400">− Rs. {{ number_format((float)$order->discount_amount,2) }}</span></div>
                @endif
                <div class="flex justify-between"><span class="text-stone-500">Shipping</span><span>{{ (float)$order->shipping_amount == 0 ? 'Free' : 'Rs. '.number_format((float)$order->shipping_amount,2) }}</span></div>
                <div class="flex justify-between border-t border-stone-800 pt-2 mt-1">
                    <span class="font-semibold">Total</span>
                    <span class="font-display text-xl">{{ $order->formatted_total }}</span>
                </div>
            </div>
        </div>

        {{-- Customer + shipping --}}
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-5">
            <h2 class="font-display text-lg tracking-wide mb-4 text-stone-400">CUSTOMER</h2>
            <div class="grid grid-cols-2 gap-5 text-sm">
                <div>
                    <p class="text-stone-500 text-xs uppercase tracking-widest mb-2">Account</p>
                    <p class="text-white font-medium">{{ $order->user->name }}</p>
                    <p class="text-stone-400">{{ $order->user->email }}</p>
                    <a href="mailto:{{ $order->user->email }}" class="text-accent text-xs hover:underline mt-1 inline-block">Send email </a>
                </div>
                <div>
                    <p class="text-stone-500 text-xs uppercase tracking-widest mb-2">Shipping Address</p>
                    <p class="text-white">{{ $order->shipping_name }}</p>
                    <p class="text-stone-400 text-xs mt-0.5 leading-relaxed">{{ $order->shipping_address }}</p>
                    @if($order->shipping_phone)<p class="text-stone-500 text-xs mt-1">{{ $order->shipping_phone }}</p>@endif
                </div>
            </div>
            @if($order->customer_notes)
            <div class="mt-4 pt-4 border-t border-stone-800">
                <p class="text-stone-500 text-xs uppercase tracking-widest mb-1">Customer Notes</p>
                <p class="text-stone-400 text-sm italic">{{ $order->customer_notes }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Right: Status + tracking --}}
    <div class="space-y-5">

        {{-- Status --}}
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-5">
            <h2 class="font-display text-lg tracking-wide mb-4 text-stone-400">STATUS</h2>
            @php $sl = $order->status_label; @endphp
            <div class="mb-4">
                <span class="text-sm px-3 py-1.5 rounded-full font-medium
                    {{ match($sl['color']) {'green'=>'bg-emerald-500/15 text-emerald-400','blue'=>'bg-blue-500/15 text-blue-400','yellow'=>'bg-yellow-500/15 text-yellow-400','red'=>'bg-red-500/15 text-red-400','purple'=>'bg-purple-500/15 text-purple-400','indigo'=>'bg-indigo-500/15 text-indigo-400',default=>'bg-stone-700 text-stone-400'} }}">
                    {{ $sl['label'] }}
                </span>
                <span class="ml-2 text-xs text-{{ $order->isPaid() ? 'emerald-400' : 'stone-500' }}">
                    {{ ucfirst($order->payment_status) }}
                </span>
            </div>

            @php
            $validNext = \App\Models\Order::VALID_TRANSITIONS[$order->status] ?? [];
            @endphp
            @if(count($validNext) > 0)
            <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                @csrf @method('PATCH')
                <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Update Status</label>
                <div class="flex gap-2">
                    <select name="status" class="input-dark text-sm py-2 flex-1">
                        @foreach($validNext as $next)
                            <option value="{{ $next }}">{{ ucfirst($next) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-primary text-sm py-2 px-4">Update</button>
                </div>
            </form>
            @else
                <p class="text-stone-600 text-xs">No further status transitions available.</p>
            @endif
        </div>

        {{-- Tracking --}}
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-5">
            <h2 class="font-display text-lg tracking-wide mb-4 text-stone-400">TRACKING</h2>
            <form method="POST" action="{{ route('admin.orders.tracking', $order) }}" class="space-y-3">
                @csrf @method('PATCH')
                <div>
                    <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Carrier</label>
                    <input type="text" name="shipping_carrier" value="{{ $order->shipping_carrier }}"
                           class="input-dark text-sm py-2" placeholder="e.g. DHL, FedEx">
                </div>
                <div>
                    <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Tracking Number</label>
                    <input type="text" name="tracking_number" value="{{ $order->tracking_number }}"
                           class="input-dark text-sm py-2 font-mono" placeholder="TN123456789">
                </div>
                <button type="submit" class="w-full btn-secondary text-sm py-2">
                    Save & Mark as Shipped
                </button>
            </form>
        </div>

        {{-- Admin notes --}}
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-5">
            <h2 class="font-display text-base tracking-wide mb-3 text-stone-500">ORDER INFO</h2>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between"><span class="text-stone-600">Payment method</span><span class="text-stone-400">{{ ucfirst($order->payment_method ?? '—') }}</span></div>
                <div class="flex justify-between"><span class="text-stone-600">Placed</span><span class="text-stone-400">{{ $order->created_at->format('d M Y, H:i') }}</span></div>
                @if($order->coupon_code)
                <div class="flex justify-between"><span class="text-stone-600">Coupon</span><span class="text-accent font-mono">{{ $order->coupon_code }}</span></div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection