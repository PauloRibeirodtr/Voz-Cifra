import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/admin/versoes-musicais-form.css',
                'resources/js/admin/versoes-musicais-form.js',
                'resources/css/publico/igreja.css',
                'resources/js/publico/igreja.js',
                'resources/css/publico/music.css',
                'resources/js/publico/music.js',
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
