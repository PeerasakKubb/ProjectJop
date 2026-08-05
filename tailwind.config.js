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
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#f5f3ff',
                    100: '#ede9fe',
                    200: '#ddd6fe',
                    300: '#c4b5fd',
                    400: '#a78bfa',
                    500: '#8b5cf6',
                    600: '#7c3aed',
                    700: '#6d28d9',
                    800: '#5b21b6',
                    900: '#4c1d95',
                },
                neon: {
                    cyan: '#22d3ee',
                    pink: '#f472b6',
                    violet: '#a78bfa',
                },
            },
            boxShadow: {
                soft: '0 8px 32px rgba(0, 0, 0, 0.4)',
                glow: '0 0 40px -8px rgba(139, 92, 246, 0.6)',
                'glow-cyan': '0 0 40px -8px rgba(34, 211, 238, 0.5)',
                'glow-pink': '0 0 40px -8px rgba(244, 114, 182, 0.5)',
                card: '0 0 0 1px rgba(255,255,255,0.06), 0 20px 50px rgba(0,0,0,0.45)',
            },
            animation: {
                'fade-in': 'fadeIn 0.6s ease-out forwards',
                'slide-up': 'slideUp 0.6s ease-out forwards',
                'float': 'float 6s ease-in-out infinite',
                'pulse-glow': 'pulseGlow 3s ease-in-out infinite',
                'gradient-shift': 'gradientShift 8s ease infinite',
                'orb-1': 'orbFloat1 20s ease-in-out infinite',
                'orb-2': 'orbFloat2 25s ease-in-out infinite',
                'orb-3': 'orbFloat3 18s ease-in-out infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { opacity: '0', transform: 'translateY(20px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                float: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-10px)' },
                },
                pulseGlow: {
                    '0%, 100%': { opacity: '0.5' },
                    '50%': { opacity: '1' },
                },
                gradientShift: {
                    '0%, 100%': { backgroundPosition: '0% 50%' },
                    '50%': { backgroundPosition: '100% 50%' },
                },
                orbFloat1: {
                    '0%, 100%': { transform: 'translate(0, 0) scale(1)' },
                    '50%': { transform: 'translate(40px, -30px) scale(1.1)' },
                },
                orbFloat2: {
                    '0%, 100%': { transform: 'translate(0, 0) scale(1)' },
                    '50%': { transform: 'translate(-50px, 40px) scale(1.15)' },
                },
                orbFloat3: {
                    '0%, 100%': { transform: 'translate(0, 0)' },
                    '50%': { transform: 'translate(30px, 50px)' },
                },
            },
            backgroundImage: {
                'gradient-brand': 'linear-gradient(135deg, #7c3aed 0%, #6366f1 40%, #22d3ee 100%)',
                'gradient-hot': 'linear-gradient(135deg, #f472b6 0%, #8b5cf6 50%, #22d3ee 100%)',
                'gradient-card-brand': 'linear-gradient(135deg, rgba(124,58,237,0.25) 0%, rgba(99,102,241,0.1) 100%)',
                'gradient-card-green': 'linear-gradient(135deg, rgba(16,185,129,0.25) 0%, rgba(6,182,212,0.1) 100%)',
                'gradient-card-amber': 'linear-gradient(135deg, rgba(245,158,11,0.25) 0%, rgba(249,115,22,0.1) 100%)',
                'gradient-card-blue': 'linear-gradient(135deg, rgba(34,211,238,0.25) 0%, rgba(59,130,246,0.1) 100%)',
            },
        },
    },

    plugins: [forms],
};
