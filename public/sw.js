const CACHE_NAME = 'zazu-static-v11';
const OFFLINE_SHELL = '/dashboard';
let activeUserId = null;
const ACTIVE_USER_STATE = new Request('/__zazu-active-user__');

const PRECACHE_ASSETS = [
    '/manifest.webmanifest',
    '/offline-attachments.js',
    '/zazu-offline.js',
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
                    .filter((key) =>
                        key.startsWith('zazu-static-')
                        && key !== CACHE_NAME
                        && !key.startsWith(CACHE_NAME + '-user-')
                    )
                    .map((key) => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

function userCacheName() {
    return activeUserId ? CACHE_NAME + '-user-' + activeUserId : null;
}

async function loadActiveUser() {
    if (activeUserId) return activeUserId;

    const cache = await caches.open(CACHE_NAME);
    const stored = await cache.match(ACTIVE_USER_STATE);
    if (!stored) return null;

    activeUserId = sanitizeUserId(await stored.text());
    return activeUserId;
}

async function dynamicCache() {
    const name = activeUserId
        ? userCacheName()
        : ((await loadActiveUser()) ? userCacheName() : null);

    return name ? caches.open(name) : null;
}

function sanitizeUserId(value) {
    const id = String(value || '');
    return /^\d+$/.test(id) ? id : null;
}

async function cacheRenderedPage(request, response) {
    const probe = response.clone();
    const html = await probe.text();
    const match = html.match(/data-zazu-user-id="(\\d+)"/);
    const pageUserId = sanitizeUserId(match?.[1]);

    if (!pageUserId) return;

    await setUser(pageUserId);
    const cache = await caches.open(userCacheName());
    await cache.put(new Request(request.url), response.clone());
}

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

async function setUser(userId) {
    const nextUserId = sanitizeUserId(userId);
    if (!nextUserId) return;

    const previous = activeUserId;
    activeUserId = nextUserId;

    const keys = await caches.keys();
    await Promise.all(
        keys
            .filter((key) => key.startsWith(CACHE_NAME + '-user-') && key !== userCacheName())
            .map((key) => caches.delete(key))
    );

    const state = await caches.open(CACHE_NAME);
    await state.put(ACTIVE_USER_STATE, new Response(activeUserId));
}

async function primePages(routes) {
    const uniqueRoutes = [...new Set(Array.isArray(routes) ? routes : [])]
        .filter((path) => typeof path === 'string' && path.startsWith('/') && !path.startsWith('/api/'))
        .slice(0, 30);

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

            const probe = response.clone();
            const html = await probe.text();
            const match = html.match(/data-zazu-user-id="(\\d+)"/);
            const pageUserId = sanitizeUserId(match?.[1]);
            if (!pageUserId) return;

            if (pageUserId !== activeUserId) {
                await setUser(pageUserId);
            }

            const cache = await dynamicCache();
            if (!cache) return;

            await cache.put(new Request(new URL(path, self.location.origin)), response.clone());
        } catch {
            // A single page failing to prime must never block the installed Zazu shell.
        }
    }));
}

self.addEventListener('message', (event) => {
    if (event.data?.type !== 'prime-pages') return;

    event.waitUntil((async () => {
        await setUser(event.data.userId);
        await primePages(event.data.routes);
    })());
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    const acceptsHtml = (request.headers.get('accept') || '').includes('text/html');
    const isAppPage = request.method === 'GET'
        && url.origin === self.location.origin
        && !url.pathname.startsWith('/api/')
        && (request.mode === 'navigate' || acceptsHtml);

    if (isAppPage) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response.ok) {
                        cacheRenderedPage(request, response.clone()).catch(() => {});
                    }
                    return response;
                })
                .catch(async () => {
                    const cache = await dynamicCache();
                    const cachedPage = cache ? await cache.match(new Request(request.url)) : null;
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
