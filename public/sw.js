const CACHE_NAME = 'zazu-static-v2';

self.addEventListener('install', () => self.skipWaiting());

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

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (!isCacheableStaticAsset(url, request)) return;

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
});
