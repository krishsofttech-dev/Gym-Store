@extends('layouts.app')

@section('title', 'Shop All Products')

@section('content')
<div class="pt-24 min-h-screen">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Page header --}}
    <div class="mb-8">
      <span class="section-label">All Products</span>
      <h1 class="font-display text-5xl tracking-wide">SHOP</h1>
      <p class="text-stone-500 text-sm mt-1">
        {{ $products->total() }} products
        @if(request('search')) for "<span class="text-white">{{ request('search') }}</span>"@endif
      </p>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">

      {{-- =====================================================
           SIDEBAR FILTERS
           LESSON: The form uses GET method so filters appear
           in the URL: /products?category=dumbbells&min_price=1000
           This makes filtered pages shareable/bookmarkable.
      ===================================================== --}}
      <aside class="lg:w-64 flex-shrink-0">
        <form method="GET" action="{{ route('products.index') }}" id="filter-form">

          {{-- Keep search term if present --}}
          @if(request('search'))
            <input type="hidden" name="search" value="{{ request('search') }}">
          @endif

          {{-- Active filters strip --}}
          @if(request()->hasAny(['category','brand','min_price','max_price','in_stock']))
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs text-stone-400 uppercase tracking-widest">Active filters</span>
              <a href="{{ route('products.index') }}" class="text-xs text-accent hover:underline">Clear all</a>
            </div>
          @endif

          {{-- Category --}}
          <div class="mb-6">
            <h3 class="text-xs font-semibold uppercase tracking-widest text-stone-400 mb-3">Category</h3>
            <div class="space-y-2">
              @foreach($categories as $cat)
                <label class="flex items-center gap-2.5 cursor-pointer group">
                  <input type="radio" name="category" value="{{ $cat->slug }}"
                    {{ request('category') === $cat->slug ? 'checked' : '' }}
                    class="accent-yellow-400 cursor-pointer"
                    onchange="this.form.submit()">
                  <span class="text-sm text-stone-400 group-hover:text-white transition-colors">
                    {{ $cat->name }}
                  </span>
                </label>
              @endforeach
              @if(request('category'))
                <label class="flex items-center gap-2.5 cursor-pointer group">
                  <input type="radio" name="category" value=""
                    checked="{{ !request('category') }}"
                    class="accent-yellow-400 cursor-pointer"
                    onchange="this.form.submit()">
                  <span class="text-sm text-stone-500 group-hover:text-white transition-colors">All categories</span>
                </label>
              @endif
            </div>
          </div>

          {{-- Price range --}}
          <div class="mb-6">
            <h3 class="text-xs font-semibold uppercase tracking-widest text-stone-400 mb-3">Price Range</h3>
            <div class="flex items-center gap-2">
              <input type="number" name="min_price" placeholder="{{ $priceRange['min'] }}"
                value="{{ request('min_price') }}"
                class="w-full bg-stone-800 border border-stone-700 rounded-lg px-3 py-2 text-sm text-white placeholder-stone-600 focus:outline-none focus:border-accent">
              <span class="text-stone-600 text-sm">–</span>
              <input type="number" name="max_price" placeholder="{{ $priceRange['max'] }}"
                value="{{ request('max_price') }}"
                class="w-full bg-stone-800 border border-stone-700 rounded-lg px-3 py-2 text-sm text-white placeholder-stone-600 focus:outline-none focus:border-accent">
            </div>
            <button type="submit" class="mt-2 w-full text-xs text-stone-500 hover:text-accent transition-colors py-1">
              Apply 
            </button>
          </div>

          {{-- Brand --}}
          @if($brands->count())
          <div class="mb-6">
            <h3 class="text-xs font-semibold uppercase tracking-widest text-stone-400 mb-3">Brand</h3>
            <div class="space-y-2">
              @foreach($brands as $brand)
                <label class="flex items-center gap-2.5 cursor-pointer group">
                  <input type="radio" name="brand" value="{{ $brand }}"
                    {{ request('brand') === $brand ? 'checked' : '' }}
                    class="accent-yellow-400 cursor-pointer"
                    onchange="this.form.submit()">
                  <span class="text-sm text-stone-400 group-hover:text-white transition-colors">{{ $brand }}</span>
                </label>
              @endforeach
            </div>
          </div>
          @endif

          {{-- In stock only --}}
          <div class="mb-6">
            <label class="flex items-center gap-2.5 cursor-pointer group">
              <input type="checkbox" name="in_stock" value="1"
                {{ request('in_stock') ? 'checked' : '' }}
                class="accent-yellow-400 cursor-pointer"
                onchange="this.form.submit()">
              <span class="text-sm text-stone-400 group-hover:text-white transition-colors">In stock only</span>
            </label>
          </div>

        </form>
      </aside>

      {{-- =====================================================
           PRODUCT GRID
      ===================================================== --}}
      <div class="flex-1 min-w-0">

        {{-- Sort bar --}}
        <div class="flex items-center justify-between mb-6">
          <p class="text-stone-500 text-sm">
            Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }}
          </p>
          <select
            name="sort"
            onchange="window.location = '{{ route('products.index') }}?' + new URLSearchParams({...Object.fromEntries(new URLSearchParams(window.location.search)), sort: this.value})"
            class="bg-stone-800 border border-stone-700 text-stone-300 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-accent cursor-pointer"
          >
            <option value="newest"     {{ request('sort','newest') === 'newest'     ? 'selected' : '' }}>Newest</option>
            <option value="popular"    {{ request('sort') === 'popular'             ? 'selected' : '' }}>Most Popular</option>
            <option value="price_asc"  {{ request('sort') === 'price_asc'           ? 'selected' : '' }}>Price: Low  High</option>
            <option value="price_desc" {{ request('sort') === 'price_desc'          ? 'selected' : '' }}>Price: High  Low</option>
            <option value="top_rated"  {{ request('sort') === 'top_rated'           ? 'selected' : '' }}>Top Rated</option>
          </select>
        </div>

        {{-- Grid --}}
        @if($products->count())
          <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($products as $product)
              @include('products.partials.card', ['product' => $product])
            @endforeach
          </div>

          {{-- Pagination --}}
          {{-- LESSON: withQueryString() keeps all filters in pagination links --}}
          <div class="mt-10">
            {{ $products->links('pagination::tailwind') }}
          </div>
        @else
          <div class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-16 h-16 bg-stone-800 rounded-full flex items-center justify-center mb-4">
              <svg class="w-7 h-7 text-stone-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <p class="text-stone-400 font-medium">No products found</p>
            <p class="text-stone-600 text-sm mt-1">Try adjusting your filters</p>
            <a href="{{ route('products.index') }}" class="mt-4 text-accent text-sm hover:underline">Clear filters</a>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection