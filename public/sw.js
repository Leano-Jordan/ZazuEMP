const CACHE_NAME = 'zazu-static-v9';
const OFFLINE_SHELL = '/dashboard';

const PRECACHE_ASSETS = [
    OFFLINE_SHELL,
    '/manifest.webmanifest',
    '/offline-attachments.js',
    '/icons/zazu-192.svg',
    '/icons/zazu-512.svg',
    '/images/landing/stock/hero.jpg',
    '/images/landing/stock/catering.jpg',
    '/images/landing/stock/sound.jpg',
    '/images/landing/stock/venue.jpg',
    '/images/landing/stock/tent.jpg',
    '/images/landing/stock/decor.jpg',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(async (cache) => {
                await Promise.all(
                    PRECACHE_ASSETS.map(async (asset) => {
                        try {
                            await cache.add(asset);
                        } catch {
                            // One optional visual asset must never prevent the offline app shell from installing.
                        }
                    })
                );
            })
            .finally(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key.startsWith('zazu-static-') && key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

function isCacheableStaticAsset(url, request) {
    if (request.method !== 'GET' || url.origin !== self.location.origin) return false;

    const path = url.pathname;

    return (
        request.destination === 'style' && path.startsWith('/build/')
    ) || (
        request.destination === 'script' && path.startsWith('/build/')
    ) || (
        request.destination === 'font' && path.startsWith('/build/')
    ) || (
        request.destination === 'image' && (
            path.startsWith('/build/') ||
            path.startsWith('/images/')
        )
    );
}

async function primePages(routes) {
    const uniqueRoutes = [...new Set(Array.isArray(routes) ? routes : [])]
        .filter((path) => typeof path === 'string' && path.startsWith('/') && !path.startsWith('/api/'))
        .slice(0, 30);

    const cache = await caches.open(CACHE_NAME);

    await Promise.all(uniqueRoutes.map(async (path) => {
        try {
            const request = new Request(new URL(path, self.location.origin), {
                credentials: 'include',
                cache: 'no-store',
            });
            const response = await fetch(request);
            if (!response.ok) return;

            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('text/html')) return;

            await cache.put(new Request(new URL(path, self.location.origin)), response.clone());
        } catch {
            // A single page failing to prime must never block the installed Zazu shell.
        }
    }));
}

self.addEventListener('message', (event) => {
    if (event.data?.type !== 'prime-pages') return;
    event.waitUntil(primePages(event.data.routes));
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (request.method === 'GET' && request.mode === 'navigate' && url.origin === self.location.origin) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response.ok) {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    }
                    return response;
                })
                .catch(async () => {
                    const cachedPage = await caches.match(request);
                    return cachedPage || caches.match(OFFLINE_SHELL);
                })
        );
        return;
    }

    if (request.method === 'GET' && url.origin === self.location.origin && url.pathname === '/manifest.webmanifest') {
        event.respondWith(caches.match(request).then((cached) => cached || fetch(request)));
        return;
    }

    if (isCacheableStaticAsset(url, request)) {
        event.respondWith(
            caches.match(request).then((cached) => {
                const network = fetch(request).then((response) => {
                    if (response.ok) {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    }
                    return response;
                }).catch(() => cached);

                return cached || network;
            })
        );
    }
});
