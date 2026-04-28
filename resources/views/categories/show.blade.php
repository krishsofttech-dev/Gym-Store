@extends('layouts.app')
@section('title', $category->name)

@section('content')
<div class="pt-24 min-h-screen">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-stone-600 mb-6">
      <a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a>
      <span>/</span>
      <span class="text-stone-400">{{ $category->name }}</span>
    </nav>

    <span class="section-label">Category</span>
    <h1 class="font-display text-5xl tracking-wide mb-3">{{ strtoupper($category->name) }}</h1>
    @if($category->description)
      <p class="text-stone-500 mb-8 max-w-xl">{{ $category->description }}</p>
    @endif

    {{-- Subcategories --}}
    @if($subcategories->count())
    <div class="flex flex-wrap gap-2 mb-8">
      @foreach($subcategories as $sub)
        <a href="{{ route('categories.show', $sub) }}"
           class="px-4 py-2 bg-stone-800 border border-stone-700 rounded-full text-sm text-stone-300 hover:border-accent hover:text-white transition-colors">
          {{ $sub->name }}
        </a>
      @endforeach
    </div>
    @endif

    {{-- Sort --}}
    <div class="flex items-center justify-between mb-6">
      <p class="text-stone-500 text-sm">{{ $products->total() }} products</p>
      <select onchange="window.location='{{ route('categories.show', $category) }}?sort='+this.value"
              class="bg-stone-800 border border-stone-700 text-stone-300 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-accent cursor-pointer">
        <option value="newest"    {{ request('sort','newest') === 'newest'    ? 'selected':'' }}>Newest</option>
        <option value="popular"   {{ request('sort') === 'popular'            ? 'selected':'' }}>Popular</option>
        <option value="price_asc" {{ request('sort') === 'price_asc'          ? 'selected':'' }}>Price ↑</option>
        <option value="price_desc"{{ request('sort') === 'price_desc'         ? 'selected':'' }}>Price ↓</option>
      </select>
    </div>

    @if($products->count())
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        @foreach($products as $product)
          @include('products.partials.card', ['product' => $product])
        @endforeach
      </div>
      <div class="mt-10">{{ $products->withQueryString()->links('pagination::tailwind') }}</div>
    @else
      <div class="text-center py-20">
        <p class="text-stone-500">No products in this category yet.</p>
      </div>
    @endif

  </div>
</div>
@endsection