@extends('admin.layouts.admin')
@section('title', 'Dashboard')

@section('content')

{{-- Stats grid --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @php
    $statCards = [
        ['label'=>'Total Revenue',   'value'=>'Rs. '.number_format($stats['revenue_total'],0),  'sub'=>'Rs. '.number_format($stats['revenue_today'],0).' today',  'color'=>'text-accent',     'icon'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label'=>'Total Orders',    'value'=>number_format($stats['total_orders']),             'sub'=>$stats['orders_today'].' today',                           'color'=>'text-blue-400',   'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        ['label'=>'Customers',       'value'=>number_format($stats['total_customers']),          'sub'=>'Registered users',                                        'color'=>'text-purple-400', 'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
        ['label'=>'Low Stock',       'value'=>number_format($stats['low_stock']),                'sub'=>'Need restocking',                                         'color'=>'text-amber-400',  'icon'=>'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
    ];
    @endphp
    @foreach($statCards as $card)
    <div class="bg-stone-900 border border-stone-800 rounded-2xl p-5">
        <div class="flex items-start justify-between mb-3">
            <p class="text-stone-500 text-xs uppercase tracking-widest">{{ $card['label'] }}</p>
            <svg class="w-4 h-4 {{ $card['color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
            </svg>
        </div>
        <p class="font-display text-2xl lg:text-3xl {{ $card['color'] }} mb-1">{{ $card['value'] }}</p>
        <p class="text-stone-600 text-xs">{{ $card['sub'] }}</p>
    </div>
    @endforeach
</div>

@if($stats['pending_reviews'] > 0)
<div class="bg-amber-500/10 border border-amber-500/30 rounded-xl px-4 py-3 mb-6 flex items-center justify-between">
    <span class="text-amber-400 text-sm font-medium">{{ $stats['pending_reviews'] }} review(s) awaiting moderation</span>
    <a href="{{ route('admin.reviews.index') }}?status=pending" class="text-amber-400 text-xs hover:underline">Review now </a>
</div>
@endif

<div class="bg-stone-900 border border-stone-800 rounded-2xl overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-stone-800">
        <h2 class="font-display text-lg tracking-wide">RECENT ORDERS</h2>
        <a href="{{ route('admin.orders.index') }}" class="text-xs text-accent hover:underline">View all </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-stone-800">
                    <th class="text-left px-5 py-3 text-xs uppercase tracking-widest text-stone-500">Order</th>
                    <th class="text-left px-5 py-3 text-xs uppercase tracking-widest text-stone-500">Customer</th>
                    <th class="text-left px-5 py-3 text-xs uppercase tracking-widest text-stone-500 hidden sm:table-cell">Date</th>
                    <th class="text-left px-5 py-3 text-xs uppercase tracking-widest text-stone-500">Status</th>
                    <th class="text-right px-5 py-3 text-xs uppercase tracking-widest text-stone-500">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-800/50">
                @forelse($recentOrders as $order)
                <tr class="hover:bg-stone-800/30 transition-colors">
                    <td class="px-5 py-3">
                        <a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-accent hover:underline text-xs">{{ $order->order_number }}</a>
                    </td>
                    <td class="px-5 py-3 text-stone-300">{{ $order->user->name }}</td>
                    <td class="px-5 py-3 text-stone-500 text-xs hidden sm:table-cell">{{ $order->created_at->format('d M, H:i') }}</td>
                    <td class="px-5 py-3">
                        @php $sl = $order->status_label; @endphp
                        <span class="text-xs px-2.5 py-1 rounded-full font-medium
                            {{ match($sl['color']) {'green'=>'bg-emerald-500/15 text-emerald-400','blue'=>'bg-blue-500/15 text-blue-400','yellow'=>'bg-yellow-500/15 text-yellow-400','red'=>'bg-red-500/15 text-red-400','purple'=>'bg-purple-500/15 text-purple-400',default=>'bg-stone-700 text-stone-400'} }}">
                            {{ $sl['label'] }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right font-medium">{{ $order->formatted_total }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-stone-600">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection