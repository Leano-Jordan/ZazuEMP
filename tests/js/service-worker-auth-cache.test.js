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

    static redirect(url, status = 302) {
        return new MockResponse('', { ok: false, status, headers: { location: String(url) } });
    }

    constructor(body = '', options = {}) {
        this.body = body;
        this.ok = options.ok ?? true;
        this.status = options.status ?? (this.ok ? 200 : 500);
        this.redirected = options.redirected ?? false;
        this.url = options.url || '';
        this.headerValues = options.headers || {};
        this.headers = { get: (name) => this.headerValues[name.toLowerCase()] ?? null };
    }

    clone() {
        return new MockResponse(this.body, {
            ok: this.ok,
            status: this.status,
            redirected: this.redirected,
            url: this.url,
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
            keys: async () => [...entries.keys()].map((url) => new MockRequest(url)),
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
        const messages = [];
        listeners.get('message')({
            data,
            ports: [{ postMessage: (message) => messages.push(message), close: () => {} }],
            waitUntil: (promise) => pending.push(Promise.resolve(promise)),
        });
        return Promise.all(pending).then(() => messages);
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

    function dispatchActivate() {
        const pending = [];
        listeners.get('activate')({
            waitUntil: (promise) => pending.push(Promise.resolve(promise)),
        });
        return Promise.all(pending);
    }

    return { caches, dispatchActivate, dispatchFetch, dispatchMessage, fetchCalls, Request: MockRequest, Response: MockResponse, setOnline: (online) => { self.navigator.onLine = online; } };
}

test('service worker upgrades preserve authenticated offline pages and active business context', async () => {
    const worker = bootWorker();
    const oldStatic = await worker.caches.open('zazu-static-v49');
    const oldUser = await worker.caches.open('zazu-static-v49-user-41-business-17');
    await oldStatic.put('/__zazu-active-context__', new MockResponse(JSON.stringify({
        user_id: '41',
        business_id: '17',
    })));
    await oldStatic.put('/build/old-dashboard.css', new MockResponse('cached-css'));
    await oldUser.put('/dashboard', new MockResponse('<main>Saved dashboard</main>'));
    await oldUser.put('/media/business/logo', new MockResponse('private-logo'));

    await worker.dispatchActivate();

    const names = await worker.caches.keys();
    assert.ok(names.includes('zazu-static-v61'));
    assert.ok(names.includes('zazu-static-v61-user-41-business-17'));
    assert.equal(names.includes('zazu-static-v49'), false);
    assert.equal(names.includes('zazu-static-v49-user-41-business-17'), false);

    const currentStatic = await worker.caches.open('zazu-static-v61');
    const currentUser = await worker.caches.open('zazu-static-v61-user-41-business-17');
    assert.ok(await currentStatic.match('/__zazu-active-context__'));
    assert.equal(await (await currentStatic.match('/build/old-dashboard.css')).text(), 'cached-css');
    assert.equal(await currentStatic.match('/dashboard'), null, 'authenticated pages must never migrate into the shared cache');
    assert.equal(await currentStatic.match('/media/business/logo'), null, 'private media must never migrate into the shared cache');
    assert.equal(await (await currentUser.match('/dashboard')).text(), '<main>Saved dashboard</main>');
    assert.equal(await (await currentUser.match('/media/business/logo')).text(), 'private-logo');
});

test('redirected authenticated navigations preserve the public sign-in URL', async () => {
    const loginUrl = ORIGIN + '/?auth=login';
    const worker = bootWorker(async () => new MockResponse('<main>Sign in</main>', {
        ok: true,
        status: 200,
        redirected: true,
        url: loginUrl,
        headers: { 'content-type': 'text/html' },
    }));

    const navigation = worker.dispatchFetch(new worker.Request('/dashboard', {
        method: 'GET',
        mode: 'navigate',
        headers: { accept: 'text/html' },
    }));
    const response = await navigation.response;

    assert.equal(response.status, 302);
    assert.equal(response.headers.get('location'), loginUrl);
    await navigation.finished();
    assert.deepEqual(await worker.caches.keys(), [], 'a redirected public page must not create or populate an authenticated cache');
});

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
    assert.ok(names.includes('zazu-static-v61-user-41-business-17'));
    assert.equal(names.includes('zazu-static-v61'), true);
    const privateCache = await worker.caches.open('zazu-static-v61-user-41-business-17');
    assert.ok(await privateCache.match('/media/business/logo'));
    const staticCache = await worker.caches.open('zazu-static-v61');
    assert.equal(await staticCache.match('/media/business/logo'), null, 'private media must not enter the shared cache');

    hostAvailable = false;
    const offline = worker.dispatchFetch(imageRequest());
    const offlineResponse = await offline.response;
    assert.equal(offlineResponse.ok, true);
    assert.equal(await offlineResponse.text(), 'image-bytes');
    await offline.finished();
});

test('cached offline pages remain prepared when a later prime attempt hits a transient outage', async () => {
    let hostAvailable = true;
    const worker = bootWorker(async (request) => {
        if (!hostAvailable) throw new Error('Temporary host outage');
        const path = new URL(request.url).pathname;
        return new MockResponse(
            '<body data-zazu-user-id="41" data-zazu-business-id="17"><main>' + path + '</main></body>',
            { headers: { 'content-type': 'text/html; charset=utf-8' } },
        );
    });
    const routes = ['/dashboard', '/customers', '/customers/create', '/work', '/work/create'];

    const firstAttempt = await worker.dispatchMessage({
        type: 'prime-pages',
        userId: '41',
        businessId: '17',
        routes,
    });
    assert.equal(firstAttempt.length, 1);
    assert.equal(firstAttempt[0].type, 'prime-pages-complete');
    assert.equal(firstAttempt[0].success, true);

    hostAvailable = false;
    const retryAttempt = await worker.dispatchMessage({
        type: 'prime-pages',
        userId: '41',
        businessId: '17',
        routes,
    });

    assert.equal(retryAttempt.length, 1);
    assert.equal(retryAttempt[0].type, 'prime-pages-complete');
    assert.equal(
        retryAttempt[0].success,
        true,
        'existing authenticated offline pages must satisfy readiness during a temporary outage',
    );
});

test('page priming rejects cross-origin and API routes before making credentialed requests', async () => {
    const worker = bootWorker(async (request) => {
        const url = new URL(request.url);
        if (url.origin !== ORIGIN) {
            throw new Error('Page priming must never request another origin.');
        }
        if (url.pathname === '/dashboard') {
            return new MockResponse(
                '<body data-zazu-user-id="41" data-zazu-business-id="17"><main>Dashboard</main></body>',
                { headers: { 'content-type': 'text/html; charset=utf-8' } },
            );
        }
        return new MockResponse('unexpected route');
    });

    await worker.dispatchMessage({
        type: 'prime-pages',
        userId: '41',
        businessId: '17',
        routes: [
            '/dashboard',
            '//attacker.example/collect',
            '/\\\\attacker.example/collect',
            '/api/sync/bootstrap',
            '/api',
        ],
    });

    assert.deepEqual(
        worker.fetchCalls.map((request) => new URL(request.url).pathname),
        ['/dashboard'],
        'only the same-origin non-API route should be fetched',
    );
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

    const privateCache = await worker.caches.open('zazu-static-v61-user-41-business-17');
    assert.ok(await privateCache.match('/media/profile/customer/12'));
    assert.ok(await privateCache.match('/media/business/dashboard?v=9'));
    const staticCache = await worker.caches.open('zazu-static-v61');
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

    const staticCache = await worker.caches.open('zazu-static-v61');
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
    const cache = await worker.caches.open('zazu-static-v61');
    const readStarted = deferred();
    const releaseRead = deferred();

    await cache.put('/__zazu-active-context__', new MockResponse(() => {
        readStarted.resolve();
        return releaseRead.promise;
    }));
    const privateCache = await worker.caches.open('zazu-static-v61-user-41-business-17');
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
        (await worker.caches.keys()).some((name) => name.startsWith('zazu-static-v61-user-')),
        false,
        'logout must leave no cached authenticated page',
    );
    assert.equal(await cache.match('/__zazu-active-context__'), null, 'logout must leave no persisted active identity');
});

test('a fresh cache prime after logout does not join an invalidated task', async () => {
    const staleRequestStarted = deferred();
    const staleResponse = deferred();
    const freshRequestStarted = deferred();
    const worker = bootWorker(async (request) => {
        const path = new URL(request.url).pathname;
        if (path === '/reports') {
            staleRequestStarted.resolve();
            return staleResponse.promise;
        }
        if (path === '/dashboard') {
            freshRequestStarted.resolve();
            return new MockResponse(
                '<!doctype html><html><body data-zazu-user-id="41" data-zazu-business-id="17"><main>Fresh dashboard</main></body></html>',
                { headers: { 'content-type': 'text/html; charset=utf-8' } },
            );
        }
        return new MockResponse('online');
    });

    const stalePrime = worker.dispatchMessage({
        type: 'prime-pages',
        userId: '41',
        businessId: '17',
        routes: ['/reports'],
    });

    await staleRequestStarted.promise;
    await worker.dispatchMessage({ type: 'clear-user' });

    const freshPrime = worker.dispatchMessage({
        type: 'prime-pages',
        userId: '41',
        businessId: '17',
        routes: ['/dashboard'],
    });

    await freshRequestStarted.promise;
    staleResponse.resolve(new MockResponse(
        '<!doctype html><html><body data-zazu-user-id="41" data-zazu-business-id="17"><main>Stale reports</main></body></html>',
        { headers: { 'content-type': 'text/html; charset=utf-8' } },
    ));

    await Promise.all([stalePrime, freshPrime]);

    const privateCache = await worker.caches.open('zazu-static-v61-user-41-business-17');
    assert.ok(await privateCache.match('/dashboard'));
    assert.equal(await privateCache.match('/reports'), null, 'the invalidated task must not repopulate stale routes');
});

test('a failed logout request preserves the authenticated page cache and active identity', async () => {
    const worker = bootWorker(async (request) => {
        if (new URL(request.url).pathname === '/logout') throw new Error('Server is unreachable');
        return new MockResponse('online');
    });
    const staticCache = await worker.caches.open('zazu-static-v61');
    await staticCache.put('/__zazu-active-context__', new MockResponse(JSON.stringify({ user_id: '41', business_id: '17' })));
    const privateCache = await worker.caches.open('zazu-static-v61-user-41-business-17');
    await privateCache.put('/dashboard', new MockResponse('<main>private dashboard</main>'));

    const logout = worker.dispatchFetch(new worker.Request('/logout', { method: 'POST' }));
    await assert.rejects(logout.response, /Server is unreachable/);
    await logout.finished();

    assert.ok((await worker.caches.keys()).includes('zazu-static-v61-user-41-business-17'));
    assert.ok(await staticCache.match('/__zazu-active-context__'));
});

test('an authenticated HTML response finishing after logout cannot repopulate the private page cache', async () => {
    const networkResponse = deferred();
    const networkStarted = deferred();
    const worker = bootWorker(async (request) => {
        if (new URL(request.url).pathname === '/logout') {
            return new MockResponse('redirect', { redirected: true });
        }
        networkStarted.resolve();
        return networkResponse.promise;
    });

    const staticCache = await worker.caches.open('zazu-static-v61');
    await staticCache.put('/__zazu-active-context__', new MockResponse(JSON.stringify({ user_id: '41', business_id: '17' })));
    const privateCache = await worker.caches.open('zazu-static-v61-user-41-business-17');
    await privateCache.put('/dashboard', new MockResponse('<main>private dashboard</main>'));

    const navigation = worker.dispatchFetch(new worker.Request('/dashboard', {
        method: 'GET',
        mode: 'navigate',
        headers: { accept: 'text/html' },
    }));

    await networkStarted.promise;
    const logout = worker.dispatchFetch(new worker.Request('/logout', { method: 'POST' }));
    const logoutResponse = await logout.response;
    assert.equal(logoutResponse.redirected, true, 'the server must confirm the logout redirect');
    await logout.finished();
    networkResponse.resolve(new MockResponse('<main data-zazu-user-id="41" data-zazu-business-id="17">late response</main>', {
        headers: { 'content-type': 'text/html' },
    }));

    const response = await navigation.response;
    assert.equal(response.ok, true, 'the online navigation response should remain unaffected');
    await navigation.finished();

    assert.equal(
        (await worker.caches.keys()).some((name) => name.startsWith('zazu-static-v61-user-')),
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

    const state = await worker.caches.open('zazu-static-v61');
    assert.equal(await state.match('/__zazu-active-context__'), null);
    assert.equal(
        (await worker.caches.keys()).some((name) => name.startsWith('zazu-static-v61-user-')),
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
    assert.ok((await worker.caches.keys()).includes('zazu-static-v61-user-41-business-17'));

    const switched = worker.dispatchFetch(new worker.Request('/business/switch', { method: 'POST' }));
    const switchResponse = await switched.response;
    assert.equal(switchResponse.status, 302);
    await switched.finished();

    const state = await worker.caches.open('zazu-static-v61');
    assert.equal(await state.match('/__zazu-active-context__'), null);
    assert.equal((await worker.caches.keys()).some(name => name.startsWith('zazu-static-v61-user-41-business-')), false);

    const nextPage = worker.dispatchFetch(new worker.Request('/dashboard', {
        method: 'GET',
        mode: 'navigate',
        headers: { accept: 'text/html' },
    }));
    const response = await nextPage.response;
    assert.equal(response.ok, true);
    await nextPage.finished();

    const names = await worker.caches.keys();
    assert.ok(names.includes('zazu-static-v61-user-41-business-29'));
    assert.equal(names.includes('zazu-static-v61-user-41-business-17'), false);
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
    assert.ok(names.includes('zazu-static-v61-user-41-business-29'));
    const newBusinessCache = await worker.caches.open('zazu-static-v61-user-41-business-29');
    assert.equal(
        await newBusinessCache.match('/media/business/logo'),
        null,
        'a late response from the previous business must never enter the new business cache',
    );
    assert.equal(
        names.includes('zazu-static-v61-user-41-business-17'),
        false,
        'switching businesses must remove the previous private cache',
    );
});


test('an active user navigation does not cancel low-priority page priming', async () => {
    const primeStarted = deferred();
    const primeResponse = deferred();
    let primeSignal = null;
    const worker = bootWorker(async (request, init = {}) => {
        const path = new URL(request.url).pathname;

        if (path === '/reports') {
            primeSignal = init.signal;
            primeStarted.resolve();
            return primeResponse.promise;
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

    primeResponse.resolve(new MockResponse(
        '<!doctype html><html><body data-zazu-user-id="41" data-zazu-business-id="17"><main>Reports</main></body></html>',
        { headers: { 'content-type': 'text/html; charset=utf-8' } },
    ));

    await Promise.all([priming, navigation.finished()]);
    assert.equal(primeSignal.aborted, false, 'navigation must leave in-flight page priming intact');
    const privateCache = await worker.caches.open('zazu-static-v61-user-41-business-17');
    assert.ok(await privateCache.match('/reports'), 'the in-flight route must finish caching after navigation');
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

test('failed top-level navigation falls back to the local dashboard instead of stale route HTML', async () => {
    const worker = bootWorker(async () => {
        throw new Error('Host unavailable during navigation.');
    });

    const staticCache = await worker.caches.open('zazu-static-v61');
    await staticCache.put('/__zazu-active-context__', new MockResponse(JSON.stringify({
        user_id: '41',
        business_id: '17',
    })));

    const businessCache = await worker.caches.open('zazu-static-v61-user-41-business-17');
    await businessCache.put('/dashboard', new MockResponse(
        '<!doctype html><html><body data-zazu-user-id="41" data-zazu-business-id="17"><main>Local dashboard shell</main></body></html>',
    ));
    await businessCache.put('/customers', new MockResponse(
        '<!doctype html><html><body><main>Stale server-rendered customer snapshot</main></body></html>',
    ));

    const navigation = worker.dispatchFetch(new worker.Request('/customers', {
        method: 'GET',
        mode: 'navigate',
        headers: { accept: 'text/html' },
    }));

    const response = await navigation.response;
    assert.equal(response.ok, true);
    const html = await response.text();
    assert.match(html, /Local dashboard shell/);
    assert.doesNotMatch(html, /Stale server-rendered customer snapshot/);
    await navigation.finished();
});


test('offline forms and cached Work detail preserve their exact route while other routes prefer the dashboard', async () => {
    const worker = bootWorker(async () => { throw new Error('Host unavailable.'); });
    const staticCache = await worker.caches.open('zazu-static-v61');
    await staticCache.put('/__zazu-active-context__', new MockResponse(JSON.stringify({
        user_id: '41',
        business_id: '17',
    })));
    const businessCache = await worker.caches.open('zazu-static-v61-user-41-business-17');
    await businessCache.put('/dashboard', new MockResponse('<main>Offline dashboard</main>'));

    const preservedRoutes = [
        ['/customers/create', '<main>Offline customer form</main>'],
        ['/work/create', '<main>Offline Work form</main>'],
        ['/finance/invoices/create', '<main>Offline invoice form</main>'],
        ['/work/42', '<main>Offline Work detail with attachments</main>'],
    ];

    for (const [path, html] of preservedRoutes) {
        await businessCache.put(path, new MockResponse(html));
        const navigation = worker.dispatchFetch(new worker.Request(path, {
            method: 'GET',
            mode: 'navigate',
            headers: { accept: 'text/html' },
        }));
        const response = await navigation.response;
        assert.equal(response.ok, true, path + ' should load from its exact business cache entry');
        assert.equal(await response.text(), html);
        await navigation.finished();
    }

    await businessCache.put('/work/43', new MockResponse('<main>Stale Work detail</main>'));
    const staleRoute = worker.dispatchFetch(new worker.Request('/work/43?stale=1', {
        method: 'GET',
        mode: 'navigate',
        headers: { accept: 'text/html' },
    }));
    const staleResponse = await staleRoute.response;
    assert.match(await staleResponse.text(), /Offline dashboard/, 'query-variant Work routes must use the safe shell');
    await staleRoute.finished();

    const directory = worker.dispatchFetch(new worker.Request('/work', {
        method: 'GET',
        mode: 'navigate',
        headers: { accept: 'text/html' },
    }));
    const directoryResponse = await directory.response;
    assert.match(await directoryResponse.text(), /Offline dashboard/);
    await directory.finished();
});


test('a hanging host with Wi-Fi still connected falls back quickly to the local dashboard shell', async () => {
    const worker = bootWorker(async request => {
        if (new URL(request.url).pathname === '/dashboard') {
            return new MockResponse('<!doctype html><html><body data-zazu-user-id="41" data-zazu-business-id="17"><main>Local dashboard shell</main></body></html>', {
                headers: { 'content-type': 'text/html; charset=utf-8' },
            });
        }
        return new Promise(() => {});
    }, {}, true);
    await worker.dispatchMessage({
        type: 'prime-pages',
        userId: '41',
        businessId: '17',
        routes: ['/dashboard'],
    });

    const businessCache = await worker.caches.open('zazu-static-v61-user-41-business-17');
    await businessCache.put('/customers', new MockResponse('<main>Stale customer snapshot</main>'));

    const startedAt = Date.now();
    const navigation = worker.dispatchFetch(new worker.Request('/customers', {
        method: 'GET',
        mode: 'navigate',
        headers: { accept: 'text/html' },
    }));

    const response = await navigation.response;
    const elapsed = Date.now() - startedAt;
    assert.equal(response.ok, true);
    assert.match(await response.text(), /data-zazu-user-id="41"/);
    assert.doesNotMatch(await response.text(), /Stale customer snapshot/);
    assert.ok(elapsed < 3000, 'the worker must not leave the installed app waiting on a dead host');
    await navigation.finished();
});
