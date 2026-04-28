<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register — {{ config('app.name', 'GymStore') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    {{-- FIX: Register Alpine component BEFORE Alpine boots via Vite --}}
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('registerForm', () => ({
            password: '',
            strengthPct: 0,
            strengthLabel: '',
            strengthColor: '#ef4444',
            checkStrength() {
                const p = this.password;
                let score = 0;
                if (p.length >= 8)          score++;
                if (p.length >= 12)         score++;
                if (/[A-Z]/.test(p))        score++;
                if (/[0-9]/.test(p))        score++;
                if (/[^A-Za-z0-9]/.test(p)) score++;

                const lvl = [
                    { pct: 15,  label: 'Too weak',  color: '#ef4444' },
                    { pct: 35,  label: 'Weak',       color: '#f97316' },
                    { pct: 58,  label: 'Fair',        color: '#eab308' },
                    { pct: 80,  label: 'Good',        color: '#84cc16' },
                    { pct: 100, label: 'Strong 💪',   color: '#22c55e' },
                ][Math.min(score, 4)];

                this.strengthPct   = lvl.pct;
                this.strengthLabel = lvl.label;
                this.strengthColor = lvl.color;
            }
        }));
    });
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

{{-- 3D Dumbbell Canvas --}}
<canvas id="three-canvas"></canvas>
<div class="grain-overlay"></div>
<div class="vignette"></div>

{{-- Back to home --}}
<a href="{{ route('home') }}" class="auth-back">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M19 12H5M12 5l-7 7 7 7"/>
    </svg>
    Back to store
</a>

<div class="auth-wrapper">
    <div class="auth-card" x-data="registerForm()">

        {{-- Brand --}}
        <div class="auth-brand">
            <div class="auth-brand-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0c0a09" stroke-width="2.5">
                    <path d="M6 4v16M18 4v16M2 9h4M18 9h4M2 15h4M18 15h4"/>
                </svg>
            </div>
            <span class="auth-brand-name">GYMSTORE</span>
        </div>

        <h1 class="auth-heading">JOIN THE <span>GRIND</span></h1>
        <p class="auth-subheading">Create your free account and start training today</p>

        {{-- Perks --}}
        <div class="auth-perks">
            <div class="auth-perk">
                <span class="auth-perk-icon">⚡</span>
                <span class="auth-perk-label">Fast Checkout</span>
            </div>
            <div class="auth-perk">
                <span class="auth-perk-icon">📦</span>
                <span class="auth-perk-label">Order Tracking</span>
            </div>
            <div class="auth-perk">
                <span class="auth-perk-icon">🏷️</span>
                <span class="auth-perk-label">Member Deals</span>
            </div>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            {{-- Name --}}
            <div class="auth-field">
                <label class="auth-label" for="name">Full Name</label>
                <input class="auth-input" type="text" id="name" name="name"
                       value="{{ old('name') }}"
                       placeholder="John Smith"
                       required autofocus autocomplete="name">
                @error('name')
                    <p class="auth-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div class="auth-field">
                <label class="auth-label" for="email">Email Address</label>
                <input class="auth-input" type="email" id="email" name="email"
                       value="{{ old('email') }}"
                       placeholder="you@example.com"
                       required autocomplete="username">
                @error('email')
                    <p class="auth-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password with strength meter --}}
            <div class="auth-field">
                <label class="auth-label" for="password">Password</label>
                <input class="auth-input" type="password" id="password" name="password"
                       placeholder="Min. 8 characters"
                       required autocomplete="new-password"
                       x-model="password"
                       @input="checkStrength()">
                @error('password')
                    <p class="auth-error">{{ $message }}</p>
                @enderror
                <div class="strength-bar-track" x-show="password.length > 0" x-transition>
                    <div class="strength-bar-fill"
                         :style="{ width: strengthPct + '%', background: strengthColor }">
                    </div>
                </div>
                <p class="strength-label"
                   x-show="password.length > 0"
                   x-transition
                   x-text="strengthLabel"
                   :style="{ color: strengthColor }">
                </p>
            </div>

            {{-- Confirm Password --}}
            <div class="auth-field">
                <label class="auth-label" for="password_confirmation">Confirm Password</label>
                <input class="auth-input" type="password" id="password_confirmation"
                       name="password_confirmation"
                       placeholder="Repeat your password"
                       required autocomplete="new-password">
                @error('password_confirmation')
                    <p class="auth-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="auth-btn">CREATE ACCOUNT</button>
        </form>

        <p class="auth-terms">
            By registering you agree to our
            <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
        </p>

        <div class="auth-divider"><span>Already have an account?</span></div>

        <div class="auth-footer">
            <a href="{{ route('login') }}">← Sign in instead</a>
        </div>

    </div>
</div>

{{-- Three.js loaded AFTER page content --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
(function () {
    const canvas   = document.getElementById('three-canvas');
    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setClearColor(0x0c0a09, 1);

    const scene  = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(55, window.innerWidth / window.innerHeight, 0.1, 100);
    camera.position.set(0, 0, 06);

    // ── Build a dumbbell ──────────────────────────────────────
    function makeDumbbell(accent) {
        const group = new THREE.Group();

        const barMat = new THREE.MeshStandardMaterial({
            color: 0x292524, metalness: 0.9, roughness: 0.3,
        });
        const plateMat = new THREE.MeshStandardMaterial({
            color: accent ? 0xfbbf24 : 0x3d3835,
            metalness: 0.8,
            roughness: accent ? 0.15 : 0.5,
        });

        // Centre bar
        group.add(new THREE.Mesh(
            new THREE.CylinderGeometry(0.07, 0.07, 1.6, 16), barMat
        ));

        // Knurl rings
        for (let y = -0.3; y <= 0.3; y += 0.15) {
            const r = new THREE.Mesh(
                new THREE.TorusGeometry(0.075, 0.012, 6, 24), barMat
            );
            r.rotation.x = Math.PI / 2;
            r.position.y = y;
            group.add(r);
        }

        // Plates each side
        [-0.72, 0.72].forEach(yp => {
            const sign = yp > 0 ? -1 : 1;

            const lg = new THREE.Mesh(
                new THREE.CylinderGeometry(0.38, 0.38, 0.14, 32), plateMat
            );
            lg.position.y = yp;
            group.add(lg);

            const sm = new THREE.Mesh(
                new THREE.CylinderGeometry(0.28, 0.28, 0.10, 32), plateMat
            );
            sm.position.y = yp + sign * 0.12;
            group.add(sm);

            const col = new THREE.Mesh(
                new THREE.CylinderGeometry(0.095, 0.095, 0.08, 16), barMat
            );
            col.position.y = yp + sign * 0.24;
            group.add(col);
        });

        return group;
    }

    // ── Scatter dumbbells ─────────────────────────────────────
    const dumbbells = [];
    [
        [ 3.5,  1.5, -2,  0.3, 0.8, 0.2, true,  0.003, 0.004, 0.002, 0.007, 0.0 ],
        [-3.8, -1.2, -1,  0.6, 0.2, 0.5, false, 0.002, 0.003, 0.004, 0.005, 1.1 ],
        [ 0.5,  3.0, -3,  0.1, 0.5, 0.8, false, 0.004, 0.002, 0.003, 0.006, 2.2 ],
        [-1.0, -3.2, -2,  0.4, 0.9, 0.1, true,  0.003, 0.005, 0.002, 0.008, 0.7 ],
        [ 5.0, -0.5, -4,  0.7, 0.3, 0.6, false, 0.002, 0.003, 0.003, 0.005, 1.8 ],
        [-4.5,  2.8, -3,  0.2, 0.6, 0.4, false, 0.005, 0.002, 0.004, 0.006, 3.1 ],
        [ 2.0, -2.5, -1,  0.5, 0.1, 0.7, true,  0.004, 0.004, 0.003, 0.007, 2.5 ],
        [-2.5,  0.8, -2,  0.3, 0.7, 0.2, false, 0.003, 0.003, 0.005, 0.006, 0.3 ],
    ].forEach(([x, y, z, rx, ry, rz, acc, sx, sy, sz, fa, fp]) => {
        const db = makeDumbbell(acc);
        db.position.set(x, y, z);
        db.rotation.set(rx, ry, rz);
        scene.add(db);
        dumbbells.push({ mesh: db, sx, sy, sz, fa, fp, baseY: y });
    });

    // ── Floating rings ────────────────────────────────────────
    const rings = [];
    for (let i = 0; i < 10; i++) {
        const acc = i % 3 === 0;
        const r = new THREE.Mesh(
            new THREE.TorusGeometry(0.2 + Math.random() * 0.25, 0.033, 8, 32),
            new THREE.MeshStandardMaterial({
                color: acc ? 0xfbbf24 : 0x292524,
                metalness: 0.85,
                roughness: acc ? 0.2 : 0.6,
                wireframe: !acc && i % 3 === 2,
            })
        );
        r.position.set(
            (Math.random() - 0.5) * 14,
            (Math.random() - 0.5) * 10,
            -3 + (Math.random() - 0.5) * 3
        );
        r.rotation.set(
            Math.random() * Math.PI,
            Math.random() * Math.PI,
            Math.random() * Math.PI
        );
        scene.add(r);
        rings.push({
            mesh: r,
            rx: (Math.random() - 0.5) * 0.009,
            ry: (Math.random() - 0.5) * 0.013,
            fa: (Math.random() - 0.5) * 0.004,
            fp: Math.random() * Math.PI * 2,
        });
    }

    // ── Lighting ──────────────────────────────────────────────
    scene.add(new THREE.AmbientLight(0xffffff, 0.35));

    const key = new THREE.PointLight(0xfbbf24, 3.5, 25);
    key.position.set(-6, 6, 5);
    scene.add(key);

    const fill = new THREE.PointLight(0x34d399, 1.2, 18);
    fill.position.set(6, -3, 3);
    scene.add(fill);

    const rim = new THREE.PointLight(0xffffff, 0.7, 20);
    rim.position.set(0, -6, -2);
    scene.add(rim);

    // ── Mouse parallax ────────────────────────────────────────
    let tx = 0, ty = 0, cx = 0, cy = 0;
    document.addEventListener('mousemove', e => {
        tx = (e.clientX / window.innerWidth  - 0.5) * 0.6;
        ty = (e.clientY / window.innerHeight - 0.5) * 0.4;
    });

    // ── Animation loop ────────────────────────────────────────
    const clock = new THREE.Clock();

    (function animate() {
        requestAnimationFrame(animate);
        const t = clock.getElapsedTime();

        cx += (tx - cx) * 0.04;
        cy += (ty - cy) * 0.04;

        dumbbells.forEach(({ mesh, sx, sy, sz, fa, fp, baseY }) => {
            mesh.rotation.x += sx;
            mesh.rotation.y += sy;
            mesh.rotation.z += sz;
            mesh.position.y  = baseY + Math.sin(t * 0.6 + fp) * fa * 18;
        });

        rings.forEach(({ mesh, rx, ry, fa, fp }) => {
            mesh.rotation.x += rx;
            mesh.rotation.y += ry;
            mesh.position.y += Math.sin(t + fp) * fa;
        });

        key.intensity = 3.0 + Math.sin(t * 0.8) * 0.6;

        camera.position.x += (cx * 1.5 - camera.position.x) * 0.05;
        camera.position.y += (-cy * 1.0 - camera.position.y) * 0.05;
        camera.lookAt(0, 0, 0);

        renderer.render(scene, camera);
    })();

    window.addEventListener('resize', () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });
})();
</script>

</body>
</html>