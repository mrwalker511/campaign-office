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
      // Externalize WordPress-provided libraries
      external: [
        'react',
        'react-dom',
        '@wordpress/element',
        '@wordpress/components',
        '@wordpress/blocks',
        '@wordpress/block-editor',
        '@wordpress/i18n',
        '@wordpress/data',
        '@wordpress/api-fetch',
      ],
    },
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, '..', 'assets/react'),
      // Map React imports to WordPress globals
      'react': 'wp.element',
      'react-dom': 'wp.element',
    },
  },
  define: {
    // Define WordPress globals for build
    'process.env.NODE_ENV': JSON.stringify('production'),
  },
});
