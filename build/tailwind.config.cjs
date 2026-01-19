module.exports = {
  content: [
    './functions.php',
    './index.php',
    './templates/**/*.php',
    './includes/**/*.php',
    './assets/react/**/*.{js,jsx,ts,tsx}',
    './blocks/**/*.js',
    './blocks/**/*.jsx',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#14213d',
          50: '#f0f4f8',
          100: '#d9e2ec',
          200: '#bcccdc',
          300: '#9fb3c8',
          400: '#829ab1',
          500: '#627d98',
          600: '#486581',
          700: '#334e68',
          800: '#243b53',
          900: '#14213d',
        },
        accent: {
          DEFAULT: '#ff8800',
          50: '#fff5eb',
          100: '#ffe8d1',
          200: '#ffd0a3',
          300: '#ffb875',
          400: '#ffa047',
          500: '#ff8800',
          600: '#e67600',
          700: '#b35c00',
          800: '#804200',
          900: '#4d2800',
        },
      },
      fontFamily: {
        display: ['Playfair Display', 'serif'],
        body: ['Inter', 'sans-serif'],
        mono: ['System Mono', 'monospace'],
      },
    },
  },
  plugins: [],
};
