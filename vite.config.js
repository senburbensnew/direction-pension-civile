import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import Inspect from 'vite-plugin-inspect';

export default defineConfig(({ command }) => ({
    server: {
        host: '127.0.0.1',
        hmr: {
            host: '127.0.0.1',
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        command === 'serve' && Inspect(),
    ].filter(Boolean),
}));
