(() => {
    'use strict';

    const DB_NAME = 'zazu-client';
    const DB_VERSION = 1;
    const STATE = 'state';
    const QUEUE = 'mutations';
    let state = null;
    let syncing = false;
    let activeSyncController = null;
    let storageAvailable = true;
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

    let dbPromise = null;
    let sessionCleared = false;
    let sessionChannel = null;

    try {
        if ('BroadcastChannel' in window) {
            sessionChannel = new BroadcastChannel('zazu-offline-session');
        }
    } catch {
        // Cross-tab signalling is best-effort; IndexedDB transactions remain authoritative.
    }

    function openDb() {
        if (dbPromise) return dbPromise;

        let opening;
        opening = new Promise((resolve, reject) => {
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
            request.onsuccess = () => {
                const db = request.result;
                db.onversionchange = () => {
                    db.close();
                    if (dbPromise === opening) dbPromise = null;
                };
                resolve(db);
            };
            request.onerror = () => reject(request.error || new Error('Could not open local offline storage.'));
            request.onblocked = () => reject(new Error('Local offline storage is blocked by another open tab.'));
        });

        dbPromise = opening;
        opening.catch(() => {
            if (dbPromise === opening) dbPromise = null;
        });
        return opening;
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
            tx.onerror = () => reject(tx.error || new Error('Local offline state write failed.'));
            tx.onabort = () => reject(tx.error || new Error('Local offline state write was aborted.'));
        });
    }

    function api(path, options = {}) {
        const headers = new Headers(options.headers || {});
        headers.set('Accept', 'application/json');
        if (state?.sync?.token) headers.set('Authorization', 'Bearer ' + state.sync.token);
        return fetch(path, {
            ...options,
            headers,
            signal: options.signal ?? activeSyncController?.signal,
        });
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
    async function allMutations() {
        const db = await openDb();
        return new Promise((resolve, reject) => {
            const request = db.transaction(QUEUE).objectStore(QUEUE).getAll();
            request.onsuccess = () => resolve(request.result || []);
            request.onerror = () => reject(request.error || new Error('Offline queue inspection failed.'));
        });
    }

    async function assessLogout() {
        await ready;

        if (!storageAvailable) {
            return { inspectionFailed: true, total: null, pendingCount: null, failedCount: null, conflictCount: null };
        }

        try {
            const records = await allMutations();
            const pendingCount = records.filter(item => item.status === 'pending').length;
            const failedCount = records.length - pendingCount;
            const conflictCount = (state?.conflicts || []).filter(item => item.status === 'open').length;

            return {
                inspectionFailed: false,
                total: records.length,
                pendingCount,
                failedCount,
                conflictCount,
                localDirty: Boolean(state?.local_dirty),
            };
        } catch {
            return { inspectionFailed: true, total: null, pendingCount: null, failedCount: null, conflictCount: null };
        }
    }

    async function clearStoredWorkspace(broadcast = true) {
        const wasCleared = sessionCleared;
        sessionCleared = true;
        activeSyncController?.abort();

        try {
            const db = await openDb();
            await new Promise((resolve, reject) => {
                const tx = db.transaction([STATE, QUEUE], 'readwrite');
                tx.objectStore(STATE).clear();
                tx.objectStore(QUEUE).clear();
                tx.oncomplete = resolve;
                tx.onerror = () => reject(tx.error || new Error('Local offline session cleanup failed.'));
                tx.onabort = () => reject(tx.error || new Error('Local offline session cleanup was aborted.'));
            });

            state = blank();
            storageAvailable = true;
            if (broadcast) sessionChannel?.postMessage({ type: 'clear-session' });
        } catch (error) {
            sessionCleared = wasCleared;
            throw error;
        }
    }

    async function revokeDeviceToken(token) {
        if (!token) return { ok: true, reason: 'not_paired' };
        if (!navigator.onLine) return { ok: false, reason: 'offline' };

        const controller = new AbortController();
        const timeout = window.setTimeout(() => controller.abort(), 1800);

        try {
            const response = await fetch('/api/sync/revoke', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token,
                },
                cache: 'no-store',
                signal: controller.signal,
            });

            return response.ok
                ? { ok: true, reason: null }
                : { ok: false, reason: 'server_rejected' };
        } catch {
            return { ok: false, reason: controller.signal.aborted ? 'timeout' : 'unreachable' };
        } finally {
            window.clearTimeout(timeout);
        }
    }

    async function clearLocalSession() {
        await ready;
        const token = state?.sync?.token || null;
        await clearStoredWorkspace(true);

        // Local logout must succeed even without the server. Remote revocation
        // is bounded and its result is returned so the UI can warn accurately.
        return revokeDeviceToken(token);
    }

    sessionChannel?.addEventListener('message', event => {
        if (event.data?.type !== 'clear-session') return;

        sessionCleared = true;
        state = blank();
        clearStoredWorkspace(false).catch(() => {
            storageAvailable = false;
        });
    });


    async function queueMutation(entityType, operation, payload) {
        await ready;
        if (sessionCleared) {
            throw new Error('This device session has been signed out. Sign in and pair the device again before making offline changes.');
        }
        if (!storageAvailable) {
            throw new Error('Local offline storage is unavailable; this change was not saved.');
        }
        const mutation = {
            id: crypto.randomUUID(),
            entity_type: entityType,
            entity_id: payload?.local_id || payload?.server_id || crypto.randomUUID(),
            operation,
            payload: payload || {},
            created_at: new Date().toISOString(),
            status: 'pending',
        };

        let db;
        try {
            db = await openDb();
        } catch (error) {
            storageAvailable = false;
            throw new Error('Local offline storage is unavailable; this change was not saved.');
        }

        return new Promise((resolve, reject) => {
            let settled = false;
            const fail = (error) => {
                if (settled) return;
                settled = true;
                reject(error);
            };
            const tx = db.transaction(QUEUE, 'readwrite');
            tx.objectStore(QUEUE).put(mutation);
            tx.oncomplete = async () => {
                try {
                    state.local_dirty = true;
                    await writeState();
                    if (!settled) {
                        settled = true;
                        resolve(mutation);
                    }
                } catch (error) {
                    storageAvailable = false;
                    fail(new Error('Local offline storage failed while saving; this change may not be safely queued.'));
                }
            };
            tx.onerror = () => fail(tx.error || new Error('Local offline queue write failed.'));
            tx.onabort = () => fail(tx.error || new Error('Local offline queue write was aborted.'));
        });
    }

    async function push() {
        if (sessionCleared || !navigator.onLine || !state?.sync?.token) return;
        const allMutations = await pending();
        if (sessionCleared || !state?.sync?.token || !allMutations.length) return;

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
        if (sessionCleared || !state?.sync?.token) return;
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
                } else if (remote.error) {
                    // A domain rejection is not a transient network failure.
                    // Keep the failed mutation for inspection/reconciliation, but
                    // stop treating it as dirty work or it will block pulls forever.
                    store.put({
                        ...mutation,
                        status: 'failed',
                        server_sequence: remote.sequence,
                        server_status: remote.status,
                        conflict_id: remote.conflict_id || null,
                        last_error: remote.error,
                    });

                    if (remote.conflict_id) {
                        state.conflicts = state.conflicts || [];
                        state.conflicts = [
                            ...state.conflicts.filter(item => item.id !== remote.conflict_id),
                            {
                                id: remote.conflict_id,
                                mutation_id: mutation.id,
                                entity_type: mutation.entity_type,
                                entity_id: mutation.entity_id,
                                message: remote.error,
                                status: 'open',
                            },
                        ];
                    }
                } else {
                    store.put({
                        ...mutation,
                        server_sequence: remote.sequence,
                        server_status: remote.status,
                        last_error: remote.error || 'The server did not confirm this offline change',
                    });
                }
            }

            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error || new Error('Offline queue acknowledgement failed.'));
            tx.onabort = () => reject(tx.error || new Error('Offline queue acknowledgement was aborted.'));
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
        if (sessionCleared || !state?.sync?.token) return;
        await writeState();
    }

    async function pull() {
        if (sessionCleared || !navigator.onLine || !state?.sync?.token || state.local_dirty) return;

        for (let batch = 0; batch < 20; batch++) {
            if (sessionCleared || !state?.sync?.token) return;
            const response = await api('/api/sync/pull?stream=business&limit=100');
            if (!response.ok || sessionCleared || !state?.sync?.token) return;

            const result = await response.json();
            if (sessionCleared || !state?.sync?.token) return;
            const mutations = result.mutations || [];
            if (!mutations.length) return;

            const bootstrap = await api('/api/sync/bootstrap');
            if (!bootstrap.ok || sessionCleared || !state?.sync?.token) return;

            const snapshot = await bootstrap.json();
            if (sessionCleared || !state?.sync?.token) return;
            applyBootstrap(snapshot);
            await writeState();

            const highest = Math.max(...mutations.map(m => Number(m.sequence || 0)));
            if (sessionCleared || !state?.sync?.token) return;
            const ack = await api('/api/sync/acknowledge', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ sequence: highest, stream: 'business' }),
            });

            if (!ack.ok) return;
        }
    }

    async function sync() {
        if (syncing || sessionCleared || !navigator.onLine || !state?.sync?.token) return;
        syncing = true;
        const controller = new AbortController();
        activeSyncController = controller;
        try {
            for (let batch = 0; batch < 20; batch++) {
                if (sessionCleared || !state?.sync?.token) return;
                const before = (await pending()).length;
                if (!before || sessionCleared || !state?.sync?.token) break;
                await push();
                if (sessionCleared || !state?.sync?.token) return;
                const after = (await pending()).length;
                if (after >= before) break;
            }

            if (sessionCleared || !state?.sync?.token) return;
            await pull();
            if (sessionCleared || !state?.sync?.token) return;
            await refreshBootstrap();
        } finally {
            if (activeSyncController === controller) activeSyncController = null;
            syncing = false;
        }
    }

    async function refreshBootstrap() {
        if (sessionCleared || !navigator.onLine || !state?.sync?.token || state.local_dirty) return;
        const response = await api('/api/sync/bootstrap');
        if (!response.ok || sessionCleared || !state?.sync?.token) return;
        const bootstrap = await response.json();
        if (sessionCleared || !state?.sync?.token) return;
        applyBootstrap(bootstrap);
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
        sessionCleared = false;
        state = { ...blank(), sync: { token: result.token, device: result.device }, local_dirty: false };
        applyBootstrap(result.bootstrap);
        await writeState();
        return result.device;
    }

    async function boot() {
        try {
            state = (await readState()) || blank();
        } catch (error) {
            // A storage failure must not leave every caller waiting on ready.
            // Resolve startup, but refuse local mutations because they cannot be
            // promised durable when IndexedDB itself is unavailable.
            storageAvailable = false;
            state = blank();
            state.storage_error = 'Local offline storage is unavailable.';
        }

        const shouldSync = Boolean(
            storageAvailable
            && state.sync?.token
            && navigator.onLine
            && window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC !== true
        );

        // Local startup is the critical path. Resolve readiness from durable
        // IndexedDB state before starting any network operation, so a server
        // that is slow or unreachable cannot hold the installed app hostage.
        resolveReady(state);
        window.dispatchEvent(new CustomEvent('zazu:offline-ready', { detail: api }));

        if (shouldSync) {
            sync().catch(() => {
                // A failed background sync must not undo local readiness. Record
                // the recoverable error without blocking the application shell.
                if (!state || sessionCleared || !state.sync?.token) return;
                state.last_sync_error = 'Initial synchronization failed; local work remains available.';
                writeState().catch(() => {});
            });
        }
    }

    window.ZazuOffline = {
        getState: () => state,
        isPaired: () => Boolean(state?.sync?.token),
        queueMutation,
        pair,
        sync,
        assessLogout,
        clearLocalSession,
        refreshBootstrap,
        persist: writeState,
        pending,
        ready,
    };

    window.addEventListener('online', async () => {
        // The browser can reconnect while IndexedDB is still opening. Wait for
        // boot to finish so the first reconnect cannot be silently ignored.
        await ready;
        sync().catch(() => {
            // Reconnection failures are recoverable; keep the local workspace
            // available and avoid an unhandled promise rejection.
            if (state) state.last_sync_error = 'Synchronization failed; queued work will retry.';
        });
    });
    boot().catch(() => {
        // Never leave callers waiting forever if an unexpected boot error escapes.
        resolveReady(state || blank());
        window.dispatchEvent(new CustomEvent('zazu:offline-ready', { detail: api }));
    });
})();
