const CACHE_NAME = 'zazu-static-v22';
const OFFLINE_SHELL = '/dashboard';
let activeUserId = null;
let activeUserGeneration = 0;
let activePrimeController = null;
const MAX_PRIME_ROUTES = 100;
const PRIME_CONCURRENCY = 6;
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

async function cacheRenderedPage(request, response, requestGeneration) {
    const probe = response.clone();
    const html = await probe.text();
    if (requestGeneration !== activeUserGeneration) return;

    const match = html.match(/data-zazu-user-id="(\d+)"/);
    const pageUserId = sanitizeUserId(match?.[1]);
    if (!pageUserId) return;

    await loadActiveUser();
    if (requestGeneration !== activeUserGeneration) return;

    const cacheGeneration = await setUser(pageUserId, requestGeneration);
    if (cacheGeneration === null || cacheGeneration !== activeUserGeneration) return;

    const cacheName = userCacheName();
    const cache = cacheName ? await caches.open(cacheName) : null;
    if (!cache || cacheGeneration !== activeUserGeneration || activeUserId !== pageUserId) return;

    const requestKey = new Request(request.url);
    await cache.put(requestKey, response.clone());

    if (cacheGeneration !== activeUserGeneration || activeUserId !== pageUserId) {
        await caches.delete(cacheName);
    }
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

async function setUser(userId, expectedGeneration = null) {
    const nextUserId = sanitizeUserId(userId);
    if (!nextUserId) return null;
    if (expectedGeneration !== null && expectedGeneration !== activeUserGeneration) return null;

    const previous = activeUserId;
    if (previous && previous !== nextUserId) activeUserGeneration += 1;
    activeUserId = nextUserId;
    const generation = activeUserGeneration;

    const keys = await caches.keys();
    await Promise.all(
        keys
            .filter((key) => key.startsWith(CACHE_NAME + '-user-') && key !== userCacheName())
            .map((key) => caches.delete(key))
    );

    if (generation !== activeUserGeneration || activeUserId !== nextUserId) return null;
    const state = await caches.open(CACHE_NAME);
    if (generation !== activeUserGeneration || activeUserId !== nextUserId) return null;
    await state.put(ACTIVE_USER_STATE, new Response(activeUserId));
    return generation === activeUserGeneration && activeUserId === nextUserId ? generation : null;
}

async function primePages(routes, primeGeneration = activeUserGeneration, primeUserId = activeUserId) {
    activePrimeController?.abort();
    const controller = new AbortController();
    activePrimeController = controller;

    const uniqueRoutes = [...new Set(Array.isArray(routes) ? routes : [])]
        .filter((path) => typeof path === 'string' && path.startsWith('/') && !path.startsWith('/api/'))
        .slice(0, MAX_PRIME_ROUTES);

    try {
        for (let offset = 0; offset < uniqueRoutes.length; offset += PRIME_CONCURRENCY) {
            const batch = uniqueRoutes.slice(offset, offset + PRIME_CONCURRENCY);

            await Promise.all(batch.map(async (path) => {
                try {
                    const request = new Request(new URL(path, self.location.origin), {
                        credentials: 'include',
                        cache: 'no-store',
                    });
                    const response = await fetch(request, { signal: controller.signal });
                    if (!response.ok) return;

                    const contentType = response.headers.get('content-type') || '';
                    if (!contentType.includes('text/html')) return;

                    const probe = response.clone();
                    const html = await probe.text();
                    const match = html.match(/data-zazu-user-id="(\d+)"/);
                    const pageUserId = sanitizeUserId(match?.[1]);
                    if (!pageUserId) return;

                    if (primeGeneration !== activeUserGeneration || pageUserId !== primeUserId || pageUserId !== activeUserId) return;

                    const cache = await dynamicCache();
                    if (!cache || primeGeneration !== activeUserGeneration || pageUserId !== activeUserId) return;

                    await cache.put(new Request(new URL(path, self.location.origin)), response.clone());
                    if (primeGeneration !== activeUserGeneration || pageUserId !== activeUserId) {
                        await caches.delete(CACHE_NAME + '-user-' + pageUserId);
                    }
                } catch {
                    // A single page failing to prime must never block the installed Zazu shell.
                }
            }));
        }
    } finally {
        if (activePrimeController === controller) {
            activePrimeController = null;
        }
    }
}

async function clearActiveUser() {
    activeUserGeneration += 1;
    activePrimeController?.abort();
    activePrimeController = null;
    activeUserId = null;

    const keys = await caches.keys();
    await Promise.all(
        keys
            .filter((key) => key.startsWith(CACHE_NAME + '-user-'))
            .map((key) => caches.delete(key))
    );

    const state = await caches.open(CACHE_NAME);
    await state.delete(ACTIVE_USER_STATE);
}

self.addEventListener('message', (event) => {
    if (event.data?.type === 'clear-user') {
        event.waitUntil(clearActiveUser());
        return;
    }

    if (event.data?.type !== 'prime-pages') return;

    event.waitUntil((async () => {
        const generation = activeUserGeneration;
        await loadActiveUser();
        const activeGeneration = await setUser(event.data.userId, generation);
        if (activeGeneration === null) return;
        await primePages(event.data.routes, activeGeneration, activeUserId);
    })());
});

async function fetchWithTimeout(request, timeoutMs = 4000) {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), timeoutMs);

    try {
        return await fetch(request, { signal: controller.signal });
    } finally {
        clearTimeout(timeout);
    }
}

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (request.method === 'POST' && url.origin === self.location.origin && url.pathname === '/logout') {
        event.waitUntil(clearActiveUser());
        return;
    }

    const requestGeneration = activeUserGeneration;
    const acceptsHtml = (request.headers.get('accept') || '').includes('text/html');
    const isAppPage = request.method === 'GET'
        && url.origin === self.location.origin
        && !url.pathname.startsWith('/api/')
        && (request.mode === 'navigate' || acceptsHtml);

    if (isAppPage) {
        event.respondWith(
            fetchWithTimeout(request)
                .then((response) => {
                    if (!response.ok) return response;

                    // Keep the authenticated page cache operation attached to the
                    // navigation event. Mobile browsers may terminate a worker
                    // immediately after returning the response otherwise.
                    event.waitUntil(cacheRenderedPage(request, response.clone(), requestGeneration));
                    return response;
                })
                .catch(async () => {
                    const cache = await dynamicCache();
                    const cachedPage = cache ? await cache.match(new Request(request.url)) : null;
                    const offlineShell = cache ? await cache.match(new Request(new URL(OFFLINE_SHELL, self.location.origin))) : null;
                    return cachedPage || offlineShell || Response.error();
                })
        );
        return;
    }

    if (request.method === 'GET' && url.origin === self.location.origin && url.pathname === '/manifest.webmanifest') {
        event.respondWith(caches.match(request).then((cached) => cached || fetch(request)));
        return;
    }

    // These two scripts are precached as part of the offline client, but they
    // are not Vite /build assets. Serve the cached copies when disconnected.
    if (
        request.method === 'GET'
        && url.origin === self.location.origin
        && ['/zazu-offline.js', '/offline-attachments.js'].includes(url.pathname)
    ) {
        event.respondWith((async () => {
            const cache = await caches.open(CACHE_NAME);
            const cacheKey = new Request(new URL(url.pathname, self.location.origin));
            const cached = await cache.match(cacheKey);

            if (cached) return cached;

            const response = await fetch(request);
            if (response.ok) await cache.put(cacheKey, response.clone());
            return response;
        })());
        return;
    }

    if (isCacheableStaticAsset(url, request)) {
        event.respondWith((async () => {
            // Cache-first keeps already downloaded application assets usable
            // offline and avoids an untracked background fetch on every visit.
            const cached = await caches.match(request);
            if (cached) return cached;

            const response = await fetch(request);
            if (response.ok) {
                try {
                    // Await the write before completing the fetch event. A worker
                    // can be suspended immediately after returning the response.
                    const cache = await caches.open(CACHE_NAME);
                    await cache.put(request, response.clone());
                } catch {
                    // A cache quota/storage failure must not break the online page.
                }
            }

            return response;
        })());
    }
});
