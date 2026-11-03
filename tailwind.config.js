import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                ink: '#10262b',
                soft: '#46606a',
                teal: { DEFAULT: '#0c8c84', deep: '#0a6b65' },
                mint: '#e3f3f0',
                mist: '#f3f7f7',
                line: '#d3e0e0',
                amber: '#f4b63f',
                paper: '#fdfcf8',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                display: ['"Bricolage Grotesque"', '"Hind Siliguri"', 'sans-serif'],
            },
        },
    },

    plugins: [forms],
};
