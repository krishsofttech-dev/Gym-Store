<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password — {{ config('app.name', 'GymStore') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

<canvas id="three-canvas"></canvas>
<div class="grain-overlay"></div>
<div class="vignette"></div>

<a href="{{ route('login') }}" class="auth-back">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M19 12H5M12 5l-7 7 7 7"/>
    </svg>
    Back to login
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

        <h1 class="auth-heading">RESET <span>PASSWORD</span></h1>
        <p class="auth-subheading">Enter your email and we'll send you a reset link</p>

        {{-- Status --}}
        @if (session('status'))
            <div class="auth-status">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;margin-right:6px;vertical-align:middle">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
                {{ session('status') }}
            </div>
        @endif

        {{-- Lock icon --}}
        <div style="display:flex;justify-content:center;margin-bottom:1.8rem;animation:fadeUp 0.5s 0.2s both">
            <div style="width:64px;height:64px;background:rgba(251,191,36,0.08);border:1px solid rgba(251,191,36,0.2);border-radius:16px;display:flex;align-items:center;justify-content:center;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="1.8">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
        </div>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="auth-field">
                <label class="auth-label" for="email">Email Address</label>
                <input class="auth-input" type="email" id="email" name="email"
                       value="{{ old('email') }}"
                       placeholder="you@example.com"
                       required autofocus autocomplete="email">
                @error('email')
                    <p class="auth-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="auth-btn" style="margin-top:0.5rem">
                SEND RESET LINK
            </button>
        </form>

        <div class="auth-divider"><span>Remember your password?</span></div>

        <div class="auth-footer">
            <a href="{{ route('login') }}">← Back to sign in</a>
        </div>

    </div>
</div>

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

    function makeDumbbell(accent) {
        const group  = new THREE.Group();
        const barMat = new THREE.MeshStandardMaterial({ color: 0x292524, metalness: 0.9, roughness: 0.3 });
        const plateMat = new THREE.MeshStandardMaterial({
            color: accent ? 0xfbbf24 : 0x3d3835,
            metalness: 0.8, roughness: accent ? 0.15 : 0.5,
        });

        group.add(new THREE.Mesh(new THREE.CylinderGeometry(0.07, 0.07, 1.6, 16), barMat));

        for (let y = -0.3; y <= 0.3; y += 0.15) {
            const r = new THREE.Mesh(new THREE.TorusGeometry(0.075, 0.012, 6, 24), barMat);
            r.rotation.x = Math.PI / 2;
            r.position.y = y;
            group.add(r);
        }

        [-0.72, 0.72].forEach(yp => {
            const sign = yp > 0 ? -1 : 1;
            const lg = new THREE.Mesh(new THREE.CylinderGeometry(0.38, 0.38, 0.14, 32), plateMat);
            lg.position.y = yp;
            group.add(lg);
            const sm = new THREE.Mesh(new THREE.CylinderGeometry(0.28, 0.28, 0.10, 32), plateMat);
            sm.position.y = yp + sign * 0.12;
            group.add(sm);
            const col = new THREE.Mesh(new THREE.CylinderGeometry(0.095, 0.095, 0.08, 16), barMat);
            col.position.y = yp + sign * 0.24;
            group.add(col);
        });

        return group;
    }

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

    const rings = [];
    for (let i = 0; i < 10; i++) {
        const acc = i % 3 === 0;
        const r = new THREE.Mesh(
            new THREE.TorusGeometry(0.2 + Math.random() * 0.25, 0.033, 8, 32),
            new THREE.MeshStandardMaterial({
                color: acc ? 0xfbbf24 : 0x292524,
                metalness: 0.85, roughness: acc ? 0.2 : 0.6,
                wireframe: !acc && i % 3 === 2,
            })
        );
        r.position.set(
            (Math.random() - 0.5) * 14,
            (Math.random() - 0.5) * 10,
            -3 + (Math.random() - 0.5) * 3
        );
        r.rotation.set(Math.random() * Math.PI, Math.random() * Math.PI, Math.random() * Math.PI);
        scene.add(r);
        rings.push({
            mesh: r,
            rx: (Math.random() - 0.5) * 0.009,
            ry: (Math.random() - 0.5) * 0.013,
            fa: (Math.random() - 0.5) * 0.004,
            fp: Math.random() * Math.PI * 2,
        });
    }

    scene.add(new THREE.AmbientLight(0xffffff, 0.35));
    const key = new THREE.PointLight(0xfbbf24, 3.5, 25);
    key.position.set(6, 6, 5);
    scene.add(key);
    const fill = new THREE.PointLight(0x818cf8, 1.2, 18);
    fill.position.set(-6, -3, 3);
    scene.add(fill);
    const rim = new THREE.PointLight(0xffffff, 0.7, 20);
    rim.position.set(0, -6, -2);
    scene.add(rim);

    let tx = 0, ty = 0, cx = 0, cy = 0;
    document.addEventListener('mousemove', e => {
        tx = (e.clientX / window.innerWidth  - 0.5) * 0.6;
        ty = (e.clientY / window.innerHeight - 0.5) * 0.4;
    });

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