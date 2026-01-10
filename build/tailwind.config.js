/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "../**/*.php",
        "../assets/react/**/*.{js,jsx,ts,tsx}",
    ],
    theme: {
        extend: {
            colors: {
                // Classic Statesman Color Palette
                navy: {
                    DEFAULT: '#14213d',
                    50: '#e6eaf1',
                    100: '#c4cee0',
                    200: '#9fb0cd',
                    300: '#7a92ba',
                    400: '#5e7aac',
                    500: '#43629d',
                    600: '#3c5a95',
                    700: '#32508b',
                    800: '#294681',
                    900: '#14213d',
                },
                red: {
                    DEFAULT: '#C4232C',
                    50: '#fceced',
                    100: '#f7cfd1',
                    200: '#f1a8ac',
                    300: '#eb8187',
                    400: '#e76469',
                    500: '#e3474c',
                    600: '#d93f44',
                    700: '#ca373b',
                    800: '#bc2f33',
                    900: '#C4232C',
                },
                gold: {
                    DEFAULT: '#BF9B30',
                    50: '#faf7eb',
                    100: '#f3ebce',
                    200: '#ebdeae',
                    300: '#e3d18e',
                    400: '#ddc876',
                    500: '#d7bf5e',
                    600: '#cfb756',
                    700: '#c5ad4c',
                    800: '#BF9B30',
                    900: '#a88628',
                },
                neutral: {
                    50: '#f8f9fa',
                    100: '#f1f3f5',
                    200: '#e9ecef',
                    300: '#dee2e6',
                    400: '#ced4da',
                    500: '#adb5bd',
                    600: '#6c757d',
                    700: '#495057',
                    800: '#343a40',
                    900: '#212529',
                }
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                serif: ['Playfair Display', 'Georgia', 'Times New Roman', 'serif'],
                display: ['Playfair Display', 'Georgia', 'Times New Roman', 'serif'],
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
