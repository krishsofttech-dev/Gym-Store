@extends('layouts.app')

@section('title', 'Premium Gym Equipment')

@section('content')

{{-- ============================================================
     HERO SECTION — Full screen with Three.js 3D animation
     LESSON: The canvas is positioned behind the text content.
     Three.js renders a rotating 3D dumbbell into #hero-canvas.
     The text floats on top using z-index layering.
============================================================ --}}
<section class="relative min-h-screen flex items-center overflow-hidden">

    {{-- Three.js canvas — fills the entire hero --}}
    <canvas id="hero-canvas" class="absolute inset-0 w-full h-full"></canvas>

    {{-- Dark overlay so text stays readable over the 3D scene --}}
    <div class="absolute inset-0 bg-gradient-to-r from-stone-950 via-stone-950/80 to-transparent"></div>

    {{-- Hero text content --}}
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20">
        <div class="max-w-2xl">

            {{-- Eyebrow label --}}
            <div class="inline-flex items-center gap-2 bg-stone-800/60 border border-stone-700 rounded-full px-4 py-1.5 mb-6 hero-reveal" style="animation-delay:.1s">
                <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
                <span class="text-xs font-medium text-stone-300 tracking-widest uppercase">New Collection 2025</span>
            </div>

            {{-- Main headline --}}
            <h1 class="font-display text-6xl sm:text-7xl lg:text-8xl leading-none tracking-wider text-white mb-6 hero-reveal" style="animation-delay:.2s">
                FORGE<br>
                YOUR<br>
                <span class="text-accent">LIMITS</span>
            </h1>

            <p class="text-stone-400 text-lg leading-relaxed mb-10 hero-reveal" style="animation-delay:.35s">
                Premium gym equipment and accessories. Built for athletes who refuse to compromise on quality.
            </p>

            <div class="flex flex-wrap gap-4 hero-reveal" style="animation-delay:.5s">
                <a href="{{ route('products.index') }}"
                   class="bg-accent text-stone-950 font-semibold px-8 py-4 rounded-xl hover:bg-yellow-300 transition-all hover:scale-105 tracking-wide uppercase text-sm">
                    Shop Now
                </a>
                <a href="#featured"
                   class="border border-stone-600 text-stone-300 font-medium px-8 py-4 rounded-xl hover:border-stone-400 hover:text-white transition-all tracking-wide uppercase text-sm">
                    Explore
                </a>
            </div>

            {{-- Stats row --}}
            <div class="flex gap-10 mt-14 hero-reveal" style="animation-delay:.65s">
                <div>
                    <p class="font-display text-3xl text-white">500+</p>
                    <p class="text-stone-500 text-xs uppercase tracking-widest mt-1">Products</p>
                </div>
                <div class="w-px bg-stone-800"></div>
                <div>
                    <p class="font-display text-3xl text-white">10K+</p>
                    <p class="text-stone-500 text-xs uppercase tracking-widest mt-1">Athletes</p>
                </div>
                <div class="w-px bg-stone-800"></div>
                <div>
                    <p class="font-display text-3xl text-white">4.9★</p>
                    <p class="text-stone-500 text-xs uppercase tracking-widest mt-1">Rating</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Scroll indicator --}}
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-stone-600 animate-bounce">
        <span class="text-xs uppercase tracking-widest">Scroll</span>
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
</section>

{{-- ============================================================
     CATEGORIES STRIP
============================================================ --}}
<section class="py-16 border-y border-stone-800 bg-stone-900/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($categories as $category)
                <a href="{{ route('categories.show', $category) }}"
                   class="group flex flex-col items-center gap-3 p-4 rounded-xl border border-stone-800 hover:border-accent/50 hover:bg-stone-800/50 transition-all">
                    <div class="w-12 h-12 rounded-full bg-stone-800 group-hover:bg-accent/10 transition-colors flex items-center justify-center">
                        <span class="font-display text-lg text-accent">{{ strtoupper(substr($category->name, 0, 2)) }}</span>
                    </div>
                    <span class="text-xs text-stone-400 group-hover:text-white text-center transition-colors font-medium">
                        {{ $category->name }}
                    </span>
                    <span class="text-xs text-stone-600">{{ $category->products_count }} items</span>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     FEATURED PRODUCTS
============================================================ --}}
<section id="featured" class="py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between mb-12">
            <div>
                <p class="text-accent text-xs font-medium tracking-widest uppercase mb-2">Handpicked</p>
                <h2 class="font-display text-4xl lg:text-5xl tracking-wide">FEATURED</h2>
            </div>
            <a href="{{ route('products.index') }}" class="text-stone-400 hover:text-white text-sm transition-colors hidden sm:block">
                View all 
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($featured as $product)
                @include('products.partials.card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     BANNER — 3D parallax section
============================================================ --}}
<section class="relative py-32 overflow-hidden bg-stone-900 border-y border-stone-800">
    <canvas id="banner-canvas" class="absolute inset-0 w-full h-full opacity-40"></canvas>
    <div class="relative z-10 max-w-7xl mx-auto px-4 text-center">
        <h2 class="font-display text-5xl lg:text-7xl tracking-wider mb-6">
            FREE SHIPPING<br><span class="text-accent">OVER RS. 5000</span>
        </h2>
        <p class="text-stone-400 mb-8">On all orders island-wide. Same day dispatch on orders before 2PM.</p>
        <a href="{{ route('products.index') }}"
           class="inline-block bg-accent text-stone-950 font-semibold px-10 py-4 rounded-xl hover:bg-yellow-300 transition-all hover:scale-105 uppercase tracking-wide text-sm">
            Shop Now
        </a>
    </div>
</section>

{{-- ============================================================
     NEW ARRIVALS
============================================================ --}}
<section class="py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-12">
            <div>
                <p class="text-accent text-xs font-medium tracking-widest uppercase mb-2">Just In</p>
                <h2 class="font-display text-4xl lg:text-5xl tracking-wide">NEW ARRIVALS</h2>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($newArrivals as $product)
                @include('products.partials.card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>

@endsection

@push('scripts')
{{-- Three.js loaded from CDN then our custom scene script --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
/**
 * LESSON: Three.js Hero Scene
 * We create a 3D scene with:
 *   - A rotating dumbbell shape (built from basic geometries)
 *   - Floating particle field
 *   - Ambient + directional lighting
 *   - Mouse-tracking rotation for interactivity
 *
 * Core Three.js concepts:
 *   Scene    — the 3D world container
 *   Camera   — our viewpoint into the scene
 *   Renderer — draws the scene onto the <canvas>
 *   Mesh     — a 3D object (geometry + material)
 *   Geometry — the shape (CylinderGeometry, SphereGeometry, etc.)
 *   Material — the surface appearance (color, metalness, roughness)
 */
(function initHeroScene() {
    const canvas = document.getElementById('hero-canvas');
    if (!canvas) return;

    // --- Setup ---
    const scene    = new THREE.Scene();
    const camera   = new THREE.PerspectiveCamera(60, canvas.clientWidth / canvas.clientHeight, 0.1, 1000);
    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });

    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setSize(canvas.clientWidth, canvas.clientHeight);
    renderer.setClearColor(0x000000, 0); // transparent background

    camera.position.set(0, 0, 5);

    // --- Lighting ---
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.3);
    scene.add(ambientLight);

    const keyLight = new THREE.DirectionalLight(0xe8ff47, 2.0); // accent yellow
    keyLight.position.set(5, 5, 5);
    scene.add(keyLight);

    const fillLight = new THREE.DirectionalLight(0x4466ff, 0.8);
    fillLight.position.set(-5, -3, 2);
    scene.add(fillLight);

    // --- Dumbbell geometry (bar + two plates each side) ---
    const metalMat = new THREE.MeshStandardMaterial({
        color: 0x888888,
        metalness: 0.9,
        roughness: 0.2,
    });
    const plateMat = new THREE.MeshStandardMaterial({
        color: 0x1a1a1a,
        metalness: 0.8,
        roughness: 0.3,
    });

    const dumbbell = new THREE.Group();

    // Bar
    const bar = new THREE.Mesh(new THREE.CylinderGeometry(0.06, 0.06, 3.2, 16), metalMat);
    bar.rotation.z = Math.PI / 2;
    dumbbell.add(bar);

    // Plates — left side
    [-1.2, -1.5].forEach(x => {
        const plate = new THREE.Mesh(new THREE.CylinderGeometry(0.55, 0.55, 0.18, 32), plateMat);
        plate.rotation.z = Math.PI / 2;
        plate.position.x = x;
        dumbbell.add(plate);
    });

    // Plates — right side
    [1.2, 1.5].forEach(x => {
        const plate = new THREE.Mesh(new THREE.CylinderGeometry(0.55, 0.55, 0.18, 32), plateMat);
        plate.rotation.z = Math.PI / 2;
        plate.position.x = x;
        dumbbell.add(plate);
    });

    // Collars (end caps)
    [-1.55, 1.55].forEach(x => {
        const collar = new THREE.Mesh(new THREE.CylinderGeometry(0.12, 0.12, 0.12, 16), metalMat);
        collar.rotation.z = Math.PI / 2;
        collar.position.x = x;
        dumbbell.add(collar);
    });

    dumbbell.position.set(2.5, 0, 0);
    dumbbell.rotation.x = 0.3;
    scene.add(dumbbell);

    // --- Floating particles ---
    const particleCount = 120;
    const positions = new Float32Array(particleCount * 3);
    for (let i = 0; i < particleCount * 3; i++) {
        positions[i] = (Math.random() - 0.5) * 14;
    }
    const particleGeo = new THREE.BufferGeometry();
    particleGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    const particleMat = new THREE.PointsMaterial({
        color: 0xe8ff47,
        size: 0.04,
        transparent: true,
        opacity: 0.5,
    });
    scene.add(new THREE.Points(particleGeo, particleMat));

    // --- Mouse tracking ---
    let mouseX = 0, mouseY = 0;
    window.addEventListener('mousemove', e => {
        mouseX = (e.clientX / window.innerWidth  - 0.5) * 2;
        mouseY = (e.clientY / window.innerHeight - 0.5) * 2;
    });

    // --- Resize handler ---
    window.addEventListener('resize', () => {
        camera.aspect = canvas.clientWidth / canvas.clientHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(canvas.clientWidth, canvas.clientHeight);
    });

    // --- Animation loop ---
    const clock = new THREE.Clock();
    function animate() {
        requestAnimationFrame(animate);
        const t = clock.getElapsedTime();

        // Slowly rotate dumbbell
        dumbbell.rotation.y = t * 0.4 + mouseX * 0.3;
        dumbbell.rotation.x = 0.3 + mouseY * 0.2;
        dumbbell.position.y = Math.sin(t * 0.5) * 0.15; // gentle float

        // Slowly drift particles
        particleGeo.attributes.position.array[0] += 0.0001;
        particleGeo.attributes.position.needsUpdate = true;

        renderer.render(scene, camera);
    }
    animate();
})();

// --- Banner mini-scene: rotating rings ---
(function initBannerScene() {
    const canvas = document.getElementById('banner-canvas');
    if (!canvas) return;

    const scene    = new THREE.Scene();
    const camera   = new THREE.PerspectiveCamera(60, canvas.clientWidth / canvas.clientHeight, 0.1, 100);
    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });

    renderer.setSize(canvas.clientWidth, canvas.clientHeight);
    renderer.setClearColor(0x000000, 0);
    camera.position.z = 4;

    const ringMat = new THREE.MeshStandardMaterial({ color: 0xe8ff47, wireframe: true });
    const rings = [];
    [1.2, 1.8, 2.4].forEach((r, i) => {
        const ring = new THREE.Mesh(new THREE.TorusGeometry(r, 0.015, 8, 80), ringMat);
        ring.rotation.x = i * 0.5;
        scene.add(ring);
        rings.push(ring);
    });

    scene.add(new THREE.AmbientLight(0xffffff, 1));

    window.addEventListener('resize', () => {
        camera.aspect = canvas.clientWidth / canvas.clientHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(canvas.clientWidth, canvas.clientHeight);
    });

    const clock = new THREE.Clock();
    (function animate() {
        requestAnimationFrame(animate);
        const t = clock.getElapsedTime();
        rings.forEach((r, i) => {
            r.rotation.x = t * (0.2 + i * 0.07);
            r.rotation.y = t * (0.15 + i * 0.05);
        });
        renderer.render(scene, camera);
    })();
})();
</script>
@endpush