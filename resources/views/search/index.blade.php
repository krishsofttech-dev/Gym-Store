@extends('layouts.app')
@section('title', 'Search: ' . $term)
@section('content')
<div class="pt-24 min-h-screen">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <span class="section-label">Search results</span>
    <h1 class="font-display text-5xl tracking-wide mb-2">
      {{ $term ? '"'.strtoupper($term).'"' : 'SEARCH' }}
    </h1>
    <p class="text-stone-500 text-sm mb-10">
      @if($products instanceof \Illuminate\Pagination\LengthAwarePaginator)
        {{ $products->total() }} results found
      @elseif(strlen($term) < 2)
        Enter at least 2 characters to search
      @endif
    </p>

    {{-- Search bar --}}
    <form action="{{ route('search') }}" method="GET" class="max-w-lg mb-12">
      <div class="flex gap-3">
        <input type="text" name="q" value="{{ $term }}" placeholder="Search products..."
               class="input-dark flex-1" autofocus>
        <button type="submit" class="btn-primary px-6">Search</button>
      </div>
    </form>

    @if($products instanceof \Illuminate\Pagination\LengthAwarePaginator && $products->count())
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        @foreach($products as $product)
          @include('products.partials.card', ['product' => $product])
        @endforeach
      </div>
      <div class="mt-10">{{ $products->links('pagination::tailwind') }}</div>
    @elseif(strlen($term) >= 2)
      <div class="text-center py-20">
        <p class="text-stone-500">No results for "{{ $term }}"</p>
        <a href="{{ route('products.index') }}" class="text-accent text-sm hover:underline mt-3 inline-block">Browse all products</a>
      </div>
    @endif

  </div>
</div>
@endsection