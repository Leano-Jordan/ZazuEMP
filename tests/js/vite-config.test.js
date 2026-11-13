import assert from 'node:assert/strict';
import test from 'node:test';
import viteConfig from '../../vite.config.js';

test('LAN Vite asset origin follows the configured reachable host', () => {
    const previousHost = process.env.VITE_HMR_HOST;
    const previousOrigin = process.env.VITE_DEV_SERVER_ORIGIN;

    try {
        process.env.VITE_HMR_HOST = '192.0.2.25';
        delete process.env.VITE_DEV_SERVER_ORIGIN;

        const derived = viteConfig({ mode: 'test', command: 'serve' });
        assert.equal(derived.server.host, '0.0.0.0');
        assert.equal(derived.server.hmr.host, '192.0.2.25');
        assert.equal(derived.server.origin, 'http://192.0.2.25:5173');

        process.env.VITE_DEV_SERVER_ORIGIN = 'http://192.0.2.25:5180';
        const explicit = viteConfig({ mode: 'test', command: 'serve' });
        assert.equal(explicit.server.origin, 'http://192.0.2.25:5180');
        assert.equal(explicit.server.hmr.host, '192.0.2.25');
    } finally {
        if (previousHost === undefined) delete process.env.VITE_HMR_HOST;
        else process.env.VITE_HMR_HOST = previousHost;

        if (previousOrigin === undefined) delete process.env.VITE_DEV_SERVER_ORIGIN;
        else process.env.VITE_DEV_SERVER_ORIGIN = previousOrigin;
    }
});
