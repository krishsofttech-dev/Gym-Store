@extends('admin.layouts.admin')
@section('title', 'Orders')
@section('content')

<form method="GET" class="flex flex-col sm:flex-row gap-3 mb-6">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search order number…" class="input-dark flex-1 text-sm py-2">
    <select name="status" onchange="this.form.submit()" class="input-dark text-sm py-2 sm:w-44">
        <option value="">All Statuses</option>
        @foreach(['pending','confirmed','processing','shipped','delivered','cancelled','refunded'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn-secondary text-sm py-2 px-4">Filter</button>
    @if(request()->hasAny(['search','status']))
        <a href="{{ route('admin.orders.index') }}" class="btn-secondary text-sm py-2 px-4">Clear</a>
    @endif
</form>

<div class="bg-stone-900 border border-stone-800 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-stone-800 text-xs uppercase tracking-widest text-stone-500">
                    <th class="text-left px-5 py-3">Order</th>
                    <th class="text-left px-5 py-3">Customer</th>
                    <th class="text-left px-5 py-3 hidden sm:table-cell">Date</th>
                    <th class="text-left px-5 py-3">Status</th>
                    <th class="text-left px-5 py-3 hidden md:table-cell">Payment</th>
                    <th class="text-right px-5 py-3">Total</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-800/50">
                @forelse($orders as $order)
                <tr class="hover:bg-stone-800/30 transition-colors">
                    <td class="px-5 py-3">
                        <a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-accent hover:underline text-xs">
                            {{ $order->order_number }}
                        </a>
                    </td>
                    <td class="px-5 py-3 text-stone-300">{{ $order->user->name }}</td>
                    <td class="px-5 py-3 text-stone-500 text-xs hidden sm:table-cell">{{ $order->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3">
                        @php $sl = $order->status_label; @endphp
                        <span class="text-xs px-2.5 py-1 rounded-full font-medium
                            {{ match($sl['color']) {'green'=>'bg-emerald-500/15 text-emerald-400','blue'=>'bg-blue-500/15 text-blue-400','yellow'=>'bg-yellow-500/15 text-yellow-400','red'=>'bg-red-500/15 text-red-400','purple'=>'bg-purple-500/15 text-purple-400','indigo'=>'bg-indigo-500/15 text-indigo-400',default=>'bg-stone-700 text-stone-400'} }}">
                            {{ $sl['label'] }}
                        </span>
                    </td>
                    <td class="px-5 py-3 hidden md:table-cell">
                        <span class="text-xs {{ $order->isPaid() ? 'text-emerald-400' : 'text-stone-500' }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right font-medium text-white">{{ $order->formatted_total }}</td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-xs text-stone-400 hover:text-white px-2 py-1 rounded hover:bg-stone-800 transition-colors">
                            View
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-12 text-center text-stone-600">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="px-5 py-4 border-t border-stone-800">{{ $orders->withQueryString()->links('pagination::tailwind') }}</div>
    @endif
</div>
@endsection