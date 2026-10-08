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
            ],
            refresh: true,
        }),
    ],
});
