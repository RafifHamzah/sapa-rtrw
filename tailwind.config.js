import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                display: ['Poppins', '"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Brand SAPA: emerald/jade dengan ujung gelap hijau hutan.
                brand: {
                    50: '#ecfdf5',
                    100: '#d1fae5', // Mint
                    200: '#a7f3d0',
                    300: '#6ee7b7',
                    400: '#34d399',
                    500: '#10b981', // Emerald 500
                    600: '#059669',
                    700: '#047857',
                    800: '#165c4a', // Forest
                    900: '#0f3d33', // Deep forest
                    950: '#052e26',
                },
                // Aksen jade cerah untuk sorotan/CTA.
                accent: {
                    50: '#f0fdf4',
                    100: '#dcfce7',
                    200: '#bbf7d0',
                    300: '#86efac',
                    400: '#4ade80',
                    500: '#22c55e',
                    600: '#16a34a', // Jade 600
                    700: '#15803d',
                },
            },
            boxShadow: {
                soft: '0 2px 8px -2px rgb(16 185 129 / 0.10), 0 4px 20px -4px rgb(16 185 129 / 0.12)',
                card: '0 1px 3px rgb(15 23 42 / 0.06), 0 8px 24px -12px rgb(15 23 42 / 0.12)',
            },
            keyframes: {
                'fade-in-up': {
                    '0%': { opacity: '0', transform: 'translateY(12px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
            animation: {
                'fade-in-up': 'fade-in-up 0.5s ease-out both',
            },
        },
    },

    plugins: [forms],
};
