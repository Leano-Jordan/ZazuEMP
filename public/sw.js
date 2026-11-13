const CACHE_NAME = 'zazu-static-v36';
const OFFLINE_SHELL = '/dashboard';
let activeUserId = null;
let activeBusinessId = null;
let activeUserGeneration = 0;
let activePrimeController = null;
const MAX_PRIME_ROUTES = 100;
const PRIME_CONCURRENCY = 6;
const MAX_PRIVATE_MEDIA_PRIME = 60;
const ACTIVE_CONTEXT_STATE = new Request('/__zazu-active-context__');

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

function contextCacheName() {
    return activeUserId && activeBusinessId
        ? CACHE_NAME + '-user-' + activeUserId + '-business-' + activeBusinessId
        : null;
}

function sanitizeUserId(value) {
    const id = String(value || '');
    return /^\d+$/.test(id) ? id : null;
}

function sanitizeBusinessId(value) {
    const id = String(value || '');
    return /^\d+$/.test(id) && Number(id) > 0 ? id : null;
}

async function loadActiveContext(expectedGeneration = activeUserGeneration) {
    if (expectedGeneration !== activeUserGeneration) return null;
    if (activeUserId && activeBusinessId) return { userId: activeUserId, businessId: activeBusinessId };

    const cache = await caches.open(CACHE_NAME);
    if (expectedGeneration !== activeUserGeneration) return null;
    const stored = await cache.match(ACTIVE_CONTEXT_STATE);
    if (!stored || expectedGeneration !== activeUserGeneration) return null;

    let context;
    try {
        context = JSON.parse(await stored.text());
    } catch {
        return null;
    }

    const storedUserId = sanitizeUserId(context?.user_id);
    const storedBusinessId = sanitizeBusinessId(context?.business_id);
    if (!storedUserId || !storedBusinessId || expectedGeneration !== activeUserGeneration) return null;

    activeUserId = storedUserId;
    activeBusinessId = storedBusinessId;
    return { userId: activeUserId, businessId: activeBusinessId };
}

async function dynamicCache() {
    const name = activeUserId && activeBusinessId
        ? contextCacheName()
        : ((await loadActiveContext()) ? contextCacheName() : null);
    return name ? caches.open(name) : null;
}

async function cachedAppPage(request) {
    const cache = await dynamicCache();
    const cachedPage = cache ? await cache.match(new Request(request.url)) : null;
    const offlineShell = cache ? await cache.match(new Request(new URL(OFFLINE_SHELL, self.location.origin))) : null;
    return cachedPage || offlineShell || Response.error();
}

async function cacheRenderedPage(request, response, requestGeneration) {
    const html = await response.clone().text();
    if (requestGeneration !== activeUserGeneration) return;

    const userMatch = html.match(/data-zazu-user-id="(\d+)"/);
    const businessMatch = html.match(/data-zazu-business-id="(\d+)"/);
    const pageUserId = sanitizeUserId(userMatch?.[1]);
    const pageBusinessId = sanitizeBusinessId(businessMatch?.[1]);
    if (!pageUserId || !pageBusinessId) return;

    await loadActiveContext(requestGeneration);
    if (requestGeneration !== activeUserGeneration) return;

    const cacheGeneration = await setContext(pageUserId, pageBusinessId, requestGeneration);
    if (cacheGeneration === null || cacheGeneration !== activeUserGeneration) return;

    const cacheName = contextCacheName();
    const cache = cacheName ? await caches.open(cacheName) : null;
    if (!cache || cacheGeneration !== activeUserGeneration
        || activeUserId !== pageUserId || activeBusinessId !== pageBusinessId) return;

    await cache.put(new Request(request.url), response.clone());
    if (cacheGeneration !== activeUserGeneration
        || activeUserId !== pageUserId || activeBusinessId !== pageBusinessId) {
        await caches.delete(cacheName);
    }
}

async function primeStaticAsset(url, generation, seenUrls, signal, depth = 0) {
    if (generation !== activeUserGeneration || seenUrls.size >= 150) return;

    let assetUrl;
    try {
        assetUrl = new URL(url, self.location.origin);
    } catch {
        return;
    }

    if (assetUrl.origin !== self.location.origin
        || !(/^\/build\//.test(assetUrl.pathname) || /^\/images\//.test(assetUrl.pathname))
        || seenUrls.has(assetUrl.href)) return;

    seenUrls.add(assetUrl.href);

    try {
        const request = new Request(assetUrl.href, {
            credentials: 'same-origin',
            cache: 'no-store',
        });
        const response = await fetch(request, { signal });
        if (!response.ok || generation !== activeUserGeneration) return;

        const cache = await caches.open(CACHE_NAME);
        if (generation !== activeUserGeneration) return;
        await cache.put(request, response.clone());

        const contentType = response.headers.get('content-type') || '';
        if (depth >= 1 || !contentType.toLowerCase().includes('text/css')) return;

        const css = await response.clone().text();
        const references = [];
        for (const match of css.matchAll(/url\(\s*(['"]?)(.*?)\1\s*\)/gi)) {
            if (match[2]) references.push(match[2]);
        }
        await Promise.all(references.map((reference) => {
            let child;
            try {
                child = new URL(reference, assetUrl);
            } catch {
                return Promise.resolve();
            }
            return primeStaticAsset(child.href, generation, seenUrls, signal, depth + 1);
        }));
    } catch {
        // An individual build asset must not block the rest of the offline workspace.
    }
}

async function primeStaticAssetsFromHtml(html, generation, seenUrls, signal) {
    if (generation !== activeUserGeneration) return;

    const references = [];
    for (const match of html.matchAll(/(?:href|src)=["']([^"'#]+)["']/gi)) {
        if (match[1]) references.push(match[1]);
    }

    await Promise.all(references.map((reference) => {
        let assetUrl;
        try {
            assetUrl = new URL(reference, self.location.origin);
        } catch {
            return Promise.resolve();
        }
        return primeStaticAsset(assetUrl.href, generation, seenUrls, signal);
    }));
}

async function primePrivateMediaFromHtml(html, generation, userId, businessId, seenUrls, signal) {
    if (generation !== activeUserGeneration
        || activeUserId !== userId || activeBusinessId !== businessId) return;

    const matches = html.matchAll(/\/media\/(?:profile|business)\/[A-Za-z0-9/_-]+(?:\?[^"'()\s<>]*)?/g);
    const urls = [];

    for (const match of matches) {
        if (seenUrls.size >= MAX_PRIVATE_MEDIA_PRIME) break;

        let url;
        try {
            url = new URL(match[0].replace(/&amp;/g, '&'), self.location.origin);
        } catch {
            continue;
        }

        if (url.origin !== self.location.origin
            || !/^\/media\/(profile|business)\//.test(url.pathname)
            || seenUrls.has(url.href)) continue;

        seenUrls.add(url.href);
        urls.push(url);
    }

    await Promise.all(urls.map(async (url) => {
        if (generation !== activeUserGeneration
            || activeUserId !== userId || activeBusinessId !== businessId) return;

        try {
            const request = new Request(url.href, {
                credentials: 'include',
                cache: 'no-store',
            });
            const response = await fetch(request, { signal });
            await cachePrivateMediaResponse(request, response, generation, userId, businessId);
        } catch {
            // An unavailable image must not prevent the rest of the offline pages from being primed.
        }
    }));
}

async function cachePrivateMediaResponse(request, response, requestGeneration, userId, businessId) {
    const contentType = response.headers.get('content-type') || '';
    if (!response.ok || !contentType.toLowerCase().startsWith('image/')) return;

    await loadActiveContext(requestGeneration);
    if (requestGeneration !== activeUserGeneration
        || activeUserId !== userId || activeBusinessId !== businessId) return;

    const cacheName = contextCacheName();
    if (!cacheName) return;
    const cache = await caches.open(cacheName);
    if (requestGeneration !== activeUserGeneration
        || activeUserId !== userId || activeBusinessId !== businessId) return;

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

let userCacheMutationQueue = Promise.resolve();

function serializeUserCacheMutation(mutation) {
    const result = userCacheMutationQueue.then(mutation, mutation);
    userCacheMutationQueue = result.catch(() => {});
    return result;
}

async function setContext(userId, businessId, expectedGeneration = null) {
    const nextUserId = sanitizeUserId(userId);
    const nextBusinessId = sanitizeBusinessId(businessId);
    if (!nextUserId || !nextBusinessId) return null;
    if (expectedGeneration !== null && expectedGeneration !== activeUserGeneration) return null;

    if ((activeUserId && activeUserId !== nextUserId)
        || (activeBusinessId && activeBusinessId !== nextBusinessId)) {
        activeUserGeneration += 1;
    }
    activeUserId = nextUserId;
    activeBusinessId = nextBusinessId;
    const generation = activeUserGeneration;
    const cacheName = contextCacheName();

    return serializeUserCacheMutation(async () => {
        if (generation !== activeUserGeneration
            || activeUserId !== nextUserId || activeBusinessId !== nextBusinessId) return null;

        const keys = await caches.keys();
        await Promise.all(
            keys.filter((key) => key.startsWith(CACHE_NAME + '-user-') && key !== cacheName)
                .map((key) => caches.delete(key))
        );

        if (generation !== activeUserGeneration
            || activeUserId !== nextUserId || activeBusinessId !== nextBusinessId) return null;

        const state = await caches.open(CACHE_NAME);
        await state.put(ACTIVE_CONTEXT_STATE, new Response(JSON.stringify({
            user_id: activeUserId,
            business_id: activeBusinessId,
        })));

        return generation === activeUserGeneration
            && activeUserId === nextUserId && activeBusinessId === nextBusinessId
            ? generation : null;
    });
}

async function primePages(routes, primeGeneration = activeUserGeneration, primeUserId = activeUserId, primeBusinessId = activeBusinessId) {
    activePrimeController?.abort();
    const controller = new AbortController();
    activePrimeController = controller;

    const uniqueRoutes = [...new Set(Array.isArray(routes) ? routes : [])]
        .filter((path) => typeof path === 'string' && path.startsWith('/') && !path.startsWith('/api/'))
        .slice(0, MAX_PRIME_ROUTES);

    const primedPrivateMediaUrls = new Set();
    const primedStaticAssetUrls = new Set();

    try {
        for (let offset = 0; offset < uniqueRoutes.length; offset += PRIME_CONCURRENCY) {
            if (controller.signal.aborted) break;
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

                    const html = await response.clone().text();
                    const userMatch = html.match(/data-zazu-user-id="(\d+)"/);
                    const businessMatch = html.match(/data-zazu-business-id="(\d+)"/);
                    const pageUserId = sanitizeUserId(userMatch?.[1]);
                    const pageBusinessId = sanitizeBusinessId(businessMatch?.[1]);
                    if (!pageUserId || !pageBusinessId) return;

                    if (primeGeneration !== activeUserGeneration
                        || pageUserId !== primeUserId || pageBusinessId !== primeBusinessId
                        || pageUserId !== activeUserId || pageBusinessId !== activeBusinessId) return;

                    const cache = await dynamicCache();
                    if (!cache || primeGeneration !== activeUserGeneration
                        || pageUserId !== activeUserId || pageBusinessId !== activeBusinessId) return;

                    await cache.put(new Request(new URL(path, self.location.origin)), response.clone());
                    await primeStaticAssetsFromHtml(html, primeGeneration, primedStaticAssetUrls, controller.signal);
                    await primePrivateMediaFromHtml(
                        html,
                        primeGeneration,
                        primeUserId,
                        primeBusinessId,
                        primedPrivateMediaUrls,
                        controller.signal,
                    );
                } catch {
                    // A single page failing to prime must never block the installed Zazu shell.
                }
            }));
        }
    } finally {
        if (activePrimeController === controller) activePrimeController = null;
    }
}

async function clearActiveContext() {
    activeUserGeneration += 1;
    activePrimeController?.abort();
    activePrimeController = null;
    activeUserId = null;
    activeBusinessId = null;

    return serializeUserCacheMutation(async () => {
        const keys = await caches.keys();
        await Promise.all(keys.filter((key) => key.startsWith(CACHE_NAME + '-user-')).map((key) => caches.delete(key)));
        const state = await caches.open(CACHE_NAME);
        await state.delete(ACTIVE_CONTEXT_STATE);
    });
}

self.addEventListener('message', (event) => {
    if (event.data?.type === 'clear-user') {
        event.waitUntil(clearActiveContext());
        return;
    }
    if (event.data?.type !== 'prime-pages') return;

    event.waitUntil((async () => {
        const generation = activeUserGeneration;
        await loadActiveContext(generation);
        if (generation !== activeUserGeneration) return;
        const activeGeneration = await setContext(event.data.userId, event.data.businessId, generation);
        if (activeGeneration === null) return;
        await primePages(event.data.routes, activeGeneration, activeUserId, activeBusinessId);
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
        event.waitUntil(clearActiveContext());
        return;
    }

    if (request.method === 'POST' && url.origin === self.location.origin && url.pathname === '/business/switch') {
        event.respondWith((async () => {
            const response = await fetch(request);
            if (response.ok || (response.status >= 300 && response.status < 400)) await clearActiveContext();
            return response;
        })());
        return;
    }

    const requestGeneration = activeUserGeneration;
    const acceptsHtml = (request.headers.get('accept') || '').includes('text/html');
    const isAppPage = request.method === 'GET'
        && url.origin === self.location.origin
        && !url.pathname.startsWith('/api/')
        && (request.mode === 'navigate' || acceptsHtml);

    if (isAppPage) {
        if (request.mode === 'navigate') {
            // Real navigation takes priority over background cache warming on constrained hosts.
            activePrimeController?.abort();
        }

        event.respondWith((async () => {
            // When the browser reports no network, do not spend four seconds
            // attempting the unreachable Laravel host. Resolve the authenticated
            // page from its user/business cache immediately instead.
            if (self.navigator?.onLine === false) {
                return cachedAppPage(request);
            }

            try {
                const response = await fetchWithTimeout(request);
                if (!response.ok) return response;

                // Keep the authenticated page cache operation attached to the
                // navigation event. Mobile browsers may terminate a worker
                // immediately after returning the response otherwise.
                event.waitUntil(cacheRenderedPage(request, response.clone(), requestGeneration));
                return response;
            } catch {
                return cachedAppPage(request);
            }
        })());
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

    // Uploaded business branding and profile photos are private media.
    // Keep them in the authenticated workspace cache, never the shared static cache.
    if (
        request.method === 'GET'
        && url.origin === self.location.origin
        // Match the private media route, not request.destination: CSS background
        // images can have an empty destination in some browser fetch paths.
        && /^\/media\/(profile|business)\//.test(url.pathname)
    ) {
        event.respondWith((async () => {
            const context = await loadActiveContext(requestGeneration);
            if (!context || requestGeneration !== activeUserGeneration) return fetch(request);

            const cacheName = contextCacheName();
            const cache = cacheName ? await caches.open(cacheName) : null;
            if (!cache || requestGeneration !== activeUserGeneration) return fetch(request);

            const cached = await cache.match(new Request(request.url));
            if (cached) return cached;

            const response = await fetch(request);
            await cachePrivateMediaResponse(
                request,
                response,
                requestGeneration,
                context.userId,
                context.businessId,
            );
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