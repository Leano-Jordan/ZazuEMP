import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => {
    // Vite does not automatically expose .env values to process.env while
    // evaluating this config; load the VITE_* values explicitly.
    const env = loadEnv(mode, process.cwd(), 'VITE_');
    const hmrHost = env.VITE_HMR_HOST || process.env.VITE_HMR_HOST || undefined;
    const devServerOrigin = env.VITE_DEV_SERVER_ORIGIN
        || process.env.VITE_DEV_SERVER_ORIGIN
        || (hmrHost ? `http://${hmrHost}:5173` : undefined);

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/css/zazu-responsive-theme.css', 'resources/css/zazu-final-visual-sweep.css', 'resources/css/zazu-mobile-refinement.css', 'resources/css/zazu-dashboard-authority.css', 'resources/css/zazu-product-authority.css', 'resources/js/app.js'],
                refresh: true,
            }),
            tailwindcss(),
        ],
        server: {
            // Bind locally while allowing same-LAN devices to fetch development assets.
            host: '0.0.0.0',
            // HMR host alone changes the websocket endpoint, not CSS/JS asset URLs.
            // Point generated asset URLs at the same reachable PC address.
            origin: devServerOrigin,
            hmr: {
                host: hmrHost,
            },
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});
