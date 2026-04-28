@extends('layouts.app')
@section('title', 'Checkout')

@section('content')
<div class="pt-24 min-h-screen">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <span class="section-label">Almost there</span>
    <h1 class="font-display text-5xl tracking-wide mb-10">CHECKOUT</h1>

    <form method="POST" action="{{ route('checkout.process') }}" id="checkout-form">
      @csrf
      <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

        {{-- =====================================================
             LEFT: Shipping + Payment (3 cols)
        ===================================================== --}}
        <div class="lg:col-span-3 space-y-6">

          {{-- Shipping address --}}
          <div class="bg-stone-900 border border-stone-800 rounded-2xl p-6">
            <h2 class="font-display text-xl tracking-wide mb-5">SHIPPING ADDRESS</h2>

            {{-- Saved addresses --}}
            @if($addresses->count())
            <div class="mb-5" x-data="{ showSaved: true }">
              <div class="space-y-2 mb-4" x-show="showSaved">
                @foreach($addresses as $addr)
                <label class="flex items-start gap-3 p-3 rounded-xl border border-stone-700 cursor-pointer hover:border-stone-500 transition-colors has-[:checked]:border-accent has-[:checked]:bg-accent/5">
                  <input type="radio" name="saved_address" value="{{ $addr->id }}"
                    {{ $addr->is_default ? 'checked' : '' }}
                    class="mt-0.5 accent-yellow-400"
                    onchange="fillAddress({{ $addr->toJson() }})">
                  <div>
                    <p class="text-white text-sm font-medium">{{ $addr->name }}</p>
                    <p class="text-stone-500 text-xs mt-0.5">{{ $addr->full_address }}</p>
                  </div>
                </label>
                @endforeach
              </div>
              <button type="button" @click="showSaved = !showSaved"
                      class="text-xs text-accent hover:underline">
                <span x-text="showSaved ? '+ Enter new address' : '← Use saved address'"></span>
              </button>
            </div>
            @endif

            {{-- Address fields --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Full Name *</label>
                <input type="text" name="shipping_name" id="f-name"
                  value="{{ old('shipping_name', auth()->user()->name) }}"
                  class="input-dark @error('shipping_name') border-red-500 @enderror" required>
                @error('shipping_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
              </div>
              <div>
                <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Email *</label>
                <input type="email" name="shipping_email" id="f-email"
                  value="{{ old('shipping_email', auth()->user()->email) }}"
                  class="input-dark @error('shipping_email') border-red-500 @enderror" required>
              </div>
              <div>
                <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Phone</label>
                <input type="text" name="shipping_phone" id="f-phone"
                  value="{{ old('shipping_phone', auth()->user()->phone) }}"
                  class="input-dark">
              </div>
              <div class="sm:col-span-2">
                <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Address Line 1 *</label>
                <input type="text" name="shipping_address_line1" id="f-addr1"
                  value="{{ old('shipping_address_line1') }}"
                  class="input-dark @error('shipping_address_line1') border-red-500 @enderror" required>
              </div>
              <div class="sm:col-span-2">
                <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Address Line 2</label>
                <input type="text" name="shipping_address_line2" id="f-addr2"
                  value="{{ old('shipping_address_line2') }}"
                  class="input-dark">
              </div>
              <div>
                <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">City *</label>
                <input type="text" name="shipping_city" id="f-city"
                  value="{{ old('shipping_city') }}"
                  class="input-dark @error('shipping_city') border-red-500 @enderror" required>
              </div>
              <div>
                <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">State / Province</label>
                <input type="text" name="shipping_state" id="f-state"
                  value="{{ old('shipping_state') }}"
                  class="input-dark">
              </div>
              <div>
                <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Postal Code *</label>
                <input type="text" name="shipping_postal_code" id="f-postal"
                  value="{{ old('shipping_postal_code') }}"
                  class="input-dark" required>
              </div>
              <div>
                <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Country</label>
                <select name="shipping_country" id="f-country" class="input-dark">
                  <option value="LK" selected>Sri Lanka</option>
                  <option value="IN">India</option>
                  <option value="US">United States</option>
                  <option value="GB">United Kingdom</option>
                  <option value="AU">Australia</option>
                </select>
              </div>
            </div>

            {{-- Notes --}}
            <div class="mt-4">
              <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Order Notes (optional)</label>
              <textarea name="customer_notes" rows="2" placeholder="Special delivery instructions..."
                class="input-dark resize-none">{{ old('customer_notes') }}</textarea>
            </div>
          </div>

          {{-- Payment --}}
          <div class="bg-stone-900 border border-stone-800 rounded-2xl p-6">
            <h2 class="font-display text-xl tracking-wide mb-5">PAYMENT METHOD</h2>

            <div class="space-y-3" x-data="{ method: 'cod' }">
              {{-- Cash on delivery --}}
              <label class="flex items-center gap-3 p-4 rounded-xl border cursor-pointer transition-colors"
                     :class="method === 'cod' ? 'border-accent bg-accent/5' : 'border-stone-700 hover:border-stone-500'">
                <input type="radio" name="payment_method" value="cod" x-model="method" class="accent-yellow-400">
                <div>
                  <p class="text-white text-sm font-medium">Cash on Delivery</p>
                  <p class="text-stone-500 text-xs">Pay when your order arrives</p>
                </div>
              </label>

              {{-- Stripe --}}
              <label class="flex items-center gap-3 p-4 rounded-xl border cursor-pointer transition-colors"
                     :class="method === 'stripe' ? 'border-accent bg-accent/5' : 'border-stone-700 hover:border-stone-500'">
                <input type="radio" name="payment_method" value="stripe" x-model="method" class="accent-yellow-400">
                <div>
                  <p class="text-white text-sm font-medium">Credit / Debit Card</p>
                  <p class="text-stone-500 text-xs">Secured by Stripe</p>
                </div>
              </label>

              {{-- Stripe card element --}}
              <div x-show="method === 'stripe'" x-transition class="pt-2">
                <div id="card-element" class="input-dark py-3.5"></div>
                <div id="card-errors" class="text-red-400 text-xs mt-2"></div>
                <input type="hidden" name="stripe_payment_intent" id="stripe-pi">
              </div>
            </div>
          </div>
        </div>

        {{-- =====================================================
             RIGHT: Order summary (2 cols)
        ===================================================== --}}
        <div class="lg:col-span-2">
          <div class="bg-stone-900 border border-stone-800 rounded-2xl p-6 sticky top-24">
            <h2 class="font-display text-xl tracking-wide mb-5">ORDER SUMMARY</h2>

            {{-- Items --}}
            <div class="space-y-3 mb-5">
              @foreach($cart->items as $item)
              <div class="flex gap-3">
                <div class="relative flex-shrink-0">
                  <img src="{{ $item->product->thumbnail_url }}" class="w-12 h-12 rounded-lg object-cover bg-stone-800">
                  <span class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-stone-700 text-white text-xs rounded-full flex items-center justify-center leading-none">
                    {{ $item->quantity }}
                  </span>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-white text-xs font-medium truncate">{{ $item->product->name }}</p>
                  <p class="text-stone-500 text-xs">Rs. {{ number_format((float)$item->unit_price, 2) }}</p>
                </div>
                <span class="text-white text-xs font-medium flex-shrink-0">{{ $item->formatted_line_total }}</span>
              </div>
              @endforeach
            </div>

            {{-- Totals --}}
            <div class="border-t border-stone-800 pt-4 space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-stone-500">Subtotal</span>
                <span class="text-white">Rs. {{ number_format($subtotal, 2) }}</span>
              </div>
              @if($discount > 0)
              <div class="flex justify-between text-sm">
                <span class="text-emerald-400">Discount</span>
                <span class="text-emerald-400">− Rs. {{ number_format($discount, 2) }}</span>
              </div>
              @endif
              <div class="flex justify-between text-sm">
                <span class="text-stone-500">Shipping</span>
                <span class="{{ $shipping == 0 ? 'text-emerald-400' : 'text-white' }}">
                  {{ $shipping == 0 ? 'Free' : 'Rs. '.number_format($shipping, 2) }}
                </span>
              </div>
              <div class="flex justify-between border-t border-stone-800 pt-3 mt-2">
                <span class="text-white font-semibold">Total</span>
                <span class="font-display text-2xl text-white">Rs. {{ number_format($total, 2) }}</span>
              </div>
            </div>

            {{-- Submit --}}
            <button type="submit" id="place-order-btn"
                    class="mt-6 w-full bg-accent text-stone-950 font-semibold py-4 rounded-xl
                           hover:bg-yellow-300 transition-all hover:scale-[1.02] uppercase tracking-wide text-sm
                           disabled:opacity-50 disabled:cursor-not-allowed">
              Place Order
            </button>

            <p class="text-center text-stone-600 text-xs mt-3 flex items-center justify-center gap-1.5">
              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
              Secured checkout
            </p>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('head')
<script src="https://js.stripe.com/v3/"></script>
@endpush

@push('scripts')
<script>
// Fill address fields from saved address
window.fillAddress = function(addr) {
    document.getElementById('f-name').value    = addr.name   || ''
    document.getElementById('f-phone').value   = addr.phone  || ''
    document.getElementById('f-addr1').value   = addr.address_line1 || ''
    document.getElementById('f-addr2').value   = addr.address_line2 || ''
    document.getElementById('f-city').value    = addr.city   || ''
    document.getElementById('f-state').value   = addr.state  || ''
    document.getElementById('f-postal').value  = addr.postal_code || ''
    document.getElementById('f-country').value = addr.country || 'LK'
}

// Stripe integration
const stripeKey = '{{ config("services.stripe.key") }}'
if (stripeKey) {
    const stripe  = Stripe(stripeKey)
    const elements = stripe.elements()
    const card = elements.create('card', {
        style: {
            base: {
                color: '#f5f3ee',
                fontFamily: '"DM Sans", sans-serif',
                fontSize: '15px',
                '::placeholder': { color: '#57534e' },
            },
        },
    })
    card.mount('#card-element')
    card.on('change', e => {
        document.getElementById('card-errors').textContent = e.error ? e.error.message : ''
    })

    document.getElementById('checkout-form').addEventListener('submit', async function(e) {
        const method = document.querySelector('[name="payment_method"]:checked')?.value
        if (method !== 'stripe') return // let form submit normally for COD

        e.preventDefault()
        const btn = document.getElementById('place-order-btn')
        btn.disabled = true
        btn.textContent = 'Processing...'

        const { paymentMethod, error } = await stripe.createPaymentMethod({ type: 'card', card })
        if (error) {
            document.getElementById('card-errors').textContent = error.message
            btn.disabled = false
            btn.textContent = 'Place Order'
            return
        }

        document.getElementById('stripe-pi').value = paymentMethod.id
        this.submit()
    })
}
</script>
@endpush