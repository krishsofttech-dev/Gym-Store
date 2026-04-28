@extends('layouts.app')
@section('title', 'My Wishlist')

@section('content')
<div class="pt-24 min-h-screen">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <span class="section-label">Saved Items</span>
    <h1 class="font-display text-5xl tracking-wide mb-10">MY WISHLIST</h1>

    @if($wishlisted->count())
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        @foreach($wishlisted as $entry)
          @if($entry->product)
            @include('products.partials.card', ['product' => $entry->product])
          @endif
        @endforeach
      </div>

      <div class="mt-10">
        {{ $wishlisted->links('pagination::tailwind') }}
      </div>
    @else
      <div class="flex flex-col items-center justify-center py-24 text-center">
        <div class="w-20 h-20 bg-stone-900 border border-stone-800 rounded-full flex items-center justify-center mb-6">
          <svg class="w-8 h-8 text-stone-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
          </svg>
        </div>
        <h2 class="font-display text-3xl tracking-wide mb-2">WISHLIST IS EMPTY</h2>
        <p class="text-stone-500 mb-8">Save products you love by clicking the heart icon.</p>
        <a href="{{ route('products.index') }}" class="btn-primary">Browse Products</a>
      </div>
    @endif

  </div>
</div>
@endsection