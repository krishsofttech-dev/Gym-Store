<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — {{ config('app.name', 'GymStore') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Auth specific CSS --}}
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
    <div class="auth-card">

        {{-- Brand --}}
        <div class="auth-brand">
            <div class="auth-brand-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0c0a09" stroke-width="2.5">
                    <path d="M6 4v16M18 4v16M2 9h4M18 9h4M2 15h4M18 15h4"/>
                </svg>
            </div>
            <span class="auth-brand-name">GYMSTORE</span>
        </div>

        <h1 class="auth-heading">WELCOME <span>BACK</span></h1>
        <p class="auth-subheading">Sign in to your account to continue shopping</p>

        {{-- Status --}}
        @if (session('status'))
            <div class="auth-status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="auth-field">
                <label class="auth-label" for="email">Email Address</label>
                <input class="auth-input" type="email" id="email" name="email"
                       value="{{ old('email') }}"
                       placeholder="you@example.com"
                       required autofocus autocomplete="username">
                @error('email')
                    <p class="auth-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-field">
                <label class="auth-label" for="password">Password</label>
                <input class="auth-input" type="password" id="password" name="password"
                       placeholder="••••••••"
                       required autocomplete="current-password">
                @error('password')
                    <p class="auth-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-extras">
                <label class="auth-remember">
                    <input type="checkbox" name="remember">
                    <span>Remember me</span>
                </label>
                @if (Route::has('password.request'))
                    <a class="auth-forgot" href="{{ route('password.request') }}">Forgot password?</a>
                @endif
            </div>

            <button type="submit" class="auth-btn">SIGN IN</button>
        </form>

        <div class="auth-divider"><span>New to GymStore?</span></div>

        <div class="auth-footer">
            Don't have an account?
            <a href="{{ route('register') }}">Create one free </a>
        </div>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
(function () {
    const canvas   = document.getElementById('three-canvas');
    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: false });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setClearColor(0x0c0a09, 1);
    renderer.shadowMap.enabled = true;

    const scene  = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(55, window.innerWidth / window.innerHeight, 0.1, 100);
    camera.position.set(0, 0, 06);

    // ── Build a dumbbell ──────────────────────────────────────
    function makeDumbbell(accentColor) {
        const group = new THREE.Group();

        const barMat = new THREE.MeshStandardMaterial({
            color: 0x292524, metalness: 0.9, roughness: 0.3,
        });
        const plateMat = new THREE.MeshStandardMaterial({
            color: accentColor ? 0xfbbf24 : 0x44403c,
            metalness: 0.8, roughness: accentColor ? 0.2 : 0.5,
        });

        // Centre bar
        const bar = new THREE.Mesh(
            new THREE.CylinderGeometry(0.07, 0.07, 1.6, 16),
            barMat
        );
        group.add(bar);

        // Knurling rings
        for (let i = -0.3; i <= 0.3; i += 0.15) {
            const ring = new THREE.Mesh(
                new THREE.TorusGeometry(0.075, 0.012, 6, 24),
                barMat
            );
            ring.rotation.x = Math.PI / 2;
            ring.position.y = i;
            group.add(ring);
        }

        // Plates (left and right)
        [-0.72, 0.72].forEach(yPos => {
            // Outer large plate
            const p1 = new THREE.Mesh(
                new THREE.CylinderGeometry(0.38, 0.38, 0.14, 32),
                plateMat
            );
            p1.position.y = yPos;
            group.add(p1);

            // Inner smaller plate
            const p2 = new THREE.Mesh(
                new THREE.CylinderGeometry(0.28, 0.28, 0.1, 32),
                plateMat
            );
            p2.position.y = yPos + (yPos > 0 ? -0.12 : 0.12);
            group.add(p2);

            // Collar ring
            const collar = new THREE.Mesh(
                new THREE.CylinderGeometry(0.095, 0.095, 0.08, 16),
                barMat
            );
            collar.position.y = yPos + (yPos > 0 ? -0.24 : 0.24);
            group.add(collar);
        });

        return group;
    }

    // ── Scatter dumbbells in scene ────────────────────────────
    const dumbbells = [];

    const configs = [
        // [x,    y,    z,    rx,   ry,   rz,   accent, spdX, spdY, spdZ, floatAmp, floatPhase]
        [  3.5,   1.5, -2,   0.3,  0.8,  0.2,  true,   0.003, 0.004, 0.002, 0.008, 0.0   ],
        [ -3.8,  -1.2, -1,   0.6,  0.2,  0.5,  false,  0.002, 0.003, 0.004, 0.006, 1.1   ],
        [  0.5,   3.0, -3,   0.1,  0.5,  0.8,  false,  0.004, 0.002, 0.003, 0.007, 2.2   ],
        [ -1.0,  -3.2, -2,   0.4,  0.9,  0.1,  true,   0.003, 0.005, 0.002, 0.009, 0.7   ],
        [  5.0,  -0.5, -4,   0.7,  0.3,  0.6,  false,  0.002, 0.003, 0.003, 0.005, 1.8   ],
        [ -4.5,   2.8, -3,   0.2,  0.6,  0.4,  false,  0.005, 0.002, 0.004, 0.007, 3.1   ],
        [  2.0,  -2.5, -1,   0.5,  0.1,  0.7,  true,   0.004, 0.004, 0.003, 0.006, 2.5   ],
        [ -2.5,   0.8, -2,   0.3,  0.7,  0.2,  false,  0.003, 0.003, 0.005, 0.008, 0.3   ],
    ];

    configs.forEach(([x, y, z, rx, ry, rz, accent, spdX, spdY, spdZ, fAmp, fPhase]) => {
        const db = makeDumbbell(accent);
        db.position.set(x, y, z);
        db.rotation.set(rx, ry, rz);
        scene.add(db);
        dumbbells.push({ mesh: db, spdX, spdY, spdZ, fAmp, fPhase, baseY: y });
    });

    // ── Floating weight plates (rings) ────────────────────────
    const rings = [];
    for (let i = 0; i < 10; i++) {
        const isAccent = i % 3 === 0;
        const ring = new THREE.Mesh(
            new THREE.TorusGeometry(0.22 + Math.random() * 0.25, 0.035, 8, 32),
            new THREE.MeshStandardMaterial({
                color: isAccent ? 0xfbbf24 : 0x292524,
                metalness: 0.85,
                roughness: isAccent ? 0.2 : 0.6,
                wireframe: !isAccent && i % 3 === 2,
            })
        );
        ring.position.set(
            (Math.random() - 0.5) * 14,
            (Math.random() - 0.5) * 10,
            -3 + (Math.random() - 0.5) * 3
        );
        ring.rotation.set(
            Math.random() * Math.PI,
            Math.random() * Math.PI,
            Math.random() * Math.PI
        );
        scene.add(ring);
        rings.push({
            mesh: ring,
            rx: (Math.random() - 0.5) * 0.008,
            ry: (Math.random() - 0.5) * 0.012,
            fAmp: (Math.random() - 0.5) * 0.004,
            fPhase: Math.random() * Math.PI * 2,
        });
    }

    // ── Lighting ──────────────────────────────────────────────
    scene.add(new THREE.AmbientLight(0xffffff, 0.35));

    const keyLight = new THREE.PointLight(0xfbbf24, 3.5, 25);
    keyLight.position.set(6, 6, 5);
    scene.add(keyLight);

    const fillLight = new THREE.PointLight(0x60a5fa, 1.2, 18);
    fillLight.position.set(-6, -3, 3);
    scene.add(fillLight);

    const rimLight = new THREE.PointLight(0xffffff, 0.8, 20);
    rimLight.position.set(0, -6, -2);
    scene.add(rimLight);

    // ── Mouse parallax ────────────────────────────────────────
    let targetX = 0, targetY = 0, curX = 0, curY = 0;
    document.addEventListener('mousemove', e => {
        targetX = (e.clientX / window.innerWidth  - 0.5) * 0.6;
        targetY = (e.clientY / window.innerHeight - 0.5) * 0.4;
    });

    // ── Animation loop ────────────────────────────────────────
    const clock = new THREE.Clock();

    function animate() {
        requestAnimationFrame(animate);
        const t = clock.getElapsedTime();

        // Smooth mouse
        curX += (targetX - curX) * 0.04;
        curY += (targetY - curY) * 0.04;

        // Rotate all dumbbells
        dumbbells.forEach(({ mesh, spdX, spdY, spdZ, fAmp, fPhase, baseY }) => {
            mesh.rotation.x += spdX;
            mesh.rotation.y += spdY;
            mesh.rotation.z += spdZ;
            mesh.position.y = baseY + Math.sin(t * 0.6 + fPhase) * fAmp * 18;
        });

        // Spin rings
        rings.forEach(({ mesh, rx, ry, fAmp, fPhase }) => {
            mesh.rotation.x += rx;
            mesh.rotation.y += ry;
            mesh.position.y += Math.sin(t + fPhase) * fAmp;
        });

        // Pulse key light
        keyLight.intensity = 3.0 + Math.sin(t * 0.7) * 0.5;

        // Camera parallax
        camera.position.x += (curX * 1.5 - camera.position.x) * 0.05;
        camera.position.y += (-curY * 1.0 - camera.position.y) * 0.05;
        camera.lookAt(0, 0, 0);

        renderer.render(scene, camera);
    }

    animate();

    window.addEventListener('resize', () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });
})();
</script>

</body>
</html>