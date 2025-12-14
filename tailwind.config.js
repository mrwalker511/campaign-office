/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./*.php",
        "./inc/**/*.php",
        "./includes/**/*.php",
        "./templates/**/*.php",
        "./parts/**/*.php",
        "./blocks/**/*.php",
        "./assets/react/**/*.{js,jsx,ts,tsx}",
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#f0fdfa',
                    100: '#ccfbf1',
                    200: '#99f6e4',
                    300: '#5eead4',
                    400: '#2dd4bf',
                    500: '#14b8a6',
                    600: '#0d9488',
                    700: '#0f766e',
                    800: '#115e59',
                    900: '#134e4a',
                    950: '#042f2e',
                },
                accent: {
                    50: '#fff1f2',
                    100: '#ffe4e6',
                    500: '#f43f5e',
                    600: '#e11d48',
                    700: '#be123c',
                    900: '#881337',
                },
                neutral: {
                    50: '#f8fafc',
                    100: '#f1f5f9',
                    200: '#e2e8f0',
                    300: '#cbd5e1',
                    400: '#94a3b8',
                    500: '#64748b',
                    600: '#475569',
                    700: '#334155',
                    800: '#1e293b',
                    900: '#0f172a',
                    950: '#020617',
                }
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                serif: ['Merriweather', 'Georgia', 'serif'],
                display: ['Outfit', 'sans-serif'],
            },
            spacing: {
                // Small gaps - useful for card spacing
                '18': '4.5rem',   // 72px

                // Medium gaps - for section spacing
                '88': '22rem',    // 352px
                '100': '25rem',   // 400px

                // Large gaps - for hero sections and containers
                '112': '28rem',   // 448px
                '128': '32rem',   // 512px
                '144': '36rem',   // 576px - max content width
            },
            container: {
                center: true,
                padding: {
                    DEFAULT: '1rem',
                    sm: '2rem',
                    lg: '4rem',
                    xl: '5rem',
                    '2xl': '6rem',
                },
            },
        },
    },
    plugins: [],
}
