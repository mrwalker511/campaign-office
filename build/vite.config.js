import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig({
  plugins: [react()],
  build: {
    outDir: 'assets/dist',
    rollupOptions: {
      input: {
        blocks: resolve(__dirname, '..', 'assets/react/blocks/index.jsx'),
        crm: resolve(__dirname, '..', 'assets/react/crm/index.jsx'),
        main: resolve(__dirname, '..', 'assets/js/main.js'),
        tailwind: resolve(__dirname, '..', 'assets/css/app.css'),
      },
      output: {
        entryFileNames: 'js/[name].js',
        chunkFileNames: 'js/[name]-[hash].js',
        assetFileNames: 'css/[name].[ext]',
      },
    },
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, '..', 'assets/react'),
    },
  },
});
