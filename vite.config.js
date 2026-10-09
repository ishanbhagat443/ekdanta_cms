import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/scss/frontend.scss',
                'resources/js/frontend/app.js',
                // Ekdanta section pack: loaded only on pages that use it.
                'resources/scss/ekdanta.scss',
                'resources/js/frontend/ekdanta.js',
            ],
            refresh: true,
        }),
    ],
});
