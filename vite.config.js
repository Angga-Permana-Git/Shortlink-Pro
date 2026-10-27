import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/publik/dasar.css',
                'resources/css/publik/beranda.css',
                'resources/css/publik/auth.css',
                'resources/css/publik/linktree-publik.css',
                'resources/css/publik/tautan.css',
                'resources/css/publik/panel.css',
                'resources/js/publik/beranda.js',
                'resources/js/publik/panel.js',
                'resources/js/publik/auth.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
