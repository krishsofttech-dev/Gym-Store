@extends('admin.layouts.admin')
@section('title', 'Products')
@section('content')

<div class="flex items-center justify-between mb-6">
    <p class="text-stone-500 text-sm">{{ $products->total() }} products total</p>
    <a href="{{ route('admin.products.create') }}" class="btn-primary flex items-center gap-2 text-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Product
    </a>
</div>

<form method="GET" class="flex flex-col sm:flex-row gap-3 mb-6">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name…" class="input-dark flex-1 text-sm py-2">
    <select name="category" onchange="this.form.submit()" class="input-dark text-sm py-2 sm:w-48">
        <option value="">All Categories</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn-secondary text-sm py-2 px-4">Filter</button>
    @if(request()->hasAny(['search','category']))
        <a href="{{ route('admin.products.index') }}" class="btn-secondary text-sm py-2 px-4">Clear</a>
    @endif
</form>

<div class="bg-stone-900 border border-stone-800 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-stone-800 text-xs uppercase tracking-widest text-stone-500">
                    <th class="text-left px-5 py-3">Product</th>
                    <th class="text-left px-5 py-3 hidden md:table-cell">Category</th>
                    <th class="text-left px-5 py-3">Price</th>
                    <th class="text-left px-5 py-3 hidden sm:table-cell">Stock</th>
                    <th class="text-left px-5 py-3 hidden lg:table-cell">Status</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-800/50">
                @forelse($products as $product)
                <tr class="hover:bg-stone-800/30 transition-colors {{ $product->trashed() ? 'opacity-50' : '' }}">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $product->thumbnail_url }}" class="w-10 h-10 rounded-lg object-cover bg-stone-800 flex-shrink-0">
                            <div>
                                <p class="text-white font-medium">{{ Str::limit($product->name, 35) }}</p>
                                <p class="text-stone-600 text-xs font-mono">{{ $product->sku ?? '—' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-stone-400 hidden md:table-cell">{{ $product->category->name }}</td>
                    <td class="px-5 py-3">
                        <p class="text-white">{{ $product->formatted_price }}</p>
                        @if($product->isOnSale())<p class="text-stone-600 text-xs line-through">{{ $product->formatted_compare_price }}</p>@endif
                    </td>
                    <td class="px-5 py-3 hidden sm:table-cell">
                        <span class="{{ $product->stock_quantity <= 5 ? 'text-amber-400' : 'text-stone-300' }}">
                            {{ $product->track_quantity ? $product->stock_quantity : '∞' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 hidden lg:table-cell">
                        <div class="flex gap-1.5 flex-wrap">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $product->is_active ? 'bg-emerald-500/15 text-emerald-400' : 'bg-stone-700 text-stone-500' }}">
                                {{ $product->is_active ? 'Active' : 'Hidden' }}
                            </span>
                            @if($product->is_featured)<span class="text-xs px-2 py-0.5 rounded-full bg-accent/15 text-accent">Featured</span>@endif
                            @if($product->trashed())<span class="text-xs px-2 py-0.5 rounded-full bg-red-500/15 text-red-400">Deleted</span>@endif
                        </div>
                    </td>
                    <td class="px-5 py-3 text-right">
                        @if(!$product->trashed())
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.products.edit', $product) }}" class="text-xs text-stone-400 hover:text-white px-2 py-1 rounded hover:bg-stone-800 transition-colors">Edit</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-stone-600 hover:text-red-400 px-2 py-1 rounded hover:bg-stone-800 transition-colors">Delete</button>
                            </form>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-stone-600">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
    <div class="px-5 py-4 border-t border-stone-800">{{ $products->withQueryString()->links('pagination::tailwind') }}</div>
    @endif
</div>
@endsection