(() => {
    'use strict';

    const DB_NAME = 'zazu-client';
    const DB_VERSION = 1;
    const STATE = 'state';
    const QUEUE = 'mutations';
    let state = null;
    let syncing = false;
    let resolveReady;
    const ready = new Promise((resolve) => { resolveReady = resolve; });

    const blank = () => ({
        version: new Date().toISOString(),
        business: null,
        catalogue: [],
        customers: [],
        jobs: [],
        quotes: [],
        preparations: [],
        suppliers: [],
        purchase_orders: [],
        invoices: [],
        expenses: [],
        inventory_items: [],
        assets: [],
        costs: [],
        conflicts: [],
    });

    function openDb() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);
            request.onupgradeneeded = () => {
                const db = request.result;
                if (!db.objectStoreNames.contains(STATE)) db.createObjectStore(STATE);
                if (!db.objectStoreNames.contains(QUEUE)) {
                    const store = db.createObjectStore(QUEUE, { keyPath: 'id' });
                    store.createIndex('status', 'status');
                    store.createIndex('created_at', 'created_at');
                }
            };
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async function readState() {
        const db = await openDb();
        return new Promise((resolve, reject) => {
            const request = db.transaction(STATE).objectStore(STATE).get('current');
            request.onsuccess = () => resolve(request.result || null);
            request.onerror = () => reject(request.error);
        });
    }

    async function writeState() {
        const db = await openDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STATE, 'readwrite');
            tx.objectStore(STATE).put(state, 'current');
            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error);
        });
    }

    function api(path, options = {}) {
        const headers = new Headers(options.headers || {});
        headers.set('Accept', 'application/json');
        if (state?.sync?.token) headers.set('Authorization', 'Bearer ' + state.sync.token);
        return fetch(path, { ...options, headers });
    }

    function applyBootstrap(bootstrap) {
        if (!bootstrap) return;

        state.business = bootstrap.business || state.business;
        state.catalogue = (bootstrap.catalogue || []).map(x => ({
            ...x.record,
            local_id: x.local_id,
            id: x.server_id,
        }));

        state.suppliers = (bootstrap.suppliers || []).map(x => ({
            ...x.record,
            local_id: x.local_id,
            id: x.server_id,
        }));
        state.purchase_orders = (bootstrap.purchase_orders || []).map(x => ({
            ...x.record,
            local_id: x.local_id,
            id: x.server_id,
            items: (x.items || []).map(item => ({
                ...item.record,
                local_id: item.local_id,
                id: item.server_id,
            })),
        }));
        state.invoices = (bootstrap.invoices || []).map(x => ({
            ...x.record,
            local_id: x.local_id,
            id: x.server_id,
            payments: (x.payments || []).map(payment => ({
                ...payment.record,
                local_id: payment.local_id,
                id: payment.server_id,
            })),
        }));
        state.expenses = (bootstrap.expenses || []).map(x => ({
            ...x.record,
            local_id: x.local_id,
            id: x.server_id,
        }));
        state.inventory_items = (bootstrap.inventory_items || []).map(x => ({
            ...x.record,
            local_id: x.local_id,
            id: x.server_id,
            movements: (x.record?.movements || []).map(movement => ({
                ...(movement.record || {}),
                local_id: movement.local_id,
                id: movement.server_id,
                server_id: movement.server_id,
            })),
        }));
        state.assets = (bootstrap.assets || []).map(x => ({
            ...x.record,
            local_id: x.local_id,
            id: x.server_id,
            allocations: (x.record?.allocations || []).map(allocation => ({
                ...(allocation.record || {}),
                local_id: allocation.local_id,
                id: allocation.server_id,
                server_id: allocation.server_id,
            })),
        }));
        state.costs = (bootstrap.costs || []).map(x => ({
            ...x.record,
            local_id: x.local_id,
            id: x.server_id,
        }));

        const selected = bootstrap.selected || [];
        const targets = {
            customer: 'customers',
            job: 'jobs',
            quote: 'quotes',
            preparation: 'preparations',
        };

        for (const item of selected) {
            const target = targets[item.type];
            if (!target) continue;

            const record = {
                ...item.record,
                local_id: item.local_id,
                id: item.server_id,
            };
            const list = state[target];
            const index = list.findIndex(x => x.local_id === item.local_id || Number(x.id) === Number(item.server_id));
            if (index >= 0) list[index] = record;
            else list.push(record);
        }

        state.version = bootstrap.version || new Date().toISOString();
    }

    async function pending() {
        const db = await openDb();
        return new Promise((resolve, reject) => {
            const request = db.transaction(QUEUE).objectStore(QUEUE).index('status').getAll('pending');
            request.onsuccess = () => resolve((request.result || []).sort((a, b) => String(a.created_at).localeCompare(String(b.created_at))));
            request.onerror = () => reject(request.error);
        });
    }

    async function queueMutation(entityType, operation, payload) {
        await ready;
        const mutation = {
            id: crypto.randomUUID(),
            entity_type: entityType,
            entity_id: payload?.local_id || payload?.server_id || crypto.randomUUID(),
            operation,
            payload: payload || {},
            created_at: new Date().toISOString(),
            status: 'pending',
        };

        const db = await openDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(QUEUE, 'readwrite');
            tx.objectStore(QUEUE).put(mutation);
            tx.oncomplete = async () => {
                state.local_dirty = true;
                await writeState();
                resolve(mutation);
            };
            tx.onerror = () => reject(tx.error);
        });
    }

    async function push() {
        if (!navigator.onLine || !state?.sync?.token) return;
        const allMutations = await pending();
        if (!allMutations.length) return;

        // The server accepts at most 100 mutations per request. Keep the
        // client durable queue larger than that and drain it in ordered batches.
        const mutations = allMutations.slice(0, 100);

        const response = await api('/api/sync/push', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                mutations: mutations.map(m => ({
                    id: m.id,
                    entity_type: m.entity_type,
                    entity_id: m.entity_id,
                    operation: m.operation,
                    payload: m.payload,
                })),
            }),
        });

        if (!response.ok) return;

        const result = await response.json();
        const recorded = new Map((result.recorded || []).map(item => [item.id, item]));
        const db = await openDb();

        await new Promise((resolve, reject) => {
            const tx = db.transaction(QUEUE, 'readwrite');
            const store = tx.objectStore(QUEUE);

            for (const mutation of mutations) {
                const remote = recorded.get(mutation.id);
                if (!remote) continue;

                if (remote.status === 'applied' || remote.status === 'discarded') {
                    store.delete(mutation.id);
                } else {
                    store.put({
                        ...mutation,
                        server_sequence: remote.sequence,
                        server_status: remote.status,
                        last_error: remote.error || 'Server rejected this offline change',
                    });
                }
            }

            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error);
        });

        const identityTargets = {
            customer: 'customers',
            job: 'jobs',
            quote: 'quotes',
            preparation: 'preparations',
            supplier: 'suppliers',
            purchase_order: 'purchase_orders',
            invoice: 'invoices',
            expense: 'expenses',
            inventory_item: 'inventory_items',
            asset: 'assets',
            cost: 'costs',
        };

        for (const mutation of mutations) {
            const remote = recorded.get(mutation.id);
            const identity = remote?.identity;
            const target = identity ? identityTargets[identity.entity_type] : null;
            if (!target) continue;

            const list = state[target] || [];
            const localId = identity.local_id || mutation.payload?.local_id;
            const record = list.find(item => item.local_id === localId);
            if (record && remote.status === 'applied') {
                record.id = Number(identity.server_id);
                record.server_id = Number(identity.server_id);
                record.local_id = localId;
            }
        }

        state.local_dirty = (await pending()).length > 0;
        await writeState();
    }

    async function pull() {
        if (!navigator.onLine || !state?.sync?.token || state.local_dirty) return;

        for (let batch = 0; batch < 20; batch++) {
            const response = await api('/api/sync/pull?stream=business&limit=100');
            if (!response.ok) return;

            const result = await response.json();
            const mutations = result.mutations || [];
            if (!mutations.length) return;

            const bootstrap = await api('/api/sync/bootstrap');
            if (!bootstrap.ok) return;

            applyBootstrap(await bootstrap.json());
            await writeState();

            const highest = Math.max(...mutations.map(m => Number(m.sequence || 0)));
            const ack = await api('/api/sync/acknowledge', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ sequence: highest, stream: 'business' }),
            });

            if (!ack.ok) return;
        }
    }

    async function sync() {
        if (syncing || !navigator.onLine || !state?.sync?.token) return;
        syncing = true;
        try {
            for (let batch = 0; batch < 20; batch++) {
                const before = (await pending()).length;
                if (!before) break;
                await push();
                const after = (await pending()).length;
                if (after >= before) break;
            }

            await pull();
            await refreshBootstrap();
        } finally {
            syncing = false;
        }
    }

    async function refreshBootstrap() {
        if (!navigator.onLine || !state?.sync?.token || state.local_dirty) return;
        const response = await api('/api/sync/bootstrap');
        if (!response.ok) return;
        applyBootstrap(await response.json());
        await writeState();
    }

    async function pair(pairingCode) {
        if (!pairingCode) throw new Error('A pairing code is required.');

        const response = await fetch('/api/sync/provision', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                pairing_code: String(pairingCode).replace(/\D/g, '').slice(0, 6),
                device_name: 'Zazu phone',
                device_type: 'phone',
            }),
        });

        if (!response.ok) throw new Error('The Zazu phone pairing code is invalid or expired.');

        const result = await response.json();
        state = { ...blank(), sync: { token: result.token, device: result.device }, local_dirty: false };
        applyBootstrap(result.bootstrap);
        await writeState();
        return result.device;
    }

    async function boot() {
        state = (await readState()) || blank();

        if (state.sync?.token && navigator.onLine) {
            await sync();
        }

        resolveReady(state);
        window.dispatchEvent(new CustomEvent('zazu:offline-ready', { detail: api }));
    }

    window.ZazuOffline = {
        getState: () => state,
        isPaired: () => Boolean(state?.sync?.token),
        queueMutation,
        pair,
        sync,
        refreshBootstrap,
        persist: writeState,
        pending,
        ready,
    };

    window.addEventListener('online', sync);
    boot().catch(() => {});
})();
