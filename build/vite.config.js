import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  build: {
    outDir: 'assets/dist',
    rollupOptions: {
      input: {
        main: resolve(__dirname, '..', 'assets/js/main.js'),
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
