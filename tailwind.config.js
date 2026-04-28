/** @type {import('tailwindcss').Config} */
export default {
    /*
     * LESSON: content tells Tailwind WHERE to look for class names.
     * It scans these files and removes any CSS class that isn't found —
     * this is called "purging" and keeps the final CSS file tiny.
     */
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],

    theme: {
        extend: {
            /*
             * Custom font families matching our Google Fonts imports
             */
            fontFamily: {
                display: ['"Bebas Neue"', 'sans-serif'],
                body:    ['"DM Sans"', 'sans-serif'],
                mono:    ['"DM Mono"', 'monospace'],
            },

            /*
             * Custom colours — extend stone palette with our accent
             */
            colors: {
                accent: {
                    DEFAULT: '#e8ff47',
                    dark:    '#c9e000',
                    light:   '#f2ff80',
                },
            },

            /*
             * Custom animations
             */
            animation: {
                'float':       'float 3s ease-in-out infinite',
                'pulse-slow':  'pulse 3s ease-in-out infinite',
            },
            keyframes: {
                float: {
                    '0%, 100%': { transform: 'translateY(0px)' },
                    '50%':      { transform: 'translateY(-10px)' },
                },
            },
        },
    },

    plugins: [],
}



// import defaultTheme from 'tailwindcss/defaultTheme';
// import forms from '@tailwindcss/forms';

// /** @type {import('tailwindcss').Config} */
// export default {
//     content: [
//         './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
//         './storage/framework/views/*.php',
//         './resources/views/**/*.blade.php',
//     ],

//     theme: {
//         extend: {
//             fontFamily: {
//                 sans: ['Figtree', ...defaultTheme.fontFamily.sans],
//             },
//         },
//     },

//     plugins: [forms],
// };
