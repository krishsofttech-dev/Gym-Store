@extends('layouts.app')
@section('title', 'My Addresses')

@section('content')
<div class="pt-24 min-h-screen">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <span class="section-label">Account</span>
    <h1 class="font-display text-5xl tracking-wide mb-10">MY ADDRESSES</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

      {{-- Sidebar nav --}}
      <aside class="lg:col-span-1">
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-4 space-y-1">
          @foreach([['profile.show','Profile'],['profile.addresses','Addresses'],['orders.index','My Orders'],['wishlist.index','Wishlist']] as [$r,$l])
          <a href="{{ route($r) }}"
             class="flex items-center justify-between px-4 py-2.5 rounded-xl text-sm transition-colors
                    {{ request()->routeIs($r) ? 'bg-accent text-stone-950 font-semibold' : 'text-stone-400 hover:bg-stone-800 hover:text-white' }}">
            {{ $l }}
            <svg class="w-3.5 h-3.5 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </a>
          @endforeach
        </div>
      </aside>

      <div class="lg:col-span-2 space-y-5">

        {{-- Existing addresses --}}
        @forelse($addresses as $address)
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-5 flex items-start justify-between gap-4
                    {{ $address->is_default ? 'border-accent/40' : '' }}">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <p class="text-white font-medium text-sm">{{ $address->name }}</p>
              @if($address->label)
                <span class="text-xs bg-stone-800 text-stone-400 px-2 py-0.5 rounded-full">{{ $address->label }}</span>
              @endif
              @if($address->is_default)
                <span class="text-xs bg-accent/20 text-accent px-2 py-0.5 rounded-full">Default</span>
              @endif
            </div>
            <p class="text-stone-400 text-sm leading-relaxed">{{ $address->full_address }}</p>
            @if($address->phone)
              <p class="text-stone-600 text-xs mt-1">{{ $address->phone }}</p>
            @endif
          </div>
          <form method="POST" action="{{ route('profile.addresses.destroy', $address) }}"
                onsubmit="return confirm('Remove this address?')">
            @csrf @method('DELETE')
            <button type="submit" class="text-stone-600 hover:text-red-400 transition-colors flex-shrink-0">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
          </form>
        </div>
        @empty
          <p class="text-stone-600 text-sm text-center py-4">No saved addresses yet.</p>
        @endforelse

        {{-- Add new address form --}}
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-6" x-data="{ open: false }">
          <button @click="open = !open"
                  class="flex items-center gap-2 text-sm font-medium text-stone-400 hover:text-white transition-colors w-full">
            <svg class="w-4 h-4 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add New Address
            <svg class="w-3.5 h-3.5 ml-auto transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>

          <div x-show="open" x-transition class="mt-5">
            <form method="POST" action="{{ route('profile.addresses.store') }}" class="space-y-4">
              @csrf
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs text-stone-500 uppercase tracking-widests mb-1.5">Label</label>
                  <input type="text" name="label" placeholder="Home / Office / Gym" class="input-dark text-sm">
                </div>
                <div>
                  <label class="block text-xs text-stone-500 uppercase tracking-widests mb-1.5">Recipient Name *</label>
                  <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" class="input-dark text-sm" required>
                </div>
                <div>
                  <label class="block text-xs text-stone-500 uppercase tracking-widests mb-1.5">Phone</label>
                  <input type="text" name="phone" class="input-dark text-sm">
                </div>
                <div class="col-span-2">
                  <label class="block text-xs text-stone-500 uppercase tracking-widests mb-1.5">Address Line 1 *</label>
                  <input type="text" name="address_line1" class="input-dark text-sm" required>
                </div>
                <div class="col-span-2">
                  <label class="block text-xs text-stone-500 uppercase tracking-widests mb-1.5">Address Line 2</label>
                  <input type="text" name="address_line2" class="input-dark text-sm">
                </div>
                <div>
                  <label class="block text-xs text-stone-500 uppercase tracking-widests mb-1.5">City *</label>
                  <input type="text" name="city" class="input-dark text-sm" required>
                </div>
                <div>
                  <label class="block text-xs text-stone-500 uppercase tracking-widests mb-1.5">Postal Code *</label>
                  <input type="text" name="postal_code" class="input-dark text-sm" required>
                </div>
                <div>
                  <label class="block text-xs text-stone-500 uppercase tracking-widests mb-1.5">Country</label>
                  <select name="country" class="input-dark text-sm">
                    <option value="LK" selected>Sri Lanka</option>
                    <option value="IN">India</option>
                    <option value="US">United States</option>
                    <option value="GB">United Kingdom</option>
                  </select>
                </div>
              </div>
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_default" value="1" class="accent-yellow-400">
                <span class="text-sm text-stone-400">Set as default address</span>
              </label>
              <button type="submit" class="btn-primary text-sm">Save Address</button>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection