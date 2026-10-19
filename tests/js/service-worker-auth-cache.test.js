import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';
import vm from 'node:vm';

const ORIGIN = 'https://zazu.test';
const workerSource = await readFile(new URL('../../public/sw.js', import.meta.url), 'utf8');

function deferred() {
    let resolve;
    let reject;
    const promise = new Promise((resolvePromise, rejectPromise) => {
        resolve = resolvePromise;
        reject = rejectPromise;
    });
    return { promise, resolve, reject };
}

function bootWorker(fetchImplementation = async () => new MockResponse('online'), cacheHooks = {}) {
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

    class MockResponse {
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

    return { caches, dispatchFetch, dispatchMessage, fetchCalls, Request: MockRequest, Response: MockResponse };
}

test('a persisted identity read finishing after logout cannot restore the identity or prime private pages', async () => {
    const worker = bootWorker();
    const cache = await worker.caches.open('zazu-static-v23');
    const readStarted = deferred();
    const releaseRead = deferred();

    await cache.put('/__zazu-active-user__', new worker.Response(() => {
        readStarted.resolve();
        return releaseRead.promise;
    }));
    const privateCache = await worker.caches.open('zazu-static-v23-user-41');
    await privateCache.put('/dashboard', new worker.Response('private dashboard'));

    const priming = worker.dispatchMessage({
        type: 'prime-pages',
        userId: '41',
        routes: ['/dashboard'],
    });

    await readStarted.promise;
    await worker.dispatchMessage({ type: 'clear-user' });
    releaseRead.resolve('41');
    await priming;

    assert.equal(worker.fetchCalls.length, 0, 'stale identity must not trigger page priming');
    assert.equal(
        (await worker.caches.keys()).some((name) => name.startsWith('zazu-static-v23-user-')),
        false,
        'logout must leave no cached authenticated page',
    );
    assert.equal(await cache.match('/__zazu-active-user__'), null, 'logout must leave no persisted active identity');
});

test('an authenticated HTML response finishing after logout cannot repopulate the private page cache', async () => {
    const networkResponse = deferred();
    const networkStarted = deferred();
    const worker = bootWorker(async () => {
        networkStarted.resolve();
        return networkResponse.promise;
    });

    const staticCache = await worker.caches.open('zazu-static-v23');
    await staticCache.put('/__zazu-active-user__', new worker.Response('41'));
    const privateCache = await worker.caches.open('zazu-static-v23-user-41');
    await privateCache.put('/dashboard', new worker.Response('<main>private dashboard</main>'));

    const navigation = worker.dispatchFetch(new worker.Request('/dashboard', {
        method: 'GET',
        mode: 'navigate',
        headers: { accept: 'text/html' },
    }));

    await networkStarted.promise;
    await worker.dispatchFetch(new worker.Request('/logout', { method: 'POST' })).finished();
    networkResponse.resolve(new worker.Response('<main data-zazu-user-id="41">late response</main>', {
        headers: { 'content-type': 'text/html' },
    }));

    const response = await navigation.response;
    assert.equal(response.ok, true, 'the online navigation response should remain unaffected');
    await navigation.finished();

    assert.equal(
        (await worker.caches.keys()).some((name) => name.startsWith('zazu-static-v23-user-')),
        false,
        'a late response must not recreate the logged-out user cache',
    );
    assert.equal(await staticCache.match('/__zazu-active-user__'), null, 'the persisted identity must remain cleared');
});


test('an identity write already in flight cannot finish after logout cleanup', async () => {
    const putStarted = deferred();
    const releasePut = deferred();
    const worker = bootWorker(undefined, {
        beforePut: async ({ name, key }) => {
            if (name === 'zazu-static-v23' && key === new URL('/__zazu-active-user__', ORIGIN).href) {
                putStarted.resolve();
                await releasePut.promise;
            }
        },
    });

    const priming = worker.dispatchMessage({
        type: 'prime-pages',
        userId: '41',
        routes: [],
    });

    await putStarted.promise;
    const logout = worker.dispatchMessage({ type: 'clear-user' });
    releasePut.resolve();

    await Promise.all([priming, logout]);

    const state = await worker.caches.open('zazu-static-v23');
    assert.equal(await state.match('/__zazu-active-user__'), null, 'logout must win over any identity write already in flight');
    assert.equal(
        (await worker.caches.keys()).some((name) => name.startsWith('zazu-static-v23-user-')),
        false,
        'the stale identity operation must not leave a private page cache',
    );
});
