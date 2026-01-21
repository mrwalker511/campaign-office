import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  root: '.',
  resolve: {
    alias: {
      '@': path.resolve(__dirname, '../assets/react'),
      '@components': path.resolve(__dirname, '../assets/react/components'),
      '@blocks': path.resolve(__dirname, '../blocks'),
    },
  },
  build: {
    outDir: '../assets/dist',
    sourcemap: false,
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: true,
        drop_debugger: true,
      },
    },
    rollupOptions: {
      input: {
        main: path.resolve(__dirname, '../assets/js/main.js'),
        blocks: path.resolve(__dirname, '../assets/react/blocks/index.jsx'),
        crm: path.resolve(__dirname, '../assets/react/crm/index.jsx'),
      },
      output: {
        dir: '../assets/dist/js',
        entryFileNames: '[name].js',
        chunkFileNames: 'chunks/[name]-[hash].js',
        assetFileNames: (assetInfo) => {
          const info = assetInfo.name.split('.');
          const ext = info[info.length - 1];
          if (/\.(css)$/.test(assetInfo.name)) {
            return `css/[name]-[hash].${ext}`;
          }
          return `../assets/[name]-[hash].${ext}`;
        },
      },
    },
  },
  server: {
    port: 3000,
    open: true,
  },
});
