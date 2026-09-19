import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/po-create.js',
                'resources/js/po-show.js',
                'resources/js/po-index.js',
            ],
            refresh: true,
        }),
    ],
});
