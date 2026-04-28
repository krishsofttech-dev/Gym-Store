@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<div class="pt-24 min-h-screen">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <span class="section-label">Account</span>
    <h1 class="font-display text-5xl tracking-wide mb-10">MY PROFILE</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

      {{-- Sidebar nav --}}
      <aside class="lg:col-span-1">
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-4 space-y-1">
          @php
          $links = [
            ['route' => 'profile.show',     'label' => 'Profile'],
            ['route' => 'profile.addresses','label' => 'Addresses'],
            ['route' => 'orders.index',     'label' => 'My Orders'],
            ['route' => 'wishlist.index',   'label' => 'Wishlist'],
          ];
          @endphp
          @foreach($links as $link)
          <a href="{{ route($link['route']) }}"
             class="flex items-center justify-between px-4 py-2.5 rounded-xl text-sm transition-colors
                    {{ request()->routeIs($link['route']) ? 'bg-accent text-stone-950 font-semibold' : 'text-stone-400 hover:bg-stone-800 hover:text-white' }}">
            {{ $link['label'] }}
            <svg class="w-3.5 h-3.5 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </a>
          @endforeach

          <div class="pt-2 mt-2 border-t border-stone-800">
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="w-full text-left px-4 py-2.5 rounded-xl text-sm text-stone-600 hover:text-red-400 hover:bg-stone-800 transition-colors">
                Logout
              </button>
            </form>
          </div>
        </div>
      </aside>

      {{-- Main content --}}
      <div class="lg:col-span-2 space-y-6">

        {{-- Profile info --}}
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-6">
          <h2 class="font-display text-xl tracking-wide mb-5 text-stone-400">PERSONAL INFO</h2>
          <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf @method('PATCH')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                       class="input-dark @error('name') border-red-500 @enderror" required>
                @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-xs text-stone-500 uppercase tracking-widests mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                       class="input-dark @error('email') border-red-500 @enderror" required>
                @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-xs text-stone-500 uppercase tracking-widests mb-1.5">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                       class="input-dark" placeholder="+94 77 123 4567">
              </div>
            </div>
            <button type="submit" class="btn-primary text-sm">Save Changes</button>
          </form>
        </div>

        {{-- Change password --}}
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-6">
          <h2 class="font-display text-xl tracking-wide mb-5 text-stone-400">CHANGE PASSWORD</h2>
          <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
            @csrf @method('PATCH')
            <div>
              <label class="block text-xs text-stone-500 uppercase tracking-widests mb-1.5">Current Password</label>
              <input type="password" name="current_password"
                     class="input-dark @error('current_password') border-red-500 @enderror" required>
              @error('current_password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs text-stone-500 uppercase tracking-widests mb-1.5">New Password</label>
                <input type="password" name="password"
                       class="input-dark @error('password') border-red-500 @enderror" required>
                @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-xs text-stone-500 uppercase tracking-widests mb-1.5">Confirm Password</label>
                <input type="password" name="password_confirmation" class="input-dark" required>
              </div>
            </div>
            <button type="submit" class="btn-primary text-sm">Update Password</button>
          </form>
        </div>

        {{-- Recent orders summary --}}
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-6">
          <div class="flex items-center justify-between mb-5">
            <h2 class="font-display text-xl tracking-wide text-stone-400">RECENT ORDERS</h2>
            <a href="{{ route('orders.index') }}" class="text-xs text-accent hover:underline">View all </a>
          </div>
          @forelse($user->orders->take(3) as $order)
          <div class="flex items-center justify-between py-3 border-b border-stone-800 last:border-0">
            <div>
              <p class="text-white text-sm font-mono">{{ $order->order_number }}</p>
              <p class="text-stone-500 text-xs mt-0.5">{{ $order->created_at->format('d M Y') }}</p>
            </div>
            <div class="flex items-center gap-3">
              @php $sl = $order->status_label; @endphp
              <span class="text-xs px-2 py-0.5 rounded-full
                {{ match($sl['color']) {'green'=>'bg-emerald-500/15 text-emerald-400','blue'=>'bg-blue-500/15 text-blue-400','yellow'=>'bg-yellow-500/15 text-yellow-400','red'=>'bg-red-500/15 text-red-400',default=>'bg-stone-700 text-stone-400'} }}">
                {{ $sl['label'] }}
              </span>
              <span class="text-white text-sm font-medium">{{ $order->formatted_total }}</span>
            </div>
          </div>
          @empty
          <p class="text-stone-600 text-sm text-center py-4">No orders yet.</p>
          @endforelse
        </div>

      </div>
    </div>
  </div>
</div>
@endsection