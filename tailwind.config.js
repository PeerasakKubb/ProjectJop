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
            fontFamily: {
                sans: ['Sarabun', '"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#eef4fa',
                    100: '#d5e4f2',
                    200: '#abc9e4',
                    300: '#7aa6cc',
                    400: '#4a82b3',
                    500: '#1e5a93',
                    600: '#0b3a6e',
                    700: '#092f59',
                    800: '#072445',
                    900: '#051830',
                },
                gold: {
                    DEFAULT: '#c4a35a',
                    dark: '#9a7b32',
                    light: '#e6d5a8',
                },
                neon: {
                    cyan: '#c4a35a',
                    pink: '#c4a35a',
                    violet: '#7aa6cc',
                },
            },
            boxShadow: {
                soft: '0 8px 32px rgba(0, 0, 0, 0.35)',
                glow: 'none',
                'glow-cyan': 'none',
                'glow-pink': 'none',
                card: '0 0 0 1px rgba(255,255,255,0.06), 0 12px 32px rgba(0,0,0,0.35)',
            },
            animation: {
                'fade-in': 'fadeIn 0.4s ease-out forwards',
                'slide-up': 'slideUp 0.4s ease-out forwards',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { opacity: '0', transform: 'translateY(12px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
            backgroundImage: {
                'gradient-brand': 'linear-gradient(180deg, #0b3a6e 0%, #072445 100%)',
                'gradient-hot': 'linear-gradient(180deg, #c4a35a 0%, #9a7b32 100%)',
                'gradient-card-brand': 'linear-gradient(180deg, rgba(11,58,110,0.35) 0%, rgba(7,36,69,0.15) 100%)',
                'gradient-card-green': 'linear-gradient(180deg, rgba(16,185,129,0.2) 0%, rgba(6,40,30,0.1) 100%)',
                'gradient-card-amber': 'linear-gradient(180deg, rgba(196,163,90,0.25) 0%, rgba(40,30,10,0.1) 100%)',
                'gradient-card-blue': 'linear-gradient(180deg, rgba(30,90,147,0.3) 0%, rgba(7,36,69,0.1) 100%)',
            },
        },
    },

    plugins: [forms],
};
