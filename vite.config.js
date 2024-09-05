import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.js', 'resources/sass/app.scss'], // Asegúrate de incluir app.scss aquí
      refresh: true,
    }),
  ],
});
