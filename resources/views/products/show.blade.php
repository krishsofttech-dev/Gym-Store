@extends('layouts.app')

@section('title', $product->name)
@section('meta_description', $product->short_description ?? Str::limit($product->description, 160))

@section('content')
<div class="pt-24 min-h-screen">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-stone-600 mb-8">
      <a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a>
      <span>/</span>
      <a href="{{ route('categories.show', $product->category) }}" class="hover:text-white transition-colors">
        {{ $product->category->name }}
      </a>
      <span>/</span>
      <span class="text-stone-400">{{ Str::limit($product->name, 40) }}</span>
    </nav>

    {{-- =====================================================
         PRODUCT MAIN SECTION
    ===================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-20">

      {{-- LEFT: Image gallery + 3D viewer --}}
      <div x-data="{ activeImage: '{{ $product->thumbnail_url }}', show3d: false }">

        {{-- Main image / 3D canvas toggle --}}
        <div class="relative aspect-square bg-stone-900 rounded-2xl overflow-hidden mb-3 border border-stone-800">

          {{-- Product image --}}
          <img
            :src="activeImage"
            :class="show3d ? 'opacity-0' : 'opacity-100'"
            alt="{{ $product->name }}"
            class="w-full h-full object-cover transition-opacity duration-300"
          >

          {{-- Three.js 3D product viewer canvas --}}
          <canvas
            id="product-canvas"
            :class="show3d ? 'opacity-100' : 'opacity-0'"
            class="absolute inset-0 w-full h-full transition-opacity duration-300"
          ></canvas>

          {{-- 3D toggle button --}}
          <button
            @click="show3d = !show3d; show3d && init3DViewer()"
            class="absolute bottom-4 right-4 bg-stone-900/80 backdrop-blur-sm border border-stone-700
                   text-xs text-stone-300 px-3 py-1.5 rounded-lg hover:border-accent hover:text-accent transition-all"
          >
            <span x-text="show3d ? '📷 Photo' : '🔮 3D View'"></span>
          </button>

          {{-- Badges --}}
          <div class="absolute top-4 left-4 flex flex-col gap-2">
            @if($product->is_new)
              <span class="bg-accent text-stone-950 text-xs font-bold px-3 py-1 rounded-full">New</span>
            @endif
            @if($product->isOnSale())
              <span class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                Save {{ $product->discount_percentage }}
              </span>
            @endif
          </div>
        </div>

        {{-- Thumbnail strip --}}
        @if($product->image_urls && count($product->image_urls))
        <div class="flex gap-2 overflow-x-auto pb-1">
          <button
            @click="activeImage = '{{ $product->thumbnail_url }}'; show3d = false"
            class="w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden border-2 transition-colors"
            :class="activeImage === '{{ $product->thumbnail_url }}' ? 'border-accent' : 'border-stone-800'"
          >
            <img src="{{ $product->thumbnail_url }}" class="w-full h-full object-cover">
          </button>
          @foreach($product->image_urls as $url)
          <button
            @click="activeImage = '{{ $url }}'; show3d = false"
            class="w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden border-2 transition-colors"
            :class="activeImage === '{{ $url }}' ? 'border-accent' : 'border-stone-800'"
          >
            <img src="{{ $url }}" class="w-full h-full object-cover">
          </button>
          @endforeach
        </div>
        @endif
      </div>

      {{-- RIGHT: Product info + add to cart --}}
      <div x-data="{ quantity: 1 }">

        {{-- Brand + Category --}}
        <div class="flex items-center gap-3 mb-3">
          @if($product->brand)
            <span class="text-accent text-xs font-semibold uppercase tracking-widest">{{ $product->brand }}</span>
            <span class="text-stone-700">·</span>
          @endif
          <a href="{{ route('categories.show', $product->category) }}"
             class="text-stone-500 text-xs uppercase tracking-widest hover:text-stone-300 transition-colors">
            {{ $product->category->name }}
          </a>
        </div>

        <h1 class="font-display text-4xl lg:text-5xl tracking-wide text-white mb-4">
          {{ strtoupper($product->name) }}
        </h1>

        {{-- Rating --}}
        @if($product->reviews_count > 0)
        <div class="flex items-center gap-2 mb-4">
          <div class="flex text-accent">
            @for($i = 1; $i <= 5; $i++)
              <span class="text-lg">{{ $i <= round($product->average_rating) ? '★' : '☆' }}</span>
            @endfor
          </div>
          <span class="text-stone-400 text-sm">{{ number_format((float)$product->average_rating, 1) }}</span>
          <span class="text-stone-600 text-sm">({{ $product->reviews_count }} reviews)</span>
        </div>
        @endif

        {{-- Price --}}
        <div class="flex items-baseline gap-3 mb-6">
          <span class="font-display text-4xl text-white">{{ $product->formatted_price }}</span>
          @if($product->isOnSale())
            <span class="text-stone-600 text-xl line-through">{{ $product->formatted_compare_price }}</span>
            <span class="bg-red-500/20 text-red-400 text-sm px-2 py-0.5 rounded-full">
              Save {{ $product->discount_percentage }}
            </span>
          @endif
        </div>

        {{-- Short description --}}
        @if($product->short_description)
          <p class="text-stone-400 leading-relaxed mb-6">{{ $product->short_description }}</p>
        @endif

        {{-- Stock status --}}
        <div class="flex items-center gap-2 mb-6">
          @if($product->isOutOfStock())
            <span class="w-2 h-2 rounded-full bg-red-500"></span>
            <span class="text-red-400 text-sm">Out of stock</span>
          @elseif($product->stock_quantity <= 5 && $product->track_quantity)
            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
            <span class="text-amber-400 text-sm">Only {{ $product->stock_quantity }} left</span>
          @else
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            <span class="text-emerald-400 text-sm">In stock</span>
          @endif
        </div>

        {{-- Quantity + Add to cart --}}
        @if(!$product->isOutOfStock())
        <div class="flex items-center gap-3 mb-4">
          {{-- Qty stepper --}}
          <div class="flex items-center bg-stone-800 border border-stone-700 rounded-xl overflow-hidden">
            <button @click="quantity = Math.max(1, quantity - 1)"
                    class="px-4 py-3 text-stone-400 hover:text-white hover:bg-stone-700 transition-colors text-lg font-light">
              −
            </button>
            <span x-text="quantity" class="px-4 py-3 text-white font-medium min-w-[3rem] text-center"></span>
            <button @click="quantity = Math.min(99, quantity + 1)"
                    class="px-4 py-3 text-stone-400 hover:text-white hover:bg-stone-700 transition-colors text-lg font-light">
              +
            </button>
          </div>

          {{-- Add to cart --}}
          <button
            @click="addToCart({{ $product->id }}, quantity, $el)"
            class="flex-1 bg-accent text-stone-950 font-semibold py-3 px-6 rounded-xl
                   hover:bg-yellow-300 transition-all hover:scale-[1.02] active:scale-[0.98]
                   uppercase tracking-wide text-sm"
          >
            Add to Cart
          </button>

          {{-- Wishlist --}}
          @auth
          <button
            onclick="toggleWishlist({{ $product->id }}, this)"
            data-wishlisted="{{ $isWishlisted ? 'true' : 'false' }}"
            class="p-3 border rounded-xl transition-all
                   {{ $isWishlisted ? 'border-red-500 text-red-400' : 'border-stone-700 text-stone-500 hover:border-stone-500 hover:text-white' }}"
          >
            <svg class="w-5 h-5" fill="{{ $isWishlisted ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
          </button>
          @endauth
        </div>

        {{-- Free shipping badge --}}
        @if((float)$product->price >= 5000)
          <p class="text-emerald-400 text-xs flex items-center gap-1.5 mb-4">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Free shipping on this item
          </p>
        @endif
        @endif

        {{-- Product specs --}}
        <div class="border-t border-stone-800 pt-6 mt-6 space-y-3">
          @if($product->sku)
          <div class="flex justify-between text-sm">
            <span class="text-stone-500">SKU</span>
            <span class="text-stone-300 font-mono">{{ $product->sku }}</span>
          </div>
          @endif
          @if($product->weight)
          <div class="flex justify-between text-sm">
            <span class="text-stone-500">Weight</span>
            <span class="text-stone-300">{{ $product->weight }} kg</span>
          </div>
          @endif
          @if($product->dimensions)
          <div class="flex justify-between text-sm">
            <span class="text-stone-500">Dimensions</span>
            <span class="text-stone-300">{{ $product->dimensions }}</span>
          </div>
          @endif
        </div>
      </div>
    </div>

    {{-- =====================================================
         DESCRIPTION TAB
    ===================================================== --}}
    <div class="mb-20" x-data="{ tab: 'description' }">
      <div class="flex border-b border-stone-800 mb-8 gap-6">
        @foreach(['description' => 'Description', 'reviews' => 'Reviews (' . $product->reviews_count . ')'] as $key => $label)
        <button
          @click="tab = '{{ $key }}'"
          class="pb-3 text-sm font-medium uppercase tracking-widest transition-colors border-b-2 -mb-px"
          :class="tab === '{{ $key }}' ? 'text-white border-accent' : 'text-stone-500 border-transparent hover:text-stone-300'"
        >
          {{ $label }}
        </button>
        @endforeach
      </div>

      {{-- Description --}}
      <div x-show="tab === 'description'" class="prose prose-invert prose-sm max-w-none text-stone-400 leading-relaxed">
        {!! nl2br(e($product->description)) !!}
      </div>

      {{-- Reviews --}}
      <div x-show="tab === 'reviews'">

        {{-- Write a review --}}
        @auth
          @if(!$userReview)
          <div class="bg-stone-900 border border-stone-800 rounded-2xl p-6 mb-8">
            <h3 class="font-display text-xl tracking-wide mb-4">WRITE A REVIEW</h3>
            <form method="POST" action="{{ route('reviews.store', $product) }}">
              @csrf
              {{-- Star rating --}}
              <div class="flex gap-2 mb-4" x-data="{ rating: 0, hover: 0 }">
                @for($i = 1; $i <= 5; $i++)
                <button type="button"
                  @click="rating = {{ $i }}"
                  @mouseenter="hover = {{ $i }}"
                  @mouseleave="hover = 0"
                  class="text-3xl transition-colors"
                  :class="(hover || rating) >= {{ $i }} ? 'text-accent' : 'text-stone-700'"
                >★</button>
                @endfor
                <input type="hidden" name="rating" x-bind:value="rating">
              </div>
              <input type="text" name="title" placeholder="Review title (optional)"
                class="input-dark mb-3">
              <textarea name="body" rows="3" placeholder="Share your experience..."
                class="input-dark resize-none mb-4"></textarea>
              <button type="submit" class="btn-primary">Submit Review</button>
            </form>
          </div>
          @endif
        @else
          <div class="bg-stone-900/50 border border-stone-800 rounded-xl p-4 mb-6 text-center">
            <a href="{{ route('login') }}" class="text-accent text-sm hover:underline">Log in</a>
            <span class="text-stone-500 text-sm"> to write a review</span>
          </div>
        @endauth

        {{-- Reviews list --}}
        @forelse($product->reviews as $review)
        <div class="border-b border-stone-800 pb-6 mb-6 last:border-0">
          <div class="flex items-start justify-between mb-2">
            <div>
              <div class="flex items-center gap-2">
                <span class="text-white font-medium text-sm">{{ $review->user->name }}</span>
                @if($review->verified_purchase)
                  <span class="text-xs bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-full">Verified Purchase</span>
                @endif
              </div>
              <div class="flex text-accent text-sm mt-1">
                @for($i = 1; $i <= 5; $i++)
                  {{ $i <= $review->rating ? '★' : '☆' }}
                @endfor
              </div>
            </div>
            <span class="text-stone-600 text-xs">{{ $review->created_at->diffForHumans() }}</span>
          </div>
          @if($review->title)
            <p class="text-white font-medium text-sm mb-1">{{ $review->title }}</p>
          @endif
          @if($review->body)
            <p class="text-stone-400 text-sm leading-relaxed">{{ $review->body }}</p>
          @endif
        </div>
        @empty
          <p class="text-stone-500 text-center py-8">No reviews yet. Be the first!</p>
        @endforelse
      </div>
    </div>

    {{-- Related products --}}
    @if($related->count())
    <div>
      <span class="section-label">You may also like</span>
      <h2 class="font-display text-3xl tracking-wide mb-8">RELATED PRODUCTS</h2>
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
        @foreach($related as $rel)
          @include('products.partials.card', ['product' => $rel])
        @endforeach
      </div>
    </div>
    @endif

  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
/**
 * LESSON: 3D Product Viewer
 * When the user clicks "3D View", we spin up a Three.js scene
 * that shows an abstract representation of the product category.
 * This gives the page a premium, interactive feel without needing
 * actual 3D models — we build shapes from primitives.
 */
window.init3DViewer = function() {
    const canvas = document.getElementById('product-canvas')
    if (!canvas || canvas._initialized) return
    canvas._initialized = true

    const scene    = new THREE.Scene()
    const camera   = new THREE.PerspectiveCamera(50, 1, 0.1, 100)
    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true })

    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
    renderer.setSize(canvas.clientWidth, canvas.clientHeight)
    renderer.setClearColor(0x000000, 0)

    camera.position.set(0, 0, 4)

    // Lighting
    scene.add(new THREE.AmbientLight(0xffffff, 0.4))
    const key = new THREE.DirectionalLight(0xe8ff47, 2)
    key.position.set(3, 3, 3)
    scene.add(key)
    const fill = new THREE.DirectionalLight(0x4488ff, 1)
    fill.position.set(-3, -2, 2)
    scene.add(fill)

    // Build a dumbbell/barbell shape from primitives
    const group = new THREE.Group()
    const metalMat = new THREE.MeshStandardMaterial({ color: 0x999999, metalness: 0.9, roughness: 0.15 })
    const darkMat  = new THREE.MeshStandardMaterial({ color: 0x111111, metalness: 0.7, roughness: 0.3 })

    // Bar
    group.add(Object.assign(new THREE.Mesh(new THREE.CylinderGeometry(0.05, 0.05, 2.6, 12), metalMat), { rotation: { z: Math.PI/2 } }))

    // Plates
    ;[-0.9, -1.1, 0.9, 1.1].forEach(x => {
        const p = new THREE.Mesh(new THREE.CylinderGeometry(0.45, 0.45, 0.14, 28), darkMat)
        p.rotation.z = Math.PI/2
        p.position.x = x
        group.add(p)
    })

    scene.add(group)

    // Wireframe overlay for style
    const wireMat = new THREE.MeshBasicMaterial({ color: 0xe8ff47, wireframe: true, opacity: 0.08, transparent: true })
    const wireSphere = new THREE.Mesh(new THREE.SphereGeometry(1.6, 16, 16), wireMat)
    scene.add(wireSphere)

    // Mouse drag rotation
    let isDragging = false, prevX = 0, prevY = 0
    canvas.addEventListener('mousedown', e => { isDragging = true; prevX = e.clientX; prevY = e.clientY })
    window.addEventListener('mouseup', () => isDragging = false)
    window.addEventListener('mousemove', e => {
        if (!isDragging) return
        group.rotation.y += (e.clientX - prevX) * 0.01
        group.rotation.x += (e.clientY - prevY) * 0.005
        prevX = e.clientX; prevY = e.clientY
    })

    const clock = new THREE.Clock()
    ;(function animate() {
        requestAnimationFrame(animate)
        if (!isDragging) group.rotation.y += 0.005
        wireSphere.rotation.y = clock.getElapsedTime() * 0.1
        renderer.render(scene, camera)
    })()
}
</script>
@endpush