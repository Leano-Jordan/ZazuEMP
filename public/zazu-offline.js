(() => {
    'use strict';

    const LEGACY_DB_NAME = 'zazu-client';
    const ACTIVE_BUSINESS_ID = Number(document.body?.dataset.zazuBusinessId || 0);
    const DB_NAME = ACTIVE_BUSINESS_ID > 0
        ? 'zazu-client-business-' + ACTIVE_BUSINESS_ID
        : 'zazu-client-unscoped';
    const DB_VERSION = 1;
    const ATTACHMENT_DB_NAME = ACTIVE_BUSINESS_ID > 0
        ? 'zazu-phone-attachments-business-' + ACTIVE_BUSINESS_ID
        : 'zazu-phone-attachments-unscoped';
    const ATTACHMENT_STORE = 'files';
    const STATE = 'state';
    const QUEUE = 'mutations';
    let state = null;
    let syncing = false;
    let syncRequested = false;
    let activeSyncController = null;
    let syncRetryTimer = null;
    let syncRetryAttempt = 0;
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
    let sessionGeneration = 0;
    let sessionChannel = null;
    let primaryRegistrationAttempt = null;

    try {
        if ('BroadcastChannel' in window) {
            sessionChannel = new BroadcastChannel('zazu-offline-session-' + ACTIVE_BUSINESS_ID);
        }
    } catch {
        // Cross-tab signalling is best-effort; IndexedDB transactions remain authoritative.
    }

    function openDb(name = DB_NAME) {
        if (name === DB_NAME && dbPromise) return dbPromise;

        let opening;
        opening = new Promise((resolve, reject) => {
            const request = indexedDB.open(name, DB_VERSION);
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
                    if (name === DB_NAME && dbPromise === opening) dbPromise = null;
                };
                resolve(db);
            };
            request.onerror = () => reject(request.error || new Error('Could not open local offline storage.'));
            // IndexedDB's blocked event is a warning, not a failed request. Wait
            // for an older tab to release its connection; onversionchange above
            // asks updated tabs to close their connection automatically.
        });

        if (name === DB_NAME) {
            dbPromise = opening;
            opening.catch(() => {
                if (dbPromise === opening) dbPromise = null;
            });
        }
        return opening;
    }

    async function readState() {
        const db = await openDb();
        const stored = await new Promise((resolve, reject) => {
            const tx = db.transaction(STATE, 'readonly');
            const currentRequest = tx.objectStore(STATE).get('current');
            const migrationRequest = tx.objectStore(STATE).get('legacy-migrated');
            tx.oncomplete = () => resolve({
                state: currentRequest.result || null,
                migrated: Boolean(migrationRequest.result),
            });
            tx.onerror = () => reject(tx.error || new Error('Local offline state could not be inspected.'));
            tx.onabort = () => reject(tx.error || new Error('Local offline state inspection was aborted.'));
        });

        if (stored.state) {
            const storedBusinessId = Number(stored.state.business?.id || stored.state.sync?.device?.business_id || 0);
            if (ACTIVE_BUSINESS_ID > 0 && storedBusinessId > 0 && storedBusinessId !== ACTIVE_BUSINESS_ID) {
                throw new Error('Local offline data belongs to a different business workspace.');
            }
            return stored.state;
        }

        if (stored.migrated || ACTIVE_BUSINESS_ID < 1) return null;
        return migrateLegacyWorkspace(db);
    }

    async function migrateLegacyWorkspace(targetDb) {
        const legacyDb = await openDb(LEGACY_DB_NAME);
        try {
            const legacy = await new Promise((resolve, reject) => {
                const tx = legacyDb.transaction([STATE, QUEUE], 'readonly');
                const stateRequest = tx.objectStore(STATE).get('current');
                const queueRequest = tx.objectStore(QUEUE).getAll();
                tx.oncomplete = () => resolve({
                    state: stateRequest.result || null,
                    mutations: queueRequest.result || [],
                });
                tx.onerror = () => reject(tx.error || new Error('Legacy offline data could not be inspected.'));
                tx.onabort = () => reject(tx.error || new Error('Legacy offline data inspection was aborted.'));
            });

            const stateBusinessId = Number(legacy.state?.business?.id || 0);
            const deviceBusinessId = Number(legacy.state?.sync?.device?.business_id || 0);
            if (!legacy.state || stateBusinessId !== ACTIVE_BUSINESS_ID || deviceBusinessId !== ACTIVE_BUSINESS_ID) {
                return null;
            }

            await new Promise((resolve, reject) => {
                const tx = targetDb.transaction([STATE, QUEUE], 'readwrite');
                tx.objectStore(STATE).put(legacy.state, 'current');
                tx.objectStore(STATE).put({
                    business_id: ACTIVE_BUSINESS_ID,
                    migrated_at: new Date().toISOString(),
                }, 'legacy-migrated');
                for (const mutation of legacy.mutations) tx.objectStore(QUEUE).put(mutation);
                tx.oncomplete = resolve;
                tx.onerror = () => reject(tx.error || new Error('Legacy offline data migration failed.'));
                tx.onabort = () => reject(tx.error || new Error('Legacy offline data migration was aborted.'));
            });

            await new Promise((resolve, reject) => {
                const tx = legacyDb.transaction([STATE, QUEUE], 'readwrite');
                tx.objectStore(STATE).delete('current');
                tx.objectStore(QUEUE).clear();
                tx.oncomplete = resolve;
                tx.onerror = () => reject(tx.error || new Error('Legacy offline data cleanup failed.'));
                tx.onabort = () => reject(tx.error || new Error('Legacy offline data cleanup was aborted.'));
            });

            return legacy.state;
        } finally {
            legacyDb.close();
        }
    }

    async function writeState({ allowSessionChange = false } = {}) {
        const stateToWrite = state;
        const expectedToken = stateToWrite?.sync?.token || null;
        const db = await openDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STATE, 'readwrite');
            const store = tx.objectStore(STATE);
            const current = store.get('current');
            let sessionMismatch = false;

            current.onsuccess = () => {
                const persistedToken = current.result?.sync?.token || null;
                if (!allowSessionChange && persistedToken !== expectedToken) {
                    sessionMismatch = true;
                    tx.abort();
                    return;
                }
                store.put(stateToWrite, 'current');
            };
            current.onerror = () => tx.abort();
            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error || new Error('Local offline state write failed.'));
            tx.onabort = () => reject(sessionMismatch
                ? new Error('The offline device session changed before local state could be saved.')
                : (tx.error || new Error('Local offline state write was aborted.')));
        });
    }

    async function api(path, options = {}) {
        const requestToken = state?.sync?.token || null;
        const headers = new Headers(options.headers || {});
        headers.set('Accept', 'application/json');
        if (requestToken) headers.set('Authorization', 'Bearer ' + requestToken);
        const response = await fetch(path, {
            ...options,
            headers,
            signal: options.signal ?? activeSyncController?.signal,
        });

        // A response from an earlier device session must not be applied after
        // logout and re-pair, even if the transport completed after abort.
        if (sessionCleared || requestToken !== (state?.sync?.token || null)) {
            throw new Error('The offline device session changed while this request was in progress.');
        }

        return response;
    }

    async function recordSyncAuthorizationFailure(response) {
        if (![401, 403].includes(response?.status) || sessionCleared || !state?.sync?.token) return;
        state.sync_authorization_required = true;
        state.last_sync_error = 'This device is no longer authorised to sync. Your saved offline changes remain on this device. Ask the business owner to pair the device again.';
        try {
            await writeState();
        } finally {
            window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
        }
    }

    function applyBootstrap(bootstrap) {
        if (!bootstrap) return;

        state.business = bootstrap.business || state.business;
        state.catalogue = (bootstrap.catalogue || []).map(x => ({
            ...x.record,
            local_id: x.local_id,
            id: x.server_id,
            server_id: x.server_id,
        }));

        state.suppliers = (bootstrap.suppliers || []).map(x => ({
            ...x.record,
            local_id: x.local_id,
            id: x.server_id,
            server_id: x.server_id,
        }));
        state.purchase_orders = (bootstrap.purchase_orders || []).map(x => ({
            ...x.record,
            local_id: x.local_id,
            id: x.server_id,
            server_id: x.server_id,
            items: (x.items || []).map(item => ({
                ...item.record,
                local_id: item.local_id,
                id: item.server_id,
                server_id: item.server_id,
            })),
        }));
        state.invoices = (bootstrap.invoices || []).map(x => ({
            ...x.record,
            local_id: x.local_id,
            id: x.server_id,
            server_id: x.server_id,
            payments: (x.payments || []).map(payment => ({
                ...payment.record,
                local_id: payment.local_id,
                id: payment.server_id,
                server_id: payment.server_id,
            })),
        }));
        state.expenses = (bootstrap.expenses || []).map(x => ({
            ...x.record,
            local_id: x.local_id,
            id: x.server_id,
            server_id: x.server_id,
        }));
        state.inventory_items = (bootstrap.inventory_items || []).map(x => ({
            ...x.record,
            local_id: x.local_id,
            id: x.server_id,
            server_id: x.server_id,
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
            server_id: x.server_id,
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
            server_id: x.server_id,
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
                server_id: item.server_id,
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

    async function openAttachmentDb() {
        if (ACTIVE_BUSINESS_ID < 1) throw new Error('A business workspace is required for local attachments.');
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(ATTACHMENT_DB_NAME, 1);
            request.onupgradeneeded = () => {
                if (!request.result.objectStoreNames.contains(ATTACHMENT_STORE)) {
                    request.result.createObjectStore(ATTACHMENT_STORE, { keyPath: 'idempotency_key' });
                }
            };
            request.onsuccess = () => {
                const db = request.result;
                // A later tab may request a schema upgrade. Release this connection
                // so the upgrade can finish instead of trapping logout behind it.
                db.onversionchange = () => db.close();
                resolve(db);
            };
            request.onerror = () => reject(request.error || new Error('Local attachment storage could not be opened.'));
            // `blocked` is informational, not terminal: IndexedDB may still finish
            // once an older tab releases its connection. Do not reject prematurely.
        });
    }

    async function countPendingAttachments() {
        if (!ACTIVE_BUSINESS_ID) return 0;
        const db = await openAttachmentDb();
        try {
            return await new Promise((resolve, reject) => {
                const request = db.transaction(ATTACHMENT_STORE, 'readonly').objectStore(ATTACHMENT_STORE).getAll();
                request.onsuccess = () => resolve((request.result || []).filter(item => item.status === 'pending').length);
                request.onerror = () => reject(request.error || new Error('Local attachments could not be inspected.'));
            });
        } finally {
            db.close();
        }
    }

    async function clearAttachmentWorkspace() {
        if (ACTIVE_BUSINESS_ID < 1) return;
        const db = await openAttachmentDb();
        try {
            await new Promise((resolve, reject) => {
                const tx = db.transaction(ATTACHMENT_STORE, 'readwrite');
                tx.objectStore(ATTACHMENT_STORE).clear();
                tx.oncomplete = resolve;
                tx.onerror = () => reject(tx.error || new Error('Local attachment cleanup failed.'));
                tx.onabort = () => reject(tx.error || new Error('Local attachment cleanup was aborted.'));
            });
        } finally {
            db.close();
        }
    }


    async function readAttachmentRecords() {
        if (ACTIVE_BUSINESS_ID < 1) return [];
        const db = await openAttachmentDb();
        try {
            return await new Promise((resolve, reject) => {
                const request = db.transaction(ATTACHMENT_STORE, 'readonly')
                    .objectStore(ATTACHMENT_STORE)
                    .getAll();
                request.onsuccess = () => resolve(request.result || []);
                request.onerror = () => reject(request.error || new Error('Local attachments could not be read.'));
            });
        } finally {
            db.close();
        }
    }

    async function readAttachmentRecord(idempotencyKey) {
        const db = await openAttachmentDb();
        try {
            return await new Promise((resolve, reject) => {
                const request = db.transaction(ATTACHMENT_STORE, 'readonly')
                    .objectStore(ATTACHMENT_STORE)
                    .get(idempotencyKey);
                request.onsuccess = () => resolve(request.result || null);
                request.onerror = () => reject(request.error || new Error('The local attachment could not be read.'));
            });
        } finally {
            db.close();
        }
    }

    async function writeAttachmentRecord(record, expectedSessionGeneration = null) {
        const db = await openAttachmentDb();
        try {
            // Prevent a late upload response from recreating local data after logout.
            if (expectedSessionGeneration !== null
                && (sessionCleared || sessionGeneration !== expectedSessionGeneration)) {
                return false;
            }
            await new Promise((resolve, reject) => {
                const tx = db.transaction(ATTACHMENT_STORE, 'readwrite');
                tx.objectStore(ATTACHMENT_STORE).put(record);
                tx.oncomplete = resolve;
                tx.onerror = () => reject(tx.error || new Error('The local attachment could not be saved.'));
                tx.onabort = () => reject(tx.error || new Error('The local attachment save was aborted.'));
            });
            return true;
        } finally {
            db.close();
        }
    }

    async function queueAttachments(eventId, files, description = '', localEventId = null) {
        await ready;
        if (sessionCleared) throw new Error('The local device session has ended. Reload Zazu before saving attachments.');
        if (ACTIVE_BUSINESS_ID < 1) throw new Error('Choose a business workspace before saving attachments.');

        const targetEventId = Number(eventId);
        const targetLocalEventId = String(localEventId || '').trim();
        const hasServerIdentity = Number.isSafeInteger(targetEventId) && targetEventId > 0;
        if (!hasServerIdentity && !/^[a-zA-Z0-9_-]{1,120}$/.test(targetLocalEventId)) {
            throw new Error('Choose a saved local job before attaching files.');
        }

        const selectedFiles = Array.from(files || []);
        if (selectedFiles.length < 1 || selectedFiles.length > 10) {
            throw new Error('Choose between 1 and 10 files per upload.');
        }

        const allowedExtensions = new Set(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'jpg', 'jpeg', 'png', 'webp']);
        for (const file of selectedFiles) {
            if (!(file instanceof Blob) || !file.name) {
                throw new Error('Choose valid files before saving attachments.');
            }
            if (file.size < 1 || file.size > 20 * 1024 * 1024) {
                throw new Error('Each attachment must be larger than 0 bytes and no bigger than 20 MB.');
            }
            const extension = String(file.name).split('.').pop().toLowerCase();
            if (!allowedExtensions.has(extension)) {
                throw new Error('Use PDF, Office, CSV, text, JPG, PNG or WebP attachments.');
            }
        }

        const records = selectedFiles.map(file => ({
            business_id: ACTIVE_BUSINESS_ID,
            event_id: hasServerIdentity ? targetEventId : null,
            ...(hasServerIdentity ? {} : { event_local_id: targetLocalEventId }),
            idempotency_key: crypto.randomUUID(),
            original_name: file.name,
            mime_type: file.type || 'application/octet-stream',
            size: file.size,
            description: String(description || '').trim().slice(0, 255),
            status: 'pending',
            file,
            created_at: new Date().toISOString(),
            attempts: 0,
            last_error: null,
        }));

        const db = await openAttachmentDb();
        try {
            await new Promise((resolve, reject) => {
                const tx = db.transaction(ATTACHMENT_STORE, 'readwrite');
                const store = tx.objectStore(ATTACHMENT_STORE);
                for (const record of records) store.put(record);
                tx.oncomplete = resolve;
                tx.onerror = () => reject(tx.error || new Error('The complete attachment batch could not be saved.'));
                tx.onabort = () => reject(tx.error || new Error('The attachment batch save was aborted.'));
            });
        } finally {
            db.close();
        }

        window.dispatchEvent(new CustomEvent('zazu:offline-attachments-updated', {
            detail: { event_id: hasServerIdentity ? targetEventId : null, event_local_id: targetLocalEventId || null },
        }));

        if (navigator.onLine && state?.sync?.token) {
            syncAttachments().catch(() => {});
        }

        return records.map(record => ({
            idempotency_key: record.idempotency_key,
            status: record.status,
        }));
    }

    async function queueAttachment(eventId, file, description = '') {
        const [record] = await queueAttachments(eventId, [file], description);
        return record;
    }
    async function reconcileAttachmentEventIds() {
        const jobs = Array.isArray(state?.jobs) ? state.jobs : [];
        if (!jobs.length || sessionCleared || ACTIVE_BUSINESS_ID < 1) return 0;

        const records = await readAttachmentRecords();
        const updates = [];

        for (const record of records) {
            if (
                record.status !== 'pending'
                || Number(record.business_id) !== ACTIVE_BUSINESS_ID
                || Number(record.event_id) > 0
                || !record.event_local_id
            ) {
                continue;
            }

            const job = jobs.find(item => String(item.local_id || '') === String(record.event_local_id));
            const serverEventId = Number(job?.server_id ?? job?.id);
            if (!Number.isSafeInteger(serverEventId) || serverEventId < 1) continue;

            updates.push({ ...record, event_id: serverEventId });
        }

        if (!updates.length) return 0;

        const db = await openAttachmentDb();
        try {
            await new Promise((resolve, reject) => {
                const tx = db.transaction(ATTACHMENT_STORE, 'readwrite');
                const store = tx.objectStore(ATTACHMENT_STORE);
                for (const record of updates) store.put(record);
                tx.oncomplete = resolve;
                tx.onerror = () => reject(tx.error || new Error('Offline attachment job links could not be updated.'));
                tx.onabort = () => reject(tx.error || new Error('Offline attachment job-link update was aborted.'));
            });
        } finally {
            db.close();
        }

        return updates.length;
    }

    let attachmentSyncing = false;

    async function syncAttachments() {
        await ready;
        if (attachmentSyncing || sessionCleared || ACTIVE_BUSINESS_ID < 1) {
            return { uploaded: 0, pending: await countPendingAttachments() };
        }

        attachmentSyncing = true;
        let uploaded = 0;

        try {
            await reconcileAttachmentEventIds();
            if (!navigator.onLine || !state?.sync?.token || sessionCleared) {
                return { uploaded: 0, pending: await countPendingAttachments() };
            }

            const records = await readAttachmentRecords();
            for (const record of records) {
                if (
                    sessionCleared
                    || !state?.sync?.token
                    || record.status !== 'pending'
                    || Number(record.business_id) !== ACTIVE_BUSINESS_ID
                    || !Number.isSafeInteger(Number(record.event_id))
                    || Number(record.event_id) < 1
                    || !(record.file instanceof Blob)
                ) {
                    continue;
                }

                const form = new FormData();
                form.append('event_id', String(record.event_id));
                form.append('idempotency_key', record.idempotency_key);
                if (record.description) form.append('description', record.description);
                form.append('file', record.file, record.original_name);

                const uploadSessionGeneration = sessionGeneration;
                const uploadToken = state.sync.token;
                let response;
                try {
                    response = await api('/api/sync/attachments', {
                        method: 'POST',
                        body: form,
                    });
                } catch (error) {
                    if (sessionCleared || !state?.sync?.token) break;
                    await writeAttachmentRecord({
                        ...record,
                        attempts: Number(record.attempts || 0) + 1,
                        last_error: 'Upload paused. Zazu will retry when the connection is available.',
                    }, uploadSessionGeneration);
                    break;
                }

                if (sessionCleared || sessionGeneration !== uploadSessionGeneration
                    || state?.sync?.token !== uploadToken) break;

                if (!response.ok) {
                    await recordSyncAuthorizationFailure(response);
                    const result = await response.json().catch(() => ({}));
                    await writeAttachmentRecord({
                        ...record,
                        attempts: Number(record.attempts || 0) + 1,
                        last_error: result.message || 'The server did not accept this attachment. It remains saved on this phone.',
                    }, uploadSessionGeneration);
                    if ([401, 403, 429].includes(response.status)) break;
                    continue;
                }

                const result = await response.json();
                if (sessionCleared || sessionGeneration !== uploadSessionGeneration
                    || state?.sync?.token !== uploadToken) break;
                const confirmedAttachment = result.attachment;
                const confirmedId = Number(confirmedAttachment?.id);
                const confirmedEventId = Number(confirmedAttachment?.event_id);
                const confirmedKey = String(confirmedAttachment?.idempotency_key || '');
                if (
                    !Number.isSafeInteger(confirmedId)
                    || confirmedId < 1
                    || confirmedEventId !== Number(record.event_id)
                    || confirmedKey !== record.idempotency_key
                ) {
                    await writeAttachmentRecord({
                        ...record,
                        attempts: Number(record.attempts || 0) + 1,
                        last_error: 'The server response did not match this file and Work record. The original file remains queued for a safe retry.',
                    }, uploadSessionGeneration);
                    continue;
                }

                const persisted = await writeAttachmentRecord({
                    ...record,
                    status: 'synced',
                    attachment: result.attachment,
                    attempts: Number(record.attempts || 0) + 1,
                    last_error: null,
                    synced_at: new Date().toISOString(),
                }, uploadSessionGeneration);
                if (!persisted) break;
                uploaded++;
                window.dispatchEvent(new CustomEvent('zazu:offline-attachments-updated', {
                    detail: { event_id: Number(record.event_id) },
                }));
            }
        } finally {
            attachmentSyncing = false;
        }

        return { uploaded, pending: await countPendingAttachments() };
    }

    async function loadAttachments(eventId, localEventId = null) {
        await ready;
        const targetEventId = Number(eventId);
        const targetLocalEventId = String(localEventId || '').trim();
        const hasServerIdentity = Number.isSafeInteger(targetEventId) && targetEventId > 0;
        if (!hasServerIdentity && !/^[a-zA-Z0-9_-]{1,120}$/.test(targetLocalEventId)) return [];

        if (hasServerIdentity && navigator.onLine && state?.sync?.token && !sessionCleared) {
            const readSessionGeneration = sessionGeneration;
            const readSessionToken = state.sync.token;
            try {
                await syncAttachments();
                if (sessionCleared || sessionGeneration !== readSessionGeneration
                    || state?.sync?.token !== readSessionToken) {
                    return (await readAttachmentRecords()).filter(record =>
                        Number(record.business_id) === ACTIVE_BUSINESS_ID
                        && Number(record.event_id) === targetEventId
                    );
                }
                const response = await api('/api/sync/attachments?event_id=' + encodeURIComponent(targetEventId));
                if (sessionCleared || sessionGeneration !== readSessionGeneration
                    || state?.sync?.token !== readSessionToken) {
                    return (await readAttachmentRecords()).filter(record =>
                        Number(record.business_id) === ACTIVE_BUSINESS_ID
                        && Number(record.event_id) === targetEventId
                    );
                }
                if (response.ok) {
                    const result = await response.json();
                    if (sessionCleared || sessionGeneration !== readSessionGeneration
                        || state?.sync?.token !== readSessionToken) {
                        return (await readAttachmentRecords()).filter(record =>
                            Number(record.business_id) === ACTIVE_BUSINESS_ID
                            && Number(record.event_id) === targetEventId
                        );
                    }
                    for (const attachment of result.attachments || []) {
                        if (sessionCleared || sessionGeneration !== readSessionGeneration
                            || state?.sync?.token !== readSessionToken) break;
                        const attachmentId = Number(attachment.id);
                        const attachmentEventId = Number(attachment.event_id);
                        if (
                            !Number.isSafeInteger(attachmentId)
                            || attachmentId < 1
                            || attachmentEventId !== targetEventId
                        ) {
                            continue;
                        }

                        const key = attachment.idempotency_key || ('server-' + attachmentId);
                        const existing = await readAttachmentRecord(key);
                        if (sessionCleared || sessionGeneration !== readSessionGeneration
                            || state?.sync?.token !== readSessionToken) break;

                        // Keep an unconfirmed local upload intact. Retrying its
                        // idempotency key is the only safe way to establish whether
                        // the server accepted the exact bytes.
                        if (existing?.status === 'pending' && existing.file instanceof Blob) continue;
                        if (existing?.file instanceof Blob) {
                            await writeAttachmentRecord({
                                ...existing,
                                status: 'synced',
                                attachment,
                                last_error: null,
                            }, readSessionGeneration);
                            continue;
                        }

                        try {
                            const fileResponse = await api('/api/sync/attachments/' + encodeURIComponent(attachment.id));
                            if (sessionCleared || sessionGeneration !== readSessionGeneration
                                || state?.sync?.token !== readSessionToken) break;
                            if (!fileResponse.ok) continue;
                            const file = await fileResponse.blob();
                            if (sessionCleared || sessionGeneration !== readSessionGeneration
                                || state?.sync?.token !== readSessionToken) break;
                            if (!file.size && Number(attachment.size || 0) > 0) continue;

                            await writeAttachmentRecord({
                                business_id: ACTIVE_BUSINESS_ID,
                                event_id: Number(attachment.event_id),
                                idempotency_key: key,
                                original_name: attachment.original_name,
                                mime_type: attachment.mime_type || file.type || 'application/octet-stream',
                                size: Number(attachment.size || file.size),
                                description: attachment.description || '',
                                status: 'synced',
                                attachment,
                                file,
                                created_at: attachment.created_at || new Date().toISOString(),
                                attempts: 0,
                                last_error: null,
                            }, readSessionGeneration);
                        } catch {
                            // A failed download leaves the rest of the job files usable.
                        }
                    }
                }
            } catch {
                // The local copy remains authoritative when the server is unreachable.
            }
        }

        return (await readAttachmentRecords()).filter(record =>
            Number(record.business_id) === ACTIVE_BUSINESS_ID
            && (hasServerIdentity
                ? Number(record.event_id) === targetEventId
                : String(record.event_local_id || '') === targetLocalEventId)
        ).sort((a, b) => String(a.created_at || '').localeCompare(String(b.created_at || '')));
    }

    async function getAttachmentFile(idempotencyKey) {
        const record = await readAttachmentRecord(String(idempotencyKey || ''));
        if (!record || Number(record.business_id) !== ACTIVE_BUSINESS_ID || !(record.file instanceof Blob)) {
            throw new Error('This file is not available in the local phone copy.');
        }
        return record.file;
    }

    async function assessLogout() {
        await ready;

        if (!storageAvailable) {
            return { inspectionFailed: true, total: null, pendingCount: null, failedCount: null, conflictCount: null, attachmentPendingCount: null };
        }

        try {
            const [records, attachmentPendingCount] = await Promise.all([allMutations(), countPendingAttachments()]);
            const pendingCount = records.filter(item => item.status === 'pending').length;
            const failedCount = records.length - pendingCount;
            const conflictCount = (state?.conflicts || []).filter(item => item.status === 'open').length;

            return {
                inspectionFailed: false,
                total: records.length,
                pendingCount,
                failedCount,
                conflictCount,
                attachmentPendingCount,
                localDirty: Boolean(state?.local_dirty),
            };
        } catch {
            return { inspectionFailed: true, total: null, pendingCount: null, failedCount: null, conflictCount: null, attachmentPendingCount: null };
        }
    }

    async function clearStoredWorkspace(broadcast = true) {
        const wasCleared = sessionCleared;
        sessionGeneration++;
        sessionCleared = true;
        activeSyncController?.abort();

        try {
            // Attachments are business-scoped too. Clear them before the
            // mutation workspace so a failed attachment cleanup blocks logout.
            await clearAttachmentWorkspace();
            const db = await openDb();
            const migrationMarker = await new Promise((resolve, reject) => {
                const request = db.transaction(STATE, 'readonly')
                    .objectStore(STATE)
                    .get('legacy-migrated');
                request.onsuccess = () => resolve(request.result || null);
                request.onerror = () => reject(request.error || new Error('Local migration state could not be inspected.'));
            });
            await new Promise((resolve, reject) => {
                const tx = db.transaction([STATE, QUEUE], 'readwrite');
                tx.objectStore(STATE).clear();
                if (migrationMarker) tx.objectStore(STATE).put(migrationMarker, 'legacy-migrated');
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

    async function resolveConflict(conflictId, action, note = null) {
        await ready;

        const id = Number(conflictId);
        if (!Number.isSafeInteger(id) || id < 1) {
            throw new Error('A valid sync conflict is required.');
        }
        if (!['retry', 'discard'].includes(action)) {
            throw new Error('Choose whether to retry or discard the saved change.');
        }
        if (sessionCleared || !state?.sync?.token) {
            throw new Error('Pair this device with Zazu before resolving its sync conflicts.');
        }

        const conflictSessionToken = state.sync.token;
        const queued = await allMutations();
        if (!queued.some(item => Number(item.conflict_id) === id)) {
            throw new Error('The saved offline change could not be found on this device. Refresh the sync state before resolving it.');
        }

        const response = await api('/api/sync/conflicts/' + id + '/resolve', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action, note }),
        });
        const result = await response.json().catch(() => ({}));
        if (sessionCleared || state?.sync?.token !== conflictSessionToken) {
            throw new Error('The device session changed while this conflict was being resolved. Refresh the workspace before retrying.');
        }
        if (!response.ok) {
            throw new Error(result.message || 'Zazu could not resolve this offline sync conflict.');
        }

        const mutationStatus = result.mutation_status;
        const retryWithNewMutationId = mutationStatus === 'new_mutation_required';
        const shouldRetry = mutationStatus === 'pending' || retryWithNewMutationId;
        const shouldDiscard = ['discarded', 'applied'].includes(mutationStatus);
        if (!shouldRetry && !shouldDiscard) {
            throw new Error('The server did not confirm whether this offline change should be retried or removed.');
        }

        const db = await openDb();
        await new Promise((resolve, reject) => {
            const tx = db.transaction([STATE, QUEUE], 'readwrite');
            const store = tx.objectStore(QUEUE);
            const stateStore = tx.objectStore(STATE);
            const all = store.getAll();
            const currentState = stateStore.get('current');
            let sessionMismatch = false;

            currentState.onsuccess = () => {
                if (sessionCleared || (currentState.result?.sync?.token || null) !== conflictSessionToken) {
                    sessionMismatch = true;
                    tx.abort();
                }
            };
            currentState.onerror = () => tx.abort();

            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error || new Error('Offline conflict resolution could not be saved locally.'));
            tx.onabort = () => reject(sessionMismatch
                ? new Error('The device session changed before conflict resolution could be saved.')
                : (tx.error || new Error('Offline conflict resolution transaction was aborted.')));

            all.onsuccess = () => {
                if (sessionMismatch) return;
                const mutation = (all.result || []).find(item => Number(item.conflict_id) === id);
                if (mutation && shouldRetry) {
                    const retry = { ...mutation, status: 'pending' };
                    if (retryWithNewMutationId) {
                        // The server has permanently bound the old ID to a
                        // different payload. Keep the edited work, but retry
                        // it as a new immutable mutation.
                        retry.id = crypto.randomUUID();
                        retry.created_at = new Date().toISOString();
                        store.delete(mutation.id);
                    }
                    delete retry.conflict_id;
                    delete retry.server_sequence;
                    delete retry.server_status;
                    delete retry.last_error;
                    store.put(retry);
                } else if (mutation && shouldDiscard) {
                    store.delete(mutation.id);
                }

                const pendingCount = store.index('status').count('pending');
                pendingCount.onsuccess = () => {
                    const nextState = {
                        ...state,
                        conflicts: (state.conflicts || []).map(conflict =>
                            Number(conflict.id) === id ? { ...conflict, status: 'resolved' } : conflict
                        ),
                        local_dirty: pendingCount.result > 0,
                    };
                    tx.objectStore(STATE).put(nextState, 'current');
                    tx.oncomplete = () => {
                        state = nextState;
                        window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
                        resolve();
                    };
                };
                pendingCount.onerror = () => tx.abort();
            };
            all.onerror = () => tx.abort();
        });

        if (shouldRetry) {
            sync().catch(() => {
                if (state) state.last_sync_error = 'The saved change is queued for retry when Zazu reconnects.';
            });
        }

        return result;
    }

    sessionChannel?.addEventListener('message', event => {
        if (event.data?.type === 'clear-session') {
            sessionCleared = true;
            sessionGeneration++;
            activeSyncController?.abort();
            state = blank();
            clearStoredWorkspace(false).catch(() => {
                storageAvailable = false;
            });
            return;
        }

        if (event.data?.type !== 'session-replaced') return;

        // Another tab paired this business workspace with a new device token.
        // Stop old-session work before adopting the durable state written by
        // that tab; never keep a stale in-memory credential alive.
        sessionGeneration++;
        sessionCleared = true;
        activeSyncController?.abort();
        Promise.resolve().then(async () => {
            await ready;
            const nextState = await readState();
            if (!nextState?.sync?.token) {
                state = blank();
                return;
            }

            state = nextState;
            sessionCleared = false;
            window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
            if (!navigator.onLine) return;
            if (syncing) {
                syncRequested = true;
                return;
            }
            sync().catch(() => {
                if (!state || sessionCleared || !state.sync?.token) return;
                state.last_sync_error = 'Synchronization failed; queued work will retry.';
                writeState().catch(() => {});
            });
        }).catch(() => {
            sessionCleared = true;
            storageAvailable = false;
        });
    });


    async function queueMutation(entityType, operation, payload, nextState = null) {
        await ready;
        if (sessionCleared) {
            throw new Error('This device session has been signed out. Sign in and pair the device again before making offline changes.');
        }
        if (!storageAvailable) {
            throw new Error('Local offline storage is unavailable; this change was not saved.');
        }
        const expectedToken = (nextState || state)?.sync?.token || null;
        const record = payload?.record && typeof payload.record === 'object' && !Array.isArray(payload.record)
            ? payload.record
            : (payload || {});
        const mutationPayload = {
            local_id: payload?.local_id || null,
            server_id: payload?.server_id ?? null,
            record,
        };
        const mutation = {
            id: crypto.randomUUID(),
            entity_type: entityType,
            entity_id: mutationPayload.local_id || mutationPayload.server_id || crypto.randomUUID(),
            operation,
            payload: mutationPayload,
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

        if (sessionCleared) {
            throw new Error('This device session has been signed out. Sign in and pair the device again before making offline changes.');
        }

        return new Promise((resolve, reject) => {
            const stateToPersist = { ...(nextState || state), local_dirty: true };
            const tx = db.transaction([STATE, QUEUE], 'readwrite');
            const stateStore = tx.objectStore(STATE);
            const currentState = stateStore.get('current');
            let sessionMismatch = false;

            currentState.onsuccess = () => {
                const persistedToken = currentState.result?.sync?.token || null;
                if (sessionCleared || persistedToken !== expectedToken) {
                    sessionMismatch = true;
                    tx.abort();
                    return;
                }
                tx.objectStore(QUEUE).put(mutation);
                stateStore.put(stateToPersist, 'current');
            };
            currentState.onerror = () => tx.abort();
            tx.oncomplete = () => {
                state = stateToPersist;
                // A local save may happen while the browser still reports
                // online but the host is unavailable. Start recovery even if
                // the initial device registration never obtained a token.
                if (navigator.onLine) scheduleSyncRetry();
                resolve(mutation);
            };
            tx.onerror = () => {
                if (sessionMismatch) return;
                storageAvailable = false;
                reject(tx.error || new Error('Local offline queue transaction failed.'));
            };
            tx.onabort = () => {
                if (sessionMismatch) {
                    reject(new Error('The device session changed before this offline change could be saved. Refresh the workspace and retry.'));
                    return;
                }
                storageAvailable = false;
                reject(tx.error || new Error('Local offline queue transaction was aborted.'));
            };
        });
    }

    async function push() {
        if (sessionCleared || !navigator.onLine || !state?.sync?.token) return;
        const allMutations = await pending();
        if (sessionCleared || !state?.sync?.token || !allMutations.length) return;

        // The server accepts at most 100 mutations per request. Keep the
        // client durable queue larger than that and drain it in ordered batches.
        const mutations = allMutations.slice(0, 100);

        const pushSessionToken = state.sync.token;
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

        if (!response.ok) {
            await recordSyncAuthorizationFailure(response);
            if (![401, 403].includes(response.status) && state && !sessionCleared) {
                state.last_sync_error = 'The sync server is temporarily unavailable (HTTP ' + response.status + '). Saved changes will retry automatically.';
                await writeState().catch(() => {});
                window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
            }
            return;
        }

        const result = await response.json();
        if (sessionCleared || !state?.sync?.token) return;
        const recorded = new Map((result.recorded || []).map(item => [item.id, item]));
        const db = await openDb();

        await new Promise((resolve, reject) => {
            const tx = db.transaction([QUEUE, STATE], 'readwrite');
            const store = tx.objectStore(QUEUE);
            const stateStore = tx.objectStore(STATE);
            const currentStateRequest = stateStore.get('current');
            let nextState = null;

            let sessionMismatch = false;
            currentStateRequest.onsuccess = () => {
                const persistedState = currentStateRequest.result || state || blank();
                if (sessionCleared || (persistedState.sync?.token || null) !== pushSessionToken) {
                    sessionMismatch = true;
                    tx.abort();
                    return;
                }
                nextState = {
                    ...persistedState,
                    conflicts: [...(persistedState.conflicts || [])],
                };

                for (const mutation of mutations) {
                    const remote = recorded.get(mutation.id);
                    if (!remote) continue;

                    if (remote.status === 'applied' || remote.status === 'discarded') {
                        store.delete(mutation.id);
                    } else if (remote.error && remote.conflict_id) {
                        // A deterministic domain/identity rejection is terminal
                        // until the operator resolves its explicit conflict record.
                        // Persist the failed queue record and conflict together.
                        store.put({
                            ...mutation,
                            status: 'failed',
                            server_sequence: remote.sequence,
                            server_status: remote.status,
                            conflict_id: remote.conflict_id,
                            last_error: remote.error,
                        });

                        nextState.conflicts = [
                            ...nextState.conflicts.filter(item => Number(item.id) !== Number(remote.conflict_id)),
                            {
                                id: remote.conflict_id,
                                mutation_id: mutation.id,
                                entity_type: mutation.entity_type,
                                entity_id: mutation.entity_id,
                                conflict_type: remote.conflict_type || 'domain_rejection',
                                message: remote.error,
                                status: 'open',
                            },
                        ];
                    } else if (remote.error && remote.retryable) {
                        // Infrastructure/unexpected failures must remain retryable.
                        // Do not strand them as failed records without a resolvable conflict.
                        store.put({
                            ...mutation,
                            status: 'pending',
                            server_sequence: remote.sequence,
                            server_status: remote.status,
                            last_error: remote.error,
                        });
                    } else if (remote.error) {
                        // Fail closed if a future server response adds an error
                        // without either a conflict record or retryable disposition.
                        store.put({
                            ...mutation,
                            status: 'failed',
                            server_sequence: remote.sequence,
                            server_status: remote.status,
                            last_error: remote.error,
                        });
                    } else {
                        store.put({
                            ...mutation,
                            server_sequence: remote.sequence,
                            server_status: remote.status,
                            last_error: remote.error || 'The server did not confirm this offline change',
                        });
                    }
                }

                stateStore.put(nextState, 'current');
            };

            currentStateRequest.onerror = () => tx.abort();
            tx.oncomplete = () => {
                state = nextState || state;
                resolve();
            };
            tx.onerror = () => reject(tx.error || new Error('Offline queue acknowledgement failed.'));
            tx.onabort = () => reject(sessionMismatch
                ? new Error('The device session changed before sync acknowledgement could be applied.')
                : (tx.error || new Error('Offline queue acknowledgement was aborted.')));
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
        window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
    }

    async function applyBootstrapIfQueueClean(bootstrap, expectedToken) {
        const db = await openDb();

        return new Promise((resolve, reject) => {
            const tx = db.transaction([STATE, QUEUE], 'readwrite');
            const queueRequest = tx.objectStore(QUEUE).index('status').getAll('pending');
            const stateRequest = tx.objectStore(STATE).get('current');
            let queueReady = false;
            let stateReady = false;
            let applied = false;

            const reconcile = () => {
                if (!queueReady || !stateReady || applied) return;

                // A local mutation may have been saved while the network
                // bootstrap was in flight. Never let a remote snapshot replace
                // that newer local workspace or acknowledge past its queue.
                if (queueRequest.result?.length || sessionCleared) return;

                const persistedState = stateRequest.result;
                if (!persistedState || persistedState.sync?.token !== expectedToken) return;

                state = persistedState;
                applyBootstrap(bootstrap);
                tx.objectStore(STATE).put(state, 'current');
                applied = true;
            };

            queueRequest.onsuccess = () => {
                queueReady = true;
                reconcile();
            };
            stateRequest.onsuccess = () => {
                stateReady = true;
                reconcile();
            };
            queueRequest.onerror = () => tx.abort();
            stateRequest.onerror = () => tx.abort();
            tx.oncomplete = () => resolve(applied);
            tx.onerror = () => reject(tx.error || new Error('Offline bootstrap reconciliation failed.'));
            tx.onabort = () => reject(tx.error || new Error('Offline bootstrap reconciliation was aborted.'));
        });
    }

    async function pull() {
        if (sessionCleared || !navigator.onLine || !state?.sync?.token || state.local_dirty) return;

        for (let batch = 0; batch < 20; batch++) {
            if (sessionCleared || !state?.sync?.token) return;
            const response = await api('/api/sync/pull?stream=business&limit=100');
            if (!response.ok) {
                await recordSyncAuthorizationFailure(response);
                return;
            }
            if (sessionCleared || !state?.sync?.token) return;

            const result = await response.json();
            if (sessionCleared || !state?.sync?.token) return;
            const mutations = result.mutations || [];
            if (!mutations.length) return;

            const bootstrap = await api('/api/sync/bootstrap');
            if (!bootstrap.ok || sessionCleared || !state?.sync?.token) return;

            const snapshot = await bootstrap.json();
            if (sessionCleared || !state?.sync?.token) return;
            const expectedToken = state.sync.token;
            const applied = await applyBootstrapIfQueueClean(snapshot, expectedToken);
            if (!applied) return;

            const highest = Math.max(...mutations.map(m => Number(m.delivery_sequence || 0)));
            if (sessionCleared || !state?.sync?.token) return;
            const ack = await api('/api/sync/acknowledge', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ sequence: highest, stream: 'business' }),
            });

            if (!ack.ok) return;
        }
    }

    function clearSyncRetry(resetAttempts = false) {
        if (syncRetryTimer !== null) {
            window.clearTimeout(syncRetryTimer);
            syncRetryTimer = null;
        }
        if (resetAttempts) syncRetryAttempt = 0;
    }

    function scheduleSyncRetry() {
        if (syncRetryTimer !== null || sessionCleared || !navigator.onLine
            || state?.sync_authorization_required || state?.sync_registration_blocked) return;

        // Retry both halves of the recovery path: obtain a device credential
        // when first registration failed, then drain the durable mutation queue.
        // A browser can remain "online" while its local host is still starting,
        // so this cannot depend on another online event.
        const delay = Math.min(30000, 1000 * (2 ** Math.min(syncRetryAttempt, 5)));
        syncRetryAttempt += 1;
        syncRetryTimer = window.setTimeout(async () => {
            syncRetryTimer = null;
            if (sessionCleared || !navigator.onLine || state?.sync_authorization_required
                || state?.sync_registration_blocked) return;

            try {
                if (!state?.sync?.token) {
                    await registerPrimaryDevice();
                }

                if (sessionCleared || !navigator.onLine || state?.sync_authorization_required) return;
                if (state?.sync?.token) {
                    await sync();
                    return;
                }

                const queued = await pending();
                if (queued.length) scheduleSyncRetry();
            } catch {
                if (!state || sessionCleared) return;
                state.last_sync_error = 'Device registration or synchronization failed. Saved changes remain on this device and will retry automatically.';
                await writeState().catch(() => {});
                window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
                scheduleSyncRetry();
            }
        }, delay);
    }

    async function sync() {
        if (syncing) {
            // A token replacement can request a fresh pass while the previous
            // session is still unwinding. Do not lose that request.
            syncRequested = true;
            return;
        }
        if (sessionCleared || !navigator.onLine || !state?.sync?.token) return;
        clearSyncRetry();
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
            if (sessionCleared || !state?.sync?.token) return;
            await syncAttachments();
        } finally {
            if (activeSyncController === controller) activeSyncController = null;
            syncing = false;
            if (syncRequested) {
                syncRequested = false;
                Promise.resolve().then(() => sync()).catch(() => {
                    if (!state || sessionCleared || !state.sync?.token) return;
                    state.last_sync_error = 'Synchronization failed; queued work will retry.';
                    writeState().catch(() => {});
                });
            }

            if (!sessionCleared && navigator.onLine && state?.sync?.token
                && !state.sync_authorization_required) {
                try {
                    if ((await pending()).length) {
                        scheduleSyncRetry();
                    } else {
                        clearSyncRetry(true);
                        if (state.last_sync_error && !state.sync_authorization_required) {
                            state.last_sync_error = null;
                            await writeState().catch(() => {});
                            window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
                        }
                    }
                } catch {
                    scheduleSyncRetry();
                }
            } else {
                clearSyncRetry();
            }
        }
    }

    async function refreshBootstrap() {
        if (sessionCleared || !navigator.onLine || !state?.sync?.token || state.local_dirty) return;
        const response = await api('/api/sync/bootstrap');
        if (!response.ok) {
            await recordSyncAuthorizationFailure(response);
            return;
        }
        if (sessionCleared || !state?.sync?.token) return;
        const bootstrap = await response.json();
        if (sessionCleared || !state?.sync?.token) return;
        const expectedToken = state.sync.token;
        await applyBootstrapIfQueueClean(bootstrap, expectedToken);
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
        const expectedBusinessId = ACTIVE_BUSINESS_ID;
        if (Number(result.device?.business_id || 0) !== expectedBusinessId) {
            // Do not bind a token for a different workspace to this business-scoped
            // IndexedDB database. Revoke the newly issued token before rejecting.
            let tokenRevoked = false;
            try {
                const revokeResponse = await fetch('/api/sync/revoke', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + result.token },
                    cache: 'no-store',
                });
                tokenRevoked = revokeResponse.ok;
            } catch {
                // Keep the local workspace untouched even when cleanup is unavailable.
            }
            if (!tokenRevoked) {
                throw new Error('This pairing code belongs to a different business workspace. Your saved local data has not been changed, but the business owner must revoke the newly paired device from Trusted phone access.');
            }
            throw new Error('This pairing code belongs to a different business workspace. The issued device token was revoked and your saved local data has not been changed.');
        }

        const existingMutations = await allMutations();
        const previousState = state || blank();
        const preserveLocalWorkspace = existingMutations.length > 0 || previousState.local_dirty === true;

        // Pairing replaces the credential boundary. Invalidate attachment
        // reads/writes and stop any request issued with the previous token.
        sessionGeneration++;
        activeSyncController?.abort();
        sessionCleared = false;
        state = {
            ...(preserveLocalWorkspace ? previousState : blank()),
            installation_id: result.device.installation_id,
            sync: { token: result.token, device: result.device },
            local_dirty: preserveLocalWorkspace,
            sync_authorization_required: false,
            last_sync_error: null,
        };

        // A pending or failed local mutation may depend on locally cached records.
        // Keep that workspace intact; the normal push/pull/bootstrap cycle will
        // reconcile it after the queue has been safely processed.
        if (!preserveLocalWorkspace) applyBootstrap(result.bootstrap);
        await writeState({ allowSessionChange: true });
        window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
        sessionChannel?.postMessage({ type: 'session-replaced' });
        syncAttachments().catch(() => {});
        if (preserveLocalWorkspace) {
            sync().catch(() => {
                if (!state || sessionCleared || !state.sync?.token) return;
                state.last_sync_error = 'Re-pairing succeeded. Saved changes remain queued and will retry when sync is available.';
                writeState().catch(() => {});
            });
        }
        return result.device;
    }

    async function registerPrimaryDevice() {
        if (primaryRegistrationAttempt) return primaryRegistrationAttempt;

        const attempt = registerPrimaryDeviceOnce();
        primaryRegistrationAttempt = attempt;
        try {
            return await attempt;
        } finally {
            if (primaryRegistrationAttempt === attempt) primaryRegistrationAttempt = null;
        }
    }

    async function registerPrimaryDeviceOnce() {
        if (sessionCleared || !navigator.onLine || !storageAvailable || !ACTIVE_BUSINESS_ID || state?.sync?.token) return false;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) return false;

        if (!state.installation_id) {
            if (!window.crypto?.randomUUID) {
                state.last_sync_error = 'This browser cannot create a secure device identity.';
                return false;
            }
            state.installation_id = window.crypto.randomUUID();
            await writeState({ allowSessionChange: true });
        }

        const registrationGeneration = sessionGeneration;
        const registrationInstallationId = state.installation_id;
        const response = await fetch('/offline/device/register', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                installation_id: state.installation_id,
                device_name: /Android|iPhone|iPad/i.test(navigator.userAgent) ? 'Zazu phone' : 'Zazu device',
                device_type: /Android|iPhone|iPad/i.test(navigator.userAgent) ? 'phone' : 'desktop',
            }),
        });

        if (sessionCleared || sessionGeneration !== registrationGeneration
            || state?.sync?.token || state?.installation_id !== registrationInstallationId) return false;

        if (!response.ok) {
            state.sync_registration_blocked = response.status === 409;
            state.last_sync_error = response.status === 409
                ? 'This installation is registered to a different business workspace. The owner must resolve the device registration before these changes can sync.'
                : 'Zazu could not register this device (HTTP ' + response.status + '). Your local work remains saved and registration will retry automatically.';
            await writeState().catch(() => {});
            window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
            return false;
        }

        const result = await response.json();
        if (Number(result.device?.business_id || 0) !== ACTIVE_BUSINESS_ID) {
            state.last_sync_error = 'The registered device belongs to a different business workspace.';
            await writeState().catch(() => {});
            window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
            return false;
        }

        const queued = await allMutations();
        if (sessionCleared || sessionGeneration !== registrationGeneration
            || state?.sync?.token || state?.installation_id !== registrationInstallationId) return false;
        const preserveLocalWorkspace = queued.length > 0 || state.local_dirty === true;
        state.sync = { token: result.token, device: result.device };
        state.sync_authorization_required = false;
        state.sync_registration_blocked = false;
        state.last_sync_error = null;
        if (!preserveLocalWorkspace) applyBootstrap(result.bootstrap);
        await writeState({ allowSessionChange: true });
        window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
        if (preserveLocalWorkspace) {
            await sync();
        }
        return true;
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

        if (storageAvailable && !state.sync?.token && navigator.onLine && window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC !== true) {
            registerPrimaryDevice().then(registered => {
                if (registered && state?.sync?.token) sync().catch(() => {});
            }).catch(() => {
                // Device registration is best-effort; local work remains available.
            });
        }

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
        queueAttachment,
        queueAttachments,
        loadAttachments,
        getAttachmentFile,
        syncAttachments,
        pair,
        registerPrimaryDevice,
        sync,
        assessLogout,
        clearLocalSession,
        resolveConflict,
        refreshBootstrap,
        persist: writeState,
        pending,
        ready,
    };

    let reconnectAttempt = null;

    async function resumeSync() {
        if (reconnectAttempt) return reconnectAttempt;

        reconnectAttempt = (async () => {
            // Android may suspend an installed PWA while offline. In that case
            // the browser's one-time "online" event can be missed; retry when
            // the app is shown or focused again.
            await ready;
            if (sessionCleared || !navigator.onLine) return;

            if (!state?.sync?.token) {
                try {
                    await registerPrimaryDevice();
                } catch {
                    // Keep local work queued; another resume/reconnect can retry.
                }
            }

            if (state?.sync?.token) await sync();
        })();

        try {
            await reconnectAttempt;
        } finally {
            reconnectAttempt = null;
        }
    }

    function handleResume() {
        resumeSync().catch(() => {
            if (state && !sessionCleared) {
                state.last_sync_error = 'Synchronization failed; queued work will retry.';
                writeState().catch(() => {});
            }
        });
    }

    window.addEventListener('online', handleResume);
    // A normal first-load pageshow is startup, not a resume. Only retry here
    // when the browser restores this document from the back-forward cache.
    window.addEventListener('pageshow', event => {
        if (event.persisted) handleResume();
    });
    window.addEventListener('focus', handleResume);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') handleResume();
    });
    boot().catch(() => {
        // Never leave callers waiting forever if an unexpected boot error escapes.
        resolveReady(state || blank());
        window.dispatchEvent(new CustomEvent('zazu:offline-ready', { detail: api }));
    });
})();
