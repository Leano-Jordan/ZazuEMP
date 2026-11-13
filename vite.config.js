import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/zazu-responsive-theme.css', 'resources/css/zazu-final-visual-sweep.css', 'resources/css/zazu-mobile-refinement.css', 'resources/css/zazu-dashboard-authority.css', 'resources/css/zazu-product-authority.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // Allow a phone/tablet on the same LAN to fetch Vite assets during local QA.
        host: '0.0.0.0',
        hmr: {
            host: process.env.VITE_HMR_HOST || undefined,
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
