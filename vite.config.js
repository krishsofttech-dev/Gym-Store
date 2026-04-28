import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

/*
 * LESSON: Vite is the asset bundler that:
 *   1. Compiles resources/css/app.css (Tailwind  optimised CSS)
 *   2. Bundles resources/js/app.js (Alpine + our JS  single file)
 *   3. In dev mode: hot-reloads CSS/JS on every save (npm run dev)
 *   4. In prod: minifies + fingerprints files (npm run build)
 *
 * @vite(['resources/css/app.css', 'resources/js/app.js']) in app.blade.php
 * automatically resolves to the right URLs in dev and production.
 */
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true, // reload browser on Blade file changes
        }),
    ],
})

// import { defineConfig } from 'vite';
// import laravel from 'laravel-vite-plugin';

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/css/app.css', 'resources/js/app.js'],
//             refresh: true,
//         }),
//     ],
// });
