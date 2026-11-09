import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import Inspect from 'vite-plugin-inspect';

export default defineConfig(({ command }) => ({
    build: {
        // vis-timeline, cytoscape, pdfjs : chunks déjà chargés à la demande (pages ciblées).
        chunkSizeWarningLimit: 600,
    },
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
                'resources/js/admin/officials-form.js',
                'resources/js/admin/flux-transitions-index.js',
                'resources/js/admin/drag-reorder-grid.js',
                'resources/js/admin/documents-upload.js',
                'resources/js/pages/mediatheque-public.js',
                'resources/js/pages/glossaire-public.js',
                'resources/js/pages/auth-profile-photo.js',
                'resources/js/pages/simulateur-calcul.js',
                'resources/js/pages/institution-demande-adhesion.js',
                'resources/js/pages/institution-pension-standard.js',
                'resources/js/pages/institution-pension-reversion.js',
            ],
            refresh: true,
        }),
        command === 'serve' && Inspect(),
    ].filter(Boolean),
}));
