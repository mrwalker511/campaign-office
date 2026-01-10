import { defineConfig } from 'vite';
import { resolve } from 'path';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  build: {
    outDir: 'assets/dist',
    rollupOptions: {
      input: {
        main: resolve(__dirname, '..', 'assets/js/main.js'),
        'classic-statesman': resolve(__dirname, '..', 'assets/react/index.jsx'),
        tailwind: resolve(__dirname, '..', 'assets/css/app.css'),
      },
      output: {
        entryFileNames: 'js/[name].js',
        chunkFileNames: 'js/[name]-[hash].js',
        assetFileNames: '[ext]/[name].[ext]',
      },
    },
  },
});
