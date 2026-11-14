import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';
import vm from 'node:vm';

const ORIGIN = 'https://zazu.test';
const workerSource = await readFile(new URL('../../public/sw.js', import.meta.url), 'utf8');

function deferred() {
    let resolve;
    const promise = new Promise((resolvePromise) => {
        resolve = resolvePromise;
    });
    return { promise, resolve };
}

class MockResponse {
    static error() {
        return new MockResponse('', { ok: false, status: 0 });
    }

    constructor(body = '', options = {}) {
        this.body = body;
        this.ok = options.ok ?? true;
        this.status = options.status ?? (this.ok ? 200 : 500);
        this.headerValues = options.headers || {};
        this.headers = { get: (name) => this.headerValues[name.toLowerCase()] ?? null };
    }

    clone() {
        return new MockResponse(this.body, {
            ok: this.ok,
            status: this.status,
            headers: this.headerValues,
        });
    }

    async text() {
        return typeof this.body === 'function' ? this.body() : String(this.body);
    }
}


function bootWorker(fetchImplementation = async () => new MockResponse('online'), cacheHooks = {}, initiallyOnline = true) {
    const listeners = new Map();
    const cacheStore = new Map();
    const fetchCalls = [];

    function requestUrl(input) {
        const raw = typeof input === 'string'
            ? input
            : input instanceof URL
                ? input.href
                : input?.url;
        return new URL(raw, ORIGIN).href;
    }

    class MockRequest {
        constructor(input, init = {}) {
            this.url = requestUrl(input);
            this.method = init.method || input?.method || 'GET';
            this.mode = init.mode || input?.mode || 'cors';
            this.destination = init.destination || input?.destination || '';
            const suppliedHeaders = init.headers || input?.headers || {};
            this.headers = typeof suppliedHeaders.get === 'function'
                ? suppliedHeaders
                : { get: (name) => suppliedHeaders[name.toLowerCase()] ?? null };
        }
    }


    function cacheFor(name) {
        if (!cacheStore.has(name)) cacheStore.set(name, new Map());
        const entries = cacheStore.get(name);
        return {
            match: async (key) => entries.get(requestUrl(key)) ?? null,
            put: async (key, response) => {
                const normalizedKey = requestUrl(key);
                if (cacheHooks.beforePut) await cacheHooks.beforePut({ name, key: normalizedKey, response });
                entries.set(normalizedKey, response);
            },
            add: async (key) => { entries.set(requestUrl(key), new MockResponse('asset')); },
            delete: async (key) => entries.delete(requestUrl(key)),
        };
    }

    const caches = {
        open: async (name) => cacheFor(name),
        keys: async () => [...cacheStore.keys()],
        delete: async (name) => cacheStore.delete(name),
        match: async (key) => {
            for (const name of cacheStore.keys()) {
                const match = await cacheFor(name).match(key);
                if (match) return match;
            }
            return null;
        },
    };

    const self = {
        location: { origin: ORIGIN },
        navigator: { onLine: initiallyOnline },
        clients: { claim: async () => {} },
        skipWaiting: async () => {},
        addEventListener: (name, handler) => listeners.set(name, handler),
    };

    const context = {
        self,
        caches,
        Request: MockRequest,
        Response: MockResponse,
        URL,
        AbortController,
        Promise,
        setTimeout,
        clearTimeout,
        console,
        fetch: async (...args) => {
            fetchCalls.push(args[0]);
            return fetchImplementation(...args);
        },
    };

    vm.runInNewContext(workerSource, context, { filename: 'public/sw.js' });

    function dispatchMessage(data) {
        const pending = [];
        listeners.get('message')({
            data,
            waitUntil: (promise) => pending.push(Promise.resolve(promise)),
        });
        return Promise.all(pending);
    }

    function dispatchFetch(request) {
        const pending = [];
        let responsePromise;
        listeners.get('fetch')({
            request,
            respondWith: (promise) => { responsePromise = Promise.resolve(promise); },
            waitUntil: (promise) => pending.push(Promise.resolve(promise)),
        });
        return {
            response: responsePromise,
            finished: async () => { await Promise.all(pending); },
        };
    }

    return { caches, dispatchFetch, dispatchMessage, fetchCalls, Request: MockRequest, Response: MockResponse, setOnline: (online) => { self.navigator.onLine = online; } };
}

test('authenticated uploaded images are cached only in the active business cache and remain available offline', async () => {
    let hostAvailable = true;
    const worker = bootWorker(async (request) => {
        if (!hostAvailable) throw new Error('Host server unavailable');
        return new MockResponse('image-bytes', {
            headers: { 'content-type': 'image/png', 'cache-control': 'private, no-store' },
        });
    });

    await worker.dispatchMessage({
        type: 'prime-pages',
        userId: '41',
        businessId: '17',
        routes: [],
    });

    const imageRequest = () => new worker.Request('/media/business/logo', {
        method: 'GET',
        // CSS background-image requests may not carry an image destination.
        destination: '',
    });
    const online = worker.dispatchFetch(imageRequest());
    const onlineResponse = await online.response;
    assert.equal(onlineResponse.ok, true);
    await online.finished();

    const names = await worker.caches.keys();
    assert.ok(names.includes('zazu-static-v37-user-41-business-17'));
    assert.equal(names.includes('zazu-static-v37'), true);
    const privateCache = await worker.caches.open('zazu-static-v37-user-41-business-17');
    assert.ok(await privateCache.match('/media/business/logo'));
    const staticCache = await worker.caches.open('zazu-static-v37');
    assert.equal(await staticCache.match('/media/business/logo'), null, 'private media must not enter the shared cache');

    hostAvailable = false;
    const offline = worker.dispatchFetch(imageRequest());
    const offlineResponse = await offline.response;
    assert.equal(offlineResponse.ok, true);
    assert.equal(await offlineResponse.text(), 'image-bytes');
    await offline.finished();
});

test('page priming caches referenced private images before disconnected launch', async () => {
    let hostAvailable = true;
    const worker = bootWorker(async (request) => {
        if (!hostAvailable) throw new Error('Host server unavailable');
        const url = new URL(request.url);
        if (url.pathname === '/dashboard') {
            return new MockResponse(
                '<body data-zazu-user-id="41" data-zazu-business-id="17">' +
                '<img src="/media/profile/customer/12">' +
                '<style>.hero{background-image:url("/media/business/dashboard?v=9")}</style>' +
                '</body>',
                { headers: { 'content-type': 'text/html; charset=utf-8' } },
            );
        }
        if (url.pathname.startsWith('/media/')) {
            return new MockResponse('private-image-bytes', {
                headers: { 'content-type': 'image/png', 'cache-control': 'private, no-store' },
            });
        }
        return new MockResponse('online');
    });

    await worker.dispatchMessage({
        type: 'prime-pages',
        userId: '41',
        businessId: '17',
        routes: ['/dashboard'],
    });

    const privateCache = await worker.caches.open('zazu-static-v37-user-41-business-17');
    assert.ok(await privateCache.match('/media/profile/customer/12'));
    assert.ok(await privateCache.match('/media/business/dashboard?v=9'));
    const staticCache = await worker.caches.open('zazu-static-v37');
    assert.equal(await staticCache.match('/media/profile/customer/12'), null);
    assert.equal(await staticCache.match('/media/business/dashboard?v=9'), null);

    hostAvailable = false;
    const profileImage = worker.dispatchFetch(new worker.Request('/media/profile/customer/12', {
        method: 'GET',
        destination: '',
    }));
    const backgroundImage = worker.dispatchFetch(new worker.Request('/media/business/dashboard?v=9', {
        method: 'GET',
        destination: '',
    }));
    assert.equal(await (await profileImage.response).text(), 'private-image-bytes');
    assert.equal(await (await backgroundImage.response).text(), 'private-image-bytes');
    await Promise.all([profileImage.finished(), backgroundImage.finished()]);
});

test('page priming caches the page CSS, JavaScript and CSS dependencies for a styled offline launch', async () => {
    let hostAvailable = true;
    const worker = bootWorker(async (request) => {
        if (!hostAvailable) throw new Error('Host server unavailable');
        const url = new URL(request.url);
        if (url.pathname === '/dashboard') {
            return new MockResponse(
                '<body data-zazu-user-id="41" data-zazu-business-id="17">' +
                '<link rel="stylesheet" href="/build/assets/app.css?id=abc">' +
                '<script src="/build/assets/app.js?id=def"></script>' +
                '</body>',
                { headers: { 'content-type': 'text/html; charset=utf-8' } },
            );
        }
        if (url.pathname === '/build/assets/app.css') {
            return new MockResponse(
                '.hero{background-image:url("/images/hero.jpg")} @font-face{src:url("./font.woff2")}',
                { headers: { 'content-type': 'text/css' } },
            );
        }
        if (url.pathname === '/build/assets/app.js') {
            return new MockResponse('window.zazuReady = true;', {
                headers: { 'content-type': 'text/javascript' },
            });
        }
        if (url.pathname === '/images/hero.jpg') {
            return new MockResponse('hero-image-bytes', { headers: { 'content-type': 'image/jpeg' } });
        }
        if (url.pathname === '/build/assets/font.woff2') {
            return new MockResponse('font-bytes', { headers: { 'content-type': 'font/woff2' } });
        }
        return new MockResponse('online');
    });

    await worker.dispatchMessage({
        type: 'prime-pages',
        userId: '41',
        businessId: '17',
        routes: ['/dashboard'],
    });

    const staticCache = await worker.caches.open('zazu-static-v37');
    for (const path of [
        '/build/assets/app.css?id=abc',
        '/build/assets/app.js?id=def',
        '/images/hero.jpg',
        '/build/assets/font.woff2',
    ]) {
        assert.ok(await staticCache.match(path), path + ' must be cached before disconnect');
    }

    hostAvailable = false;
    const offlineAssets = [
        ['/build/assets/app.css?id=abc', 'style'],
        ['/build/assets/app.js?id=def', 'script'],
        ['/images/hero.jpg', 'image'],
        ['/build/assets/font.woff2', 'font'],
    ];
    for (const [path, destination] of offlineAssets) {
        const result = worker.dispatchFetch(new worker.Request(path, { method: 'GET', destination }));
        const response = await result.response;
        assert.equal(response.ok, true, path + ' must be served from the offline cache');
        await result.finished();
    }
});

test('uploaded images are not cached without an authenticated business context', async () => {
    const worker = bootWorker(async () => new MockResponse('image-bytes', {
        headers: { 'content-type': 'image/png' },
    }));

    const request = new worker.Request('/media/profile/customer/12', {
        method: 'GET',
        destination: 'image',
    });
    const result = worker.dispatchFetch(request);
    assert.equal((await result.response).ok, true);
    await result.finished();

    const names = await worker.caches.keys();
    assert.equal(names.some(name => name.includes('-user-')), false);
});

test('a persisted identity read finishing after logout cannot restore the identity or prime private pages', async () => {
    const worker = bootWorker();
    const cache = await worker.caches.open('zazu-static-v37');
    const readStarted = deferred();
    const releaseRead = deferred();

    await cache.put('/__zazu-active-context__', new MockResponse(() => {
        readStarted.resolve();
        return releaseRead.promise;
    }));
    const privateCache = await worker.caches.open('zazu-static-v37-user-41-business-17');
    await privateCache.put('/dashboard', new MockResponse('private dashboard'));

    const priming = worker.dispatchMessage({
        type: 'prime-pages',
        userId: '41',
        businessId: '17',
        routes: ['/dashboard'],
    });

    await readStarted.promise;
    await worker.dispatchMessage({ type: 'clear-user' });
    releaseRead.resolve(JSON.stringify({ user_id: '41', business_id: '17' }));
    await priming;

    assert.equal(worker.fetchCalls.length, 0, 'stale identity must not trigger page priming');
    assert.equal(
        (await worker.caches.keys()).some((name) => name.startsWith('zazu-static-v37-user-')),
        false,
        'logout must leave no cached authenticated page',
    );
    assert.equal(await cache.match('/__zazu-active-context__'), null, 'logout must leave no persisted active identity');
});

test('an authenticated HTML response finishing after logout cannot repopulate the private page cache', async () => {
    const networkResponse = deferred();
    const networkStarted = deferred();
    const worker = bootWorker(async () => {
        networkStarted.resolve();
        return networkResponse.promise;
    });

    const staticCache = await worker.caches.open('zazu-static-v37');
    await staticCache.put('/__zazu-active-context__', new MockResponse(JSON.stringify({ user_id: '41', business_id: '17' })));
    const privateCache = await worker.caches.open('zazu-static-v37-user-41-business-17');
    await privateCache.put('/dashboard', new MockResponse('<main>private dashboard</main>'));

    const navigation = worker.dispatchFetch(new worker.Request('/dashboard', {
        method: 'GET',
        mode: 'navigate',
        headers: { accept: 'text/html' },
    }));

    await networkStarted.promise;
    await worker.dispatchFetch(new worker.Request('/logout', { method: 'POST' })).finished();
    networkResponse.resolve(new MockResponse('<main data-zazu-user-id="41" data-zazu-business-id="17">late response</main>', {
        headers: { 'content-type': 'text/html' },
    }));

    const response = await navigation.response;
    assert.equal(response.ok, true, 'the online navigation response should remain unaffected');
    await navigation.finished();

    assert.equal(
        (await worker.caches.keys()).some((name) => name.startsWith('zazu-static-v37-user-')),
        false,
        'a late response must not recreate the logged-out user cache',
    );
    assert.equal(await staticCache.match('/__zazu-active-context__'), null, 'the persisted identity must remain cleared');
});


test('logout clears a context that was primed immediately before the session ended', async () => {
    const worker = bootWorker();
    const priming = worker.dispatchMessage({
        type: 'prime-pages',
        userId: '41',
        businessId: '17',
        routes: [],
    });
    const logout = worker.dispatchMessage({ type: 'clear-user' });

    await Promise.all([priming, logout]);

    const state = await worker.caches.open('zazu-static-v37');
    assert.equal(await state.match('/__zazu-active-context__'), null);
    assert.equal(
        (await worker.caches.keys()).some((name) => name.startsWith('zazu-static-v37-user-')),
        false,
        'logout must clear any page cache created immediately before it',
    );
});

test('a cached authenticated dashboard remains available when the host returns a transient server error', async () => {
    let hostStatus = 200;
    const worker = bootWorker(async () => new MockResponse(
        hostStatus === 200
            ? '<!doctype html><html><body data-zazu-user-id="41" data-zazu-business-id="17"><main>Fresh dashboard</main></body></html>'
            : 'Temporarily unavailable',
        {
            ok: hostStatus === 200,
            status: hostStatus,
            headers: { 'content-type': 'text/html; charset=utf-8' },
        },
    ));

    await worker.dispatchMessage({
        type: 'prime-pages',
        userId: '41',
        businessId: '17',
        routes: ['/dashboard'],
    });

    hostStatus = 503;
    const navigation = worker.dispatchFetch(new worker.Request('/dashboard', {
        method: 'GET',
        mode: 'navigate',
        headers: { accept: 'text/html' },
    }));

    const response = await navigation.response;
    assert.equal(response.ok, true, 'cached workspace should survive a temporary host failure');
    assert.match(await response.text(), /Fresh dashboard/);
    await navigation.finished();
});

test('a cached authenticated dashboard is never used to mask an authorization failure', async () => {
    let hostStatus = 200;
    const worker = bootWorker(async () => new MockResponse(
        hostStatus === 200
            ? '<!doctype html><html><body data-zazu-user-id="41" data-zazu-business-id="17"><main>Private dashboard</main></body></html>'
            : 'Authentication required',
        {
            ok: hostStatus === 200,
            status: hostStatus,
            headers: { 'content-type': 'text/html; charset=utf-8' },
        },
    ));

    await worker.dispatchMessage({
        type: 'prime-pages',
        userId: '41',
        businessId: '17',
        routes: ['/dashboard'],
    });

    hostStatus = 401;
    const navigation = worker.dispatchFetch(new worker.Request('/dashboard', {
        method: 'GET',
        mode: 'navigate',
        headers: { accept: 'text/html' },
    }));

    const response = await navigation.response;
    assert.equal(response.status, 401, 'authorization failures must not reveal stale cached pages');
    assert.match(await response.text(), /Authentication required/);
    await navigation.finished();
});

test('a cached authenticated dashboard opens locally when the host server is unavailable', async () => {
    let hostAvailable = true;
    const worker = bootWorker(async () => {
        if (!hostAvailable) throw new Error('Host server unavailable');
        return new MockResponse(
            '<!doctype html><html><body data-zazu-user-id="41" data-zazu-business-id="17"><main>Saved local dashboard</main></body></html>',
            { headers: { 'content-type': 'text/html; charset=utf-8' } },
        );
    });

    await worker.dispatchMessage({
        type: 'prime-pages',
        userId: '41',
        businessId: '17',
        routes: ['/dashboard'],
    });

    hostAvailable = false;
    const navigation = worker.dispatchFetch(new worker.Request('/dashboard', {
        method: 'GET',
        mode: 'navigate',
        headers: { accept: 'text/html' },
    }));

    const response = await navigation.response;
    assert.equal(response.ok, true, 'the saved dashboard should be returned without the host');
    assert.match(await response.text(), /Saved local dashboard/);
    await navigation.finished();
});


test('a successful business switch clears the previous business page cache before the new workspace is cached', async () => {
    let activeBusiness = '17';
    const worker = bootWorker(async (request) => {
        const url = new URL(request.url);
        if (url.pathname === '/business/switch') {
            activeBusiness = '29';
            return new MockResponse('redirect', { ok: false, status: 302 });
        }
        if (url.pathname === '/dashboard') {
            return new MockResponse(
                '<!doctype html><html><body data-zazu-user-id="41" data-zazu-business-id="' + activeBusiness + '"><main>Business ' + activeBusiness + '</main></body></html>',
                { headers: { 'content-type': 'text/html; charset=utf-8' } },
            );
        }
        return new MockResponse('online');
    });

    await worker.dispatchMessage({ type: 'prime-pages', userId: '41', businessId: '17', routes: ['/dashboard'] });
    assert.ok((await worker.caches.keys()).includes('zazu-static-v37-user-41-business-17'));

    const switched = worker.dispatchFetch(new worker.Request('/business/switch', { method: 'POST' }));
    const switchResponse = await switched.response;
    assert.equal(switchResponse.status, 302);
    await switched.finished();

    const state = await worker.caches.open('zazu-static-v37');
    assert.equal(await state.match('/__zazu-active-context__'), null);
    assert.equal((await worker.caches.keys()).some(name => name.startsWith('zazu-static-v37-user-41-business-')), false);

    const nextPage = worker.dispatchFetch(new worker.Request('/dashboard', {
        method: 'GET',
        mode: 'navigate',
        headers: { accept: 'text/html' },
    }));
    const response = await nextPage.response;
    assert.equal(response.ok, true);
    await nextPage.finished();

    const names = await worker.caches.keys();
    assert.ok(names.includes('zazu-static-v37-user-41-business-29'));
    assert.equal(names.includes('zazu-static-v37-user-41-business-17'), false);
});


test('a private image response arriving after a business switch is not cached in the new workspace', async () => {
    const imageResponse = deferred();
    const imageFetchStarted = deferred();
    const worker = bootWorker(async (request) => {
        const url = new URL(request.url);
        if (url.pathname === '/media/business/logo') {
            imageFetchStarted.resolve();
            return imageResponse.promise;
        }
        if (url.pathname === '/dashboard') {
            return new MockResponse(
                '<!doctype html><html><body data-zazu-user-id="41" data-zazu-business-id="29"><main>Business 29</main></body></html>',
                { headers: { 'content-type': 'text/html; charset=utf-8' } },
            );
        }
        return new MockResponse('online');
    });

    await worker.dispatchMessage({
        type: 'prime-pages',
        userId: '41',
        businessId: '17',
        routes: [],
    });

    const imageRequest = worker.dispatchFetch(new worker.Request('/media/business/logo', {
        method: 'GET',
        destination: 'image',
    }));
    await imageFetchStarted.promise;

    await worker.dispatchMessage({
        type: 'prime-pages',
        userId: '41',
        businessId: '29',
        routes: ['/dashboard'],
    });

    imageResponse.resolve(new MockResponse('business-17-image', {
        headers: { 'content-type': 'image/png', 'cache-control': 'private, no-store' },
    }));

    assert.equal((await imageRequest.response).ok, true);
    await imageRequest.finished();

    const names = await worker.caches.keys();
    assert.ok(names.includes('zazu-static-v37-user-41-business-29'));
    const newBusinessCache = await worker.caches.open('zazu-static-v37-user-41-business-29');
    assert.equal(
        await newBusinessCache.match('/media/business/logo'),
        null,
        'a late response from the previous business must never enter the new business cache',
    );
    assert.equal(
        names.includes('zazu-static-v37-user-41-business-17'),
        false,
        'switching businesses must remove the previous private cache',
    );
});


test('an active user navigation cancels low-priority page priming', async () => {
    const primeStarted = deferred();
    let primeSignal = null;
    const worker = bootWorker(async (request, init = {}) => {
        const path = new URL(request.url).pathname;

        if (path === '/reports') {
            primeSignal = init.signal;
            primeStarted.resolve();

            return new Promise((resolve, reject) => {
                const abort = () => reject(new Error('Background page priming was cancelled by a navigation.'));
                if (init.signal.aborted) {
                    abort();
                    return;
                }
                init.signal.addEventListener('abort', abort, { once: true });
            });
        }

        if (path === '/setup/experience') {
            return new MockResponse(
                '<!doctype html><html><body data-zazu-user-id="41" data-zazu-business-id="17"><main>Experience setup</main></body></html>',
                { headers: { 'content-type': 'text/html; charset=utf-8' } },
            );
        }

        return new MockResponse('online');
    });

    const priming = worker.dispatchMessage({
        type: 'prime-pages',
        userId: '41',
        businessId: '17',
        routes: ['/reports'],
    });

    await primeStarted.promise;
    assert.ok(primeSignal);
    assert.equal(primeSignal.aborted, false);

    const navigation = worker.dispatchFetch(new worker.Request('/setup/experience', {
        method: 'GET',
        mode: 'navigate',
        headers: { accept: 'text/html' },
    }));

    const response = await navigation.response;
    assert.equal(response.ok, true, 'the requested page must win over background cache warming');
    assert.match(await response.text(), /Experience setup/);

    await Promise.all([priming, navigation.finished()]);
    assert.equal(primeSignal.aborted, true, 'navigation must cancel the in-flight page-prime request');
});


test('an installed app navigation serves the authenticated cached page without any network request when offline is known', async () => {
    let hostAvailable = true;
    const worker = bootWorker(async () => {
        if (!hostAvailable) throw new Error('Network must not be touched when the browser reports offline.');
        return new MockResponse(
            '<!doctype html><html><body data-zazu-user-id="41" data-zazu-business-id="17"><main>Cached dashboard</main></body></html>',
            { headers: { 'content-type': 'text/html; charset=utf-8' } },
        );
    });

    await worker.dispatchMessage({
        type: 'prime-pages',
        userId: '41',
        businessId: '17',
        routes: ['/dashboard'],
    });

    hostAvailable = false;
    worker.setOnline(false);
    const navigation = worker.dispatchFetch(new worker.Request('/dashboard', {
        method: 'GET',
        mode: 'navigate',
        headers: { accept: 'text/html' },
    }));

    const response = await navigation.response;
    assert.equal(response.ok, true);
    const html = await response.text();
    assert.match(html, /data-zazu-user-id="41"/);
    assert.match(html, /data-zazu-business-id="17"/);
    await navigation.finished();

    assert.equal(worker.fetchCalls.length, 1, 'only the initial online page-prime request is allowed');
    assert.equal(new URL(worker.fetchCalls[0].url).pathname, '/dashboard');
});