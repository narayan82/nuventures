import { defineConfig } from 'vite';
import path from 'node:path';

export default defineConfig({
  build: {
    outDir: 'assets/dist',
    emptyOutDir: true,

    rollupOptions: {
      input: path.resolve(__dirname, 'src/js/main.js'),

      output: {
        format: 'iife',
        entryFileNames: 'js/main.js',
      },
    },
  },
});