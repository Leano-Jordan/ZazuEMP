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
    const PENDING_DEVICE_REVOCATION_PREFIX = 'zazu-pending-device-revocation:';
    const STATE = 'state';
    const QUEUE = 'mutations';
    let state = null;
    let durableStateSnapshot = null;
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
        workspace_profile: null,
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
        sync_registration_consent: false,
    });

    function localStorageWriteError(error, fallbackMessage) {
        const quotaExceeded = error?.name === 'QuotaExceededError'
            || error?.code === 22
            || error?.code === 1014;
        const message = quotaExceeded
            ? 'This device is low on browser storage. Zazu could not save this change. The failed transaction was not committed and existing saved records were not deliberately removed. Do not clear this browser site data; reconnect and arrange a business backup before adding more files.'
            : (error?.message || fallbackMessage);
        const failure = new Error(message);
        failure.name = quotaExceeded ? 'QuotaExceededError' : (error?.name || 'StorageError');

        if (quotaExceeded) {
            storageAvailable = false;
            window.dispatchEvent(new CustomEvent('zazu:offline-storage-error', {
                detail: { category: 'quota', message },
            }));
        }

        return failure;
    }

    let dbPromise = null;
    let sessionCleared = false;
    let sessionGeneration = 0;
    let sessionChannel = null;
    let primaryRegistrationAttempt = null;
    let pendingDeviceRevocationAttempt = null;

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

    function cloneWorkspaceState(value) {
        if (typeof structuredClone === 'function') return structuredClone(value);
        return JSON.parse(JSON.stringify(value));
    }

    function sameWorkspaceValue(left, right) {
        return JSON.stringify(left) === JSON.stringify(right);
    }

    function replaceWorkspaceState(nextState) {
        if (!state || typeof state !== 'object') {
            state = cloneWorkspaceState(nextState);
            return state;
        }

        for (const key of Object.keys(state)) delete state[key];
        Object.assign(state, nextState);
        return state;
    }

    function workspaceRecordIdentity(record) {
        const identity = record?.local_id ?? record?.server_id ?? record?.id;
        return identity === null || identity === undefined || identity === ''
            ? null
            : String(identity);
    }

    function mergeWorkspaceChanges(latest, baseline, proposed, { allowSessionChange = false } = {}) {
        const merged = cloneWorkspaceState(latest || {});
        const beforeState = baseline || {};
        const proposedState = proposed || {};
        const keys = new Set([...Object.keys(beforeState), ...Object.keys(proposedState)]);

        const sessionFields = new Set([
            'sync',
            'installation_id',
            'sync_authorization_required',
            'sync_registration_blocked',
            'sync_registration_consent',
        ]);

        for (const key of keys) {
            const before = beforeState[key];
            const after = proposedState[key];
            if (sameWorkspaceValue(before, after)) continue;

            const current = latest?.[key];
            if (key === 'version') {
                merged[key] = cloneWorkspaceState(after);
                continue;
            }
            if (allowSessionChange && sessionFields.has(key)) {
                merged[key] = cloneWorkspaceState(after);
                continue;
            }
            if (Array.isArray(before) && Array.isArray(after) && Array.isArray(current)) {
                const beforeEntries = new Map();
                const afterEntries = new Map();
                const currentEntries = new Map();
                const hasStableIdentity = [before, after, current].every(collection => {
                    const entries = collection.map(item => [workspaceRecordIdentity(item), item]);
                    if (entries.some(([identity]) => identity === null)
                        || new Set(entries.map(([identity]) => identity)).size !== entries.length) {
                        return false;
                    }
                    return true;
                });

                if (!hasStableIdentity) {
                    if (!sameWorkspaceValue(current, before) && !sameWorkspaceValue(current, after)) {
                        throw new Error('This workspace changed in another tab (' + key + '). Refresh the workspace and retry your change.');
                    }
                    merged[key] = cloneWorkspaceState(after);
                    continue;
                }

                for (const item of before) beforeEntries.set(workspaceRecordIdentity(item), item);
                for (const item of after) afterEntries.set(workspaceRecordIdentity(item), item);
                for (const item of current) currentEntries.set(workspaceRecordIdentity(item), item);

                const result = [...current];
                for (const [identity, beforeItem] of beforeEntries) {
                    const afterItem = afterEntries.get(identity);
                    const currentItem = currentEntries.get(identity);
                    const currentIndex = result.findIndex(item => workspaceRecordIdentity(item) === identity);

                    if (afterItem === undefined) {
                        if (currentItem === undefined) continue;
                        if (!sameWorkspaceValue(currentItem, beforeItem)) {
                            throw new Error('This record changed in another tab. Refresh it before deleting or editing it.');
                        }
                        result.splice(currentIndex, 1);
                        currentEntries.delete(identity);
                        continue;
                    }

                    if (sameWorkspaceValue(beforeItem, afterItem)) continue;
                    if (currentItem !== undefined && sameWorkspaceValue(currentItem, afterItem)) continue;
                    if (currentItem === undefined || !sameWorkspaceValue(currentItem, beforeItem)) {
                        throw new Error('This record changed in another tab. Refresh it before applying your edit.');
                    }

                    result[currentIndex] = cloneWorkspaceState(afterItem);
                    currentEntries.set(identity, afterItem);
                }

                for (const [identity, afterItem] of afterEntries) {
                    if (beforeEntries.has(identity)) continue;
                    const currentItem = currentEntries.get(identity);
                    if (currentItem !== undefined) {
                        if (!sameWorkspaceValue(currentItem, afterItem)) {
                            throw new Error('A record with this local identity was added in another tab. Refresh the workspace and retry.');
                        }
                        continue;
                    }
                    result.push(cloneWorkspaceState(afterItem));
                    currentEntries.set(identity, afterItem);
                }

                merged[key] = result;
                continue;
            }

            if (!sameWorkspaceValue(current, before) && !sameWorkspaceValue(current, after)) {
                throw new Error('This workspace changed in another tab (' + key + '). Refresh the workspace and retry your change.');
            }
            merged[key] = cloneWorkspaceState(after);
        }

        return merged;
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
        const baseline = durableStateSnapshot || cloneWorkspaceState(stateToWrite || blank());
        let db;
        try {
            db = await openDb();
        } catch (error) {
            throw localStorageWriteError(error, 'Local offline storage could not be opened; this change was not saved.');
        }
        return new Promise((resolve, reject) => {
            let tx;
            try {
                tx = db.transaction(STATE, 'readwrite');
            } catch (error) {
                reject(localStorageWriteError(error, 'Local offline state transaction could not start; this change was not saved.'));
                return;
            }
            const store = tx.objectStore(STATE);
            const current = store.get('current');
            let sessionMismatch = false;
            let mergeError = null;
            let mergedState = null;

            current.onsuccess = () => {
                const persistedState = current.result || blank();
                const persistedToken = persistedState?.sync?.token || null;
                if (!allowSessionChange && persistedToken !== expectedToken) {
                    sessionMismatch = true;
                    tx.abort();
                    return;
                }

                try {
                    mergedState = mergeWorkspaceChanges(persistedState, baseline, stateToWrite, { allowSessionChange });
                    store.put(mergedState, 'current');
                } catch (error) {
                    mergeError = error;
                    tx.abort();
                }
            };
            current.onerror = () => tx.abort();
            tx.oncomplete = () => {
                replaceWorkspaceState(mergedState);
                durableStateSnapshot = cloneWorkspaceState(mergedState);
                sessionChannel?.postMessage({ type: 'workspace-updated' });
                resolve();
            };
            tx.onerror = () => reject(mergeError || localStorageWriteError(tx.error, 'Local offline state write failed; this change was not saved.'));
            tx.onabort = () => reject(sessionMismatch
                ? new Error('The offline device session changed before local state could be saved.')
                : localStorageWriteError(tx.error, 'Local offline state write was aborted; this change was not saved.'));
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

        // The Laravel bootstrap endpoint returns direct records (id/local_id),
        // while older sync envelopes wrap records as { record, server_id }.
        // Accept both contracts so a fresh device actually receives its workspace.
        const normalizeRecord = (item) => {
            const record = item?.record && typeof item.record === 'object' ? item.record : item;
            const serverId = item?.server_id ?? record?.server_id ?? record?.id ?? null;

            return {
                ...(record || {}),
                local_id: item?.local_id ?? record?.local_id ?? null,
                revision: Number(item?.revision ?? record?.revision ?? 1),
                ...(serverId !== null ? { id: serverId, server_id: serverId } : {}),
            };
        };
        const normalizeList = (items) => (items || []).map(normalizeRecord);

        state.business = bootstrap.business || state.business;
        if (Object.prototype.hasOwnProperty.call(bootstrap, 'workspace_profile')) {
            state.workspace_profile = bootstrap.workspace_profile;
        }
        const has = (key) => Object.prototype.hasOwnProperty.call(bootstrap, key);
        if (has('catalogue') || has('capabilities')) state.catalogue = normalizeList(bootstrap.catalogue ?? bootstrap.capabilities);
        if (has('suppliers')) state.suppliers = normalizeList(bootstrap.suppliers);
        if (has('purchase_orders')) state.purchase_orders = bootstrap.purchase_orders.map((item) => ({
            ...normalizeRecord(item),
            items: normalizeList(item.items),
        }));
        if (has('invoices')) state.invoices = bootstrap.invoices.map((item) => {
            const record = normalizeRecord(item);

            // The sync bootstrap wraps invoice fields under "record"; payments
            // are nested in that record too. Prefer the outer collection when
            // a direct-response variant supplies it, otherwise preserve and
            // normalise the nested collection instead of replacing it with [].
            return {
                ...record,
                payments: normalizeList(item.payments ?? record.payments),
            };
        });
        if (has('expenses')) state.expenses = normalizeList(bootstrap.expenses);
        if (has('inventory_items')) state.inventory_items = bootstrap.inventory_items.map((item) => ({
            ...normalizeRecord(item),
            movements: normalizeList(item.record?.movements ?? item.movements),
        }));
        if (has('assets')) state.assets = bootstrap.assets.map((item) => ({
            ...normalizeRecord(item),
            allocations: normalizeList(item.record?.allocations ?? item.allocations),
        }));
        if (has('costs')) state.costs = normalizeList(bootstrap.costs);

        // Current Laravel bootstrap uses top-level customers/events/quotes.
        // Partial registration responses must not erase existing local data.
        if (has('customers')) state.customers = normalizeList(bootstrap.customers);
        if (has('events') || has('jobs')) state.jobs = normalizeList(bootstrap.events ?? bootstrap.jobs);
        if (has('quotes')) state.quotes = normalizeList(bootstrap.quotes);
        if (has('preparations')) state.preparations = normalizeList(bootstrap.preparations);

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

            const record = normalizeRecord(item);
            const list = state[target];
            const index = list.findIndex((entry) =>
                (record.local_id && entry.local_id === record.local_id)
                || (record.server_id && Number(entry.id) === Number(record.server_id))
            );
            if (index >= 0) list[index] = record;
            else list.push(record);
        }

        state.version = bootstrap.version || new Date().toISOString();
    }

    function compareMutationOrder(a, b) {
        const aOrder = Number(a?.queue_order || 0);
        const bOrder = Number(b?.queue_order || 0);
        if (aOrder > 0 && bOrder > 0 && aOrder !== bOrder) return aOrder - bOrder;

        const createdOrder = String(a?.created_at || '').localeCompare(String(b?.created_at || ''));
        if (createdOrder !== 0) return createdOrder;
        if (aOrder !== bOrder) return aOrder - bOrder;
        return String(a?.id || '').localeCompare(String(b?.id || ''));
    }

    async function pending() {
        const db = await openDb();
        return new Promise((resolve, reject) => {
            const request = db.transaction(QUEUE).objectStore(QUEUE).index('status').getAll('pending');
            request.onsuccess = () => resolve((request.result || []).sort(compareMutationOrder));
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

    async function hasRetryablePendingAttachments() {
        if (!ACTIVE_BUSINESS_ID) return false;
        const records = await readAttachmentRecords();
        return records.some(record =>
            record.status === 'pending'
            && record.retryable === true
            && Number(record.business_id) === ACTIVE_BUSINESS_ID
            && Number.isSafeInteger(Number(record.event_id))
            && Number(record.event_id) > 0
            && record.file instanceof Blob
        );
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
                if (expectedSessionGeneration !== null
                    && (sessionCleared || sessionGeneration !== expectedSessionGeneration)) {
                    tx.abort();
                    reject(new Error('The device session changed before the local attachment could be saved.'));
                    return;
                }
                tx.objectStore(ATTACHMENT_STORE).put(record);
                tx.oncomplete = () => {
                    if (expectedSessionGeneration !== null
                        && (sessionCleared || sessionGeneration !== expectedSessionGeneration)) {
                        reject(new Error('The device session changed while the local attachment was being saved.'));
                        return;
                    }
                    resolve();
                };
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
        const queueSessionGeneration = sessionGeneration;
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

        let db;
        try {
            db = await openAttachmentDb();
        } catch (error) {
            throw localStorageWriteError(error, 'Local attachment storage could not be opened; these files were not saved.');
        }
        try {
            if (sessionCleared || sessionGeneration !== queueSessionGeneration) {
                throw new Error('The device session changed before these attachments could be saved. Reload Zazu and try again.');
            }
            await new Promise((resolve, reject) => {
                let tx;
                try {
                    tx = db.transaction(ATTACHMENT_STORE, 'readwrite');
                } catch (error) {
                    reject(localStorageWriteError(error, 'The attachment batch transaction could not start; these files were not saved.'));
                    return;
                }
                if (sessionCleared || sessionGeneration !== queueSessionGeneration) {
                    tx.abort();
                    reject(new Error('The device session changed before these attachments could be saved. Reload Zazu and try again.'));
                    return;
                }
                const store = tx.objectStore(ATTACHMENT_STORE);
                for (const record of records) store.put(record);
                tx.oncomplete = () => {
                    if (sessionCleared || sessionGeneration !== queueSessionGeneration) {
                        reject(new Error('The device session changed while these attachments were being saved. Reload Zazu before continuing.'));
                        return;
                    }
                    resolve();
                };
                tx.onerror = () => reject(localStorageWriteError(tx.error, 'The complete attachment batch could not be saved.'));
                tx.onabort = () => reject(localStorageWriteError(tx.error, 'The attachment batch save was aborted.'));
            });
        } finally {
            db.close();
        }

        if (sessionCleared || sessionGeneration !== queueSessionGeneration) {
            throw new Error('The device session changed while these attachments were being saved. Reload Zazu before continuing.');
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
        const reconciliationGeneration = sessionGeneration;
        const jobs = Array.isArray(state?.jobs) ? state.jobs : [];
        if (!jobs.length || sessionCleared || ACTIVE_BUSINESS_ID < 1) return 0;

        const records = await readAttachmentRecords();
        if (sessionCleared || sessionGeneration !== reconciliationGeneration) return 0;
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
            if (sessionCleared || sessionGeneration !== reconciliationGeneration) return 0;
            await new Promise((resolve, reject) => {
                const tx = db.transaction(ATTACHMENT_STORE, 'readwrite');
                if (sessionCleared || sessionGeneration !== reconciliationGeneration) {
                    tx.abort();
                    reject(new Error('The device session changed before attachment links could be reconciled.'));
                    return;
                }
                const store = tx.objectStore(ATTACHMENT_STORE);
                for (const record of updates) store.put(record);
                tx.oncomplete = () => {
                    if (sessionCleared || sessionGeneration !== reconciliationGeneration) {
                        reject(new Error('The device session changed while attachment links were being reconciled.'));
                        return;
                    }
                    resolve();
                };
                tx.onerror = () => reject(tx.error || new Error('Offline attachment job links could not be updated.'));
                tx.onabort = () => reject(tx.error || new Error('Offline attachment job-link update was aborted.'));
            });
        } finally {
            db.close();
        }

        return sessionCleared || sessionGeneration !== reconciliationGeneration ? 0 : updates.length;
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
                        retryable: true,
                        last_error: 'Upload paused. Zazu will retry when the connection is available.',
                    }, uploadSessionGeneration);
                    break;
                }

                if (sessionCleared || sessionGeneration !== uploadSessionGeneration
                    || state?.sync?.token !== uploadToken) break;

                if (!response.ok) {
                    await recordSyncAuthorizationFailure(response);
                    const result = await response.json().catch(() => ({}));
                    const retryable = [408, 425, 429].includes(response.status) || response.status >= 500;
                    await writeAttachmentRecord({
                        ...record,
                        attempts: Number(record.attempts || 0) + 1,
                        retryable,
                        last_error: result.message || 'The server did not accept this attachment. It remains saved on this phone.',
                    }, uploadSessionGeneration);
                    if ([401, 403, 429].includes(response.status)) break;
                    continue;
                }

                let result;
                try {
                    result = await response.json();
                } catch {
                    // The server may have committed the upload before a proxy or
                    // interrupted response corrupted its JSON acknowledgement.
                    // Keep the original bytes and idempotency key so retry can
                    // safely recover the existing server record instead of
                    // creating a duplicate or stranding the local attachment.
                    await writeAttachmentRecord({
                        ...record,
                        attempts: Number(record.attempts || 0) + 1,
                        retryable: true,
                        last_error: 'The upload response could not be verified. This file remains saved on this device and will retry safely.',
                    }, uploadSessionGeneration);
                    continue;
                }
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
                        retryable: true,
                        last_error: 'The server response did not match this file and Work record. The original file remains queued for a safe retry.',
                    }, uploadSessionGeneration);
                    continue;
                }

                const persisted = await writeAttachmentRecord({
                    ...record,
                    status: 'synced',
                    attachment: result.attachment,
                    attempts: Number(record.attempts || 0) + 1,
                    retryable: false,
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
            if (!sessionCleared && navigator.onLine && state?.sync?.token) {
                try {
                    if (await hasRetryablePendingAttachments()) scheduleSyncRetry();
                } catch {
                    // A later foreground/resume cycle can inspect the durable queue again.
                }
            }
        }

        return { uploaded, pending: await countPendingAttachments() };
    }

    async function loadAttachments(eventId, localEventId = null) {
        await ready;
        const readSessionGeneration = sessionGeneration;
        const readSessionToken = state?.sync?.token || null;
        const targetEventId = Number(eventId);
        const targetLocalEventId = String(localEventId || '').trim();
        const hasServerIdentity = Number.isSafeInteger(targetEventId) && targetEventId > 0;
        if (!hasServerIdentity && !/^[a-zA-Z0-9_-]{1,120}$/.test(targetLocalEventId)) return [];

        if (hasServerIdentity && navigator.onLine && readSessionToken && !sessionCleared) {
            try {
                await syncAttachments();
                if (sessionCleared || sessionGeneration !== readSessionGeneration
                    || state?.sync?.token !== readSessionToken) {
                    return [];
                }
                const response = await api('/api/sync/attachments?event_id=' + encodeURIComponent(targetEventId));
                if (sessionCleared || sessionGeneration !== readSessionGeneration
                    || state?.sync?.token !== readSessionToken) {
                    return [];
                }
                if (response.ok) {
                    const result = await response.json();
                    if (sessionCleared || sessionGeneration !== readSessionGeneration
                        || state?.sync?.token !== readSessionToken) {
                        return [];
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

        const attachmentRecords = await readAttachmentRecords();
        if (sessionCleared || sessionGeneration !== readSessionGeneration) return [];

        return attachmentRecords.filter(record =>
            Number(record.business_id) === ACTIVE_BUSINESS_ID
            && (hasServerIdentity
                ? Number(record.event_id) === targetEventId
                : String(record.event_local_id || '') === targetLocalEventId)
        ).sort((a, b) => String(a.created_at || '').localeCompare(String(b.created_at || '')));
    }

    async function getAttachmentFile(idempotencyKey) {
        const readGeneration = sessionGeneration;
        if (sessionCleared) throw new Error('The local device session has ended. Reload Zazu before opening attachments.');
        const record = await readAttachmentRecord(String(idempotencyKey || ''));
        if (sessionCleared || sessionGeneration !== readGeneration) {
            throw new Error('The device session changed while this file was opening. Reload Zazu before continuing.');
        }
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
            durableStateSnapshot = cloneWorkspaceState(state);
            storageAvailable = true;
            if (broadcast) sessionChannel?.postMessage({ type: 'clear-session' });
        } catch (error) {
            sessionCleared = wasCleared;
            throw error;
        }
    }

    function pendingDeviceRevocationKey() {
        return PENDING_DEVICE_REVOCATION_PREFIX + ACTIVE_BUSINESS_ID;
    }

    function readPendingDeviceRevocation() {
        if (ACTIVE_BUSINESS_ID < 1) return null;
        try {
            const marker = JSON.parse(localStorage.getItem(pendingDeviceRevocationKey()) || 'null');
            return marker
                && Number(marker.business_id) === ACTIVE_BUSINESS_ID
                && typeof marker.installation_id === 'string'
                && /^[0-9a-f-]{36}$/i.test(marker.installation_id)
                ? marker : null;
        } catch {
            return null;
        }
    }

    function rememberPendingDeviceRevocation(installationId) {
        if (ACTIVE_BUSINESS_ID < 1 || typeof installationId !== 'string'
            || !/^[0-9a-f-]{36}$/i.test(installationId)) return false;
        try {
            localStorage.setItem(pendingDeviceRevocationKey(), JSON.stringify({
                business_id: ACTIVE_BUSINESS_ID,
                installation_id: installationId,
                requested_at: new Date().toISOString(),
            }));
            return true;
        } catch {
            return false;
        }
    }

    async function retryPendingDeviceRevocation() {
        const marker = readPendingDeviceRevocation();
        if (!marker) return { ok: true, reason: 'not_pending' };
        if (pendingDeviceRevocationAttempt) return pendingDeviceRevocationAttempt;
        if (!navigator.onLine) return { ok: false, reason: 'offline' };

        const attempt = (async () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) return { ok: false, reason: 'missing_session' };

            const controller = new AbortController();
            const timeout = window.setTimeout(() => controller.abort(), 1800);
            try {
                const response = await fetch('/settings/sync/devices/revoke-installation', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ installation_id: marker.installation_id }),
                    cache: 'no-store',
                    signal: controller.signal,
                });

                if (!response.ok) {
                    return {
                        ok: false,
                        reason: response.status === 403 ? 'owner_required'
                            : response.status === 404 ? 'business_unavailable' : 'server_rejected',
                    };
                }

                const result = await response.json().catch(() => ({}));
                if (result.revoked !== true) return { ok: false, reason: 'unconfirmed' };

                // Reuse the same non-secret installation identity only after the
                // owner-authenticated revocation has been confirmed server-side.
                if (!state?.installation_id) {
                    state.installation_id = marker.installation_id;
                    await writeState({ allowSessionChange: true });
                }
                localStorage.removeItem(pendingDeviceRevocationKey());
                return { ok: true, reason: null };
            } catch {
                return { ok: false, reason: controller.signal.aborted ? 'timeout' : 'unreachable' };
            } finally {
                window.clearTimeout(timeout);
            }
        })();

        pendingDeviceRevocationAttempt = attempt;
        try {
            return await attempt;
        } finally {
            if (pendingDeviceRevocationAttempt === attempt) pendingDeviceRevocationAttempt = null;
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
        const installationId = state?.installation_id || null;
        await clearStoredWorkspace(true);

        // Local logout must succeed even without the server. When immediate
        // revocation cannot reach the host, retain only the non-secret device
        // identity so the next owner session can revoke it through its web login.
        const result = await revokeDeviceToken(token);
        const queuedForRetry = !result.ok && rememberPendingDeviceRevocation(installationId);
        return { ...result, queued_for_retry: queuedForRetry };
    }


    async function refreshConflicts() {
        await ready;
        if (sessionCleared || !state?.sync?.token || !navigator.onLine) {
            return null;
        }

        const previousState = state;
        try {
            const response = await api('/api/sync/conflicts?status=open');
            const result = await response.json().catch(() => ({}));
            if (!response.ok || !Array.isArray(result.conflicts)) {
                return null;
            }

            state = {
                ...state,
                conflicts: result.conflicts,
            };
            await writeState();
            return state.conflicts;
        } catch (error) {
            state = previousState;
            return null;
        }
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

        const remotePayload = result.conflict?.remote_payload || {};
        const remoteRevision = Number(remotePayload.revision || 0);
        const isStaleRevisionConflict = result.conflict?.conflict_type === 'stale_record_version';
        if (retryWithNewMutationId && isStaleRevisionConflict && (!result.rebase?.new_mutation_id || remoteRevision < 1)) {
            throw new Error('The latest server revision could not be confirmed. Keep the local change queued and retry conflict resolution.');
        }

        const identityTargets = {
            customer: 'customers',
            service: 'catalogue',
            job: 'jobs',
            quote: 'quotes',
            preparation: 'preparations',
            supplier: 'suppliers',
            purchase_order: 'purchase_orders',
            invoice: 'invoices',
            expense: 'expenses',
            inventory_item: 'inventory_items',
            asset: 'assets',
            event_cost: 'costs',
        };

        const db = await openDb();
        await new Promise((resolve, reject) => {
            const tx = db.transaction([STATE, QUEUE], 'readwrite');
            const store = tx.objectStore(QUEUE);
            const stateStore = tx.objectStore(STATE);
            const all = store.getAll();
            const currentState = stateStore.get('current');
            let sessionMismatch = false;
            let persistedState = null;
            let allReady = false;
            let stateReady = false;

            const finishResolution = () => {
                if (!allReady || !stateReady || sessionMismatch) return;
                const mutation = (all.result || []).find(item => Number(item.conflict_id) === id);
                const nextState = {
                    ...(persistedState || state || blank()),
                    conflicts: [...((persistedState || state || blank()).conflicts || [])],
                };

                if (mutation && shouldRetry) {
                    const retry = { ...mutation, status: 'pending' };
                    if (retryWithNewMutationId) {
                        // Preserve queue order so a rebased predecessor remains
                        // ahead of any later edits to the same local aggregate.
                        retry.id = result.rebase?.new_mutation_id || crypto.randomUUID();
                        if (isStaleRevisionConflict) {
                            retry.payload = {
                                ...retry.payload,
                                expected_revision: remoteRevision,
                            };
                            delete retry.payload.predecessor_mutation_id;
                        }
                    }
                    if (retry.id !== mutation.id) {
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

                const target = identityTargets[mutation?.entity_type];
                const localId = mutation?.payload?.local_id || mutation?.entity_id;
                const parentAsset = mutation?.entity_type === 'asset_allocation'
                    ? (nextState.assets || []).find(asset => (asset.allocations || []).some(item => item.local_id === localId))
                    : null;
                const list = mutation?.entity_type === 'asset_allocation'
                    ? (parentAsset?.allocations || [])
                    : (target ? (nextState[target] || []) : []);
                const index = list.findIndex(item => item.local_id === localId);
                if (index >= 0 && shouldDiscard && remotePayload.deleted) {
                    list.splice(index, 1);
                    if (parentAsset) {
                        parentAsset.allocations = list;
                    } else if (target) {
                        nextState[target] = list;
                    }
                } else if (index >= 0 && remotePayload.record && typeof remotePayload.record === 'object') {
                    const current = list[index];
                    if (shouldDiscard) {
                        const serverRecord = remotePayload.record;
                        list[index] = {
                            ...current,
                            ...serverRecord,
                            id: current.server_id ?? current.id ?? serverRecord.id,
                            server_id: current.server_id ?? current.id ?? serverRecord.id,
                            local_id: localId,
                            revision: remoteRevision || current.revision || 1,
                            pending_sync: false,
                        };
                        if (mutation.entity_type === 'customer' && Array.isArray(serverRecord.contacts)) {
                            const primary = serverRecord.contacts.find(item => item.is_primary) || serverRecord.contacts[0] || {};
                            list[index].contact_name = primary.name || null;
                            list[index].phone = primary.phone || null;
                            list[index].email = primary.email || null;
                        } else if (mutation.entity_type === 'job' && serverRecord.customer_id) {
                            const linkedCustomer = (nextState.customers || []).find(item =>
                                Number(item.server_id ?? item.id) === Number(serverRecord.customer_id)
                            );
                            list[index].customer_local_id = linkedCustomer?.local_id || null;
                            list[index].customer_name = linkedCustomer?.name || serverRecord.customer_name || null;
                        } else if (mutation.entity_type === 'quote' && serverRecord.event_id) {
                            const linkedJob = (nextState.jobs || []).find(item =>
                                Number(item.server_id ?? item.id) === Number(serverRecord.event_id)
                            );
                            list[index].event_local_id = linkedJob?.local_id || null;
                        } else if (mutation.entity_type === 'purchase_order' && serverRecord.supplier_id) {
                            const linkedSupplier = (nextState.suppliers || []).find(item =>
                                Number(item.server_id ?? item.id) === Number(serverRecord.supplier_id)
                            );
                            list[index].supplier_local_id = linkedSupplier?.local_id || null;
                        }
                    } else if (retryWithNewMutationId) {
                        list[index] = { ...current, revision: remoteRevision };
                    }
                    if (parentAsset) {
                        parentAsset.allocations = list;
                    } else if (target) {
                        nextState[target] = list;
                    }
                }

                const relatedAsset = remotePayload.related;
                if (relatedAsset?.entity_type === 'asset') {
                    const asset = (nextState.assets || []).find(item => item.local_id === relatedAsset.local_id);
                    if (asset) asset.revision = Number(relatedAsset.revision || asset.revision || 1);
                }

                const pendingCount = store.index('status').count('pending');
                pendingCount.onsuccess = () => {
                    nextState.conflicts = nextState.conflicts.map(conflict =>
                        Number(conflict.id) === id
                            ? { ...conflict, status: 'resolved', remote_payload: remotePayload }
                            : conflict
                    );
                    nextState.local_dirty = pendingCount.result > 0;
                    stateStore.put(nextState, 'current');
                    tx.oncomplete = () => {
                        replaceWorkspaceState(nextState);
                        durableStateSnapshot = cloneWorkspaceState(nextState);
                        window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
                        resolve();
                    };
                };
                pendingCount.onerror = () => tx.abort();
            };

            currentState.onsuccess = () => {
                persistedState = currentState.result || state || blank();
                if (sessionCleared || (persistedState.sync?.token || null) !== conflictSessionToken) {
                    sessionMismatch = true;
                    tx.abort();
                    return;
                }
                stateReady = true;
                finishResolution();
            };
            currentState.onerror = () => tx.abort();
            all.onsuccess = () => {
                allReady = true;
                finishResolution();
            };
            all.onerror = () => tx.abort();

            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error || new Error('Offline conflict resolution could not be saved locally.'));
            tx.onabort = () => reject(sessionMismatch
                ? new Error('The device session changed before conflict resolution could be saved.')
                : (tx.error || new Error('Offline conflict resolution transaction was aborted.')));
        });

        if (shouldRetry) {
            sync().catch(() => {
                if (state) state.last_sync_error = 'The saved change is queued for retry when Zazu reconnects.';
            });
        }

        return result;
    }

    sessionChannel?.addEventListener('message', event => {
        if (event.data?.type === 'workspace-updated') {
            if (sessionCleared) return;
            Promise.resolve().then(async () => {
                await ready;
                if (sessionCleared) return;
                const nextState = await readState();
                if (!nextState) return;
                if ((nextState.sync?.token || null) !== (state?.sync?.token || null)) return;
                replaceWorkspaceState(nextState);
                durableStateSnapshot = cloneWorkspaceState(nextState);
                window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
            }).catch(() => {
                // IndexedDB remains authoritative; the next local write re-reads it transactionally.
            });
            return;
        }

        if (event.data?.type === 'clear-session') {
            sessionCleared = true;
            sessionGeneration++;
            activeSyncController?.abort();
            state = blank();
            durableStateSnapshot = cloneWorkspaceState(state);
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

            replaceWorkspaceState(nextState);
            durableStateSnapshot = cloneWorkspaceState(nextState);
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


    function localRevisionForEntity(workspace, entityType, localId) {
        const collections = {
            customer: 'customers',
            job: 'jobs',
            quote: 'quotes',
            service: 'catalogue',
            preparation: 'preparations',
            supplier: 'suppliers',
            purchase_order: 'purchase_orders',
            invoice: 'invoices',
            expense: 'expenses',
            inventory_item: 'inventory_items',
            asset: 'assets',
            event_cost: 'costs',
        };
        const collection = collections[entityType];
        if (entityType === 'asset_allocation') {
            const allocation = (workspace?.assets || [])
                .flatMap(asset => asset.allocations || [])
                .find(item => item.local_id === localId);
            return Number(allocation?.revision || 1);
        }
        const record = collection
            ? (workspace?.[collection] || []).find(item => item.local_id === localId)
            : null;
        return Number(record?.revision || 1);
    }

    async function queueMutation(entityType, operation, payload, nextState = null) {
        await ready;
        if (sessionCleared) {
            throw new Error('This device session has been signed out. Sign in and pair the device again before making offline changes.');
        }
        if (!storageAvailable) {
            throw new Error('Local offline storage is unavailable; this change was not saved.');
        }
        const proposedState = nextState || state;
        const expectedToken = proposedState?.sync?.token || null;
        const baseline = durableStateSnapshot || cloneWorkspaceState(state || blank());
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
            throw localStorageWriteError(error, 'Local offline storage is unavailable; this change was not saved.');
        }

        if (sessionCleared) {
            throw new Error('This device session has been signed out. Sign in and pair the device again before making offline changes.');
        }

        return new Promise((resolve, reject) => {
            let tx;
            try {
                tx = db.transaction([STATE, QUEUE], 'readwrite');
            } catch (error) {
                storageAvailable = false;
                reject(localStorageWriteError(error, 'Local offline queue transaction could not start; this change was not saved.'));
                return;
            }
            const stateStore = tx.objectStore(STATE);
            const queueStore = tx.objectStore(QUEUE);
            const currentState = stateStore.get('current');
            const queuedRequest = queueStore.getAll();
            let sessionMismatch = false;
            let mergeError = null;
            let stateToPersist = null;
            let currentReady = false;
            let queueReady = false;

            const prepareMutation = () => {
                if (!currentReady || !queueReady || sessionMismatch || mergeError) return;
                const queueItems = queuedRequest.result || [];
                const hasLegacyOrder = queueItems.some(item => !Number.isSafeInteger(Number(item.queue_order)) || Number(item.queue_order) < 1);
                if (hasLegacyOrder) {
                    const orderedQueue = [...queueItems].sort(compareMutationOrder);
                    orderedQueue.forEach((item, index) => {
                        item.queue_order = index + 1;
                        queueStore.put(item);
                    });
                }
                const nextQueueOrder = queueItems.reduce((maximum, item) => Math.max(maximum, Number(item.queue_order) || 0), 0) + 1;
                mutation.queue_order = nextQueueOrder;

                try {
                    stateToPersist = mergeWorkspaceChanges(
                        currentState.result || blank(),
                        baseline,
                        proposedState
                    );
                    stateToPersist.local_dirty = true;
                } catch (error) {
                    mergeError = error;
                    tx.abort();
                    return;
                }

                const queuedForEntity = queueItems
                    .filter(item => ['pending', 'failed'].includes(item.status)
                        && item.entity_type === entityType
                        && String(item.entity_id) === String(mutation.entity_id))
                    .sort(compareMutationOrder);
                const predecessor = queuedForEntity[queuedForEntity.length - 1] || null;

                if (['update', 'upsert'].includes(operation)) {
                    if (predecessor) {
                        mutationPayload.predecessor_mutation_id = predecessor.id;
                    } else {
                        mutationPayload.expected_revision = localRevisionForEntity(stateToPersist, entityType, mutationPayload.local_id);
                    }
                }

                queueStore.put(mutation);
                stateStore.put(stateToPersist, 'current');
            };

            currentState.onsuccess = () => {
                const persistedToken = currentState.result?.sync?.token || null;
                if (sessionCleared || persistedToken !== expectedToken) {
                    sessionMismatch = true;
                    tx.abort();
                    return;
                }
                currentReady = true;
                prepareMutation();
            };
            queuedRequest.onsuccess = () => {
                queueReady = true;
                prepareMutation();
            };
            currentState.onerror = () => tx.abort();
            queuedRequest.onerror = () => tx.abort();
            tx.oncomplete = () => {
                replaceWorkspaceState(stateToPersist);
                durableStateSnapshot = cloneWorkspaceState(stateToPersist);
                sessionChannel?.postMessage({ type: 'workspace-updated' });
                if (navigator.onLine) scheduleSyncRetry();
                resolve(mutation);
            };
            tx.onerror = () => {
                if (sessionMismatch || mergeError) return;
                storageAvailable = false;
                reject(localStorageWriteError(tx.error, 'Local offline queue transaction failed; this change was not saved.'));
            };
            tx.onabort = () => {
                if (sessionMismatch) {
                    reject(new Error('The device session changed before this offline change could be saved. Refresh the workspace and retry.'));
                    return;
                }
                if (mergeError) {
                    replaceWorkspaceState(currentState.result || state || blank());
                    durableStateSnapshot = cloneWorkspaceState(state);
                    window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
                    reject(mergeError);
                    return;
                }
                storageAvailable = false;
                reject(localStorageWriteError(tx.error, 'Local offline queue transaction was aborted; this change was not saved.'));
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
        const recorded = new Map((Array.isArray(result.recorded) ? result.recorded : []).map(item => [item.id, item]));
        const db = await openDb();

        // Queue acknowledgement, server identity reconciliation, conflict
        // recording and local_dirty recalculation must commit together. If the
        // browser is terminated after the server accepts a mutation, a retry
        // must either find the original queue entry or the fully reconciled
        // local record; there must be no half-acknowledged intermediate state.
        await new Promise((resolve, reject) => {
            const tx = db.transaction([QUEUE, STATE], 'readwrite');
            const queueStore = tx.objectStore(QUEUE);
            const stateStore = tx.objectStore(STATE);
            const currentStateRequest = stateStore.get('current');
            const queueRequest = queueStore.getAll();
            let nextState = null;
            let currentStateReady = false;
            let queueReady = false;
            let sessionMismatch = false;

            const identityTargets = {
                customer: 'customers',
                service: 'catalogue',
                job: 'jobs',
                quote: 'quotes',
                preparation: 'preparations',
                supplier: 'suppliers',
                purchase_order: 'purchase_orders',
                invoice: 'invoices',
                expense: 'expenses',
                inventory_item: 'inventory_items',
                asset: 'assets',
                event_cost: 'costs',
            };

            const findLocalRecord = (workspace, entityType, localId, mutation) => {
                const target = identityTargets[entityType];
                if (target) {
                    return (workspace[target] || []).find(item => item.local_id === localId) || null;
                }

                const payload = mutation.payload?.record || {};
                if (entityType === 'payment') {
                    const invoice = (workspace.invoices || []).find(item =>
                        item.local_id === payload.invoice_local_id
                    );
                    return invoice?.payments?.find(item => item.local_id === localId) || null;
                }

                if (entityType === 'inventory_movement') {
                    const inventoryItem = (workspace.inventory_items || []).find(item =>
                        item.local_id === payload.inventory_item_local_id
                    );
                    return inventoryItem?.movements?.find(item => item.local_id === localId) || null;
                }

                if (entityType === 'asset_allocation') {
                    const asset = (workspace.assets || []).find(item => item.local_id === payload.asset_local_id);
                    return asset?.allocations?.find(item => item.local_id === localId) || null;
                }

                return null;
            };

            const reconcile = () => {
                if (!currentStateReady || !queueReady || sessionMismatch) return;

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

                const queueById = new Map((queueRequest.result || []).map(item => [item.id, item]));

                for (const mutation of mutations) {
                    const remote = recorded.get(mutation.id);
                    if (!remote) continue;

                    if (remote.status === 'applied' || remote.status === 'discarded') {
                        queueStore.delete(mutation.id);
                        queueById.delete(mutation.id);
                    } else if (remote.error && remote.conflict_id) {
                        // A deterministic domain/identity rejection is terminal
                        // until the operator resolves its explicit conflict record.
                        // Persist the failed queue record and conflict together.
                        const failedMutation = {
                            ...mutation,
                            status: 'failed',
                            server_sequence: remote.sequence,
                            server_status: remote.status,
                            conflict_id: remote.conflict_id,
                            last_error: remote.error,
                        };
                        queueStore.put(failedMutation);
                        queueById.set(mutation.id, failedMutation);

                        nextState.conflicts = [
                            ...nextState.conflicts.filter(item => Number(item.id) !== Number(remote.conflict_id)),
                            {
                                ...(remote.conflict || {}),
                                id: remote.conflict_id,
                                mutation_id: mutation.id,
                                entity_type: mutation.entity_type,
                                entity_id: mutation.entity_id,
                                conflict_type: remote.conflict_type || remote.conflict?.conflict_type || 'domain_rejection',
                                message: remote.conflict?.message || remote.error,
                                status: 'open',
                            },
                        ];
                    } else if (remote.error && remote.retryable) {
                        // Infrastructure/unexpected failures must remain retryable.
                        // Do not strand them as failed records without a resolvable conflict.
                        const retryableMutation = {
                            ...mutation,
                            status: 'pending',
                            server_sequence: remote.sequence,
                            server_status: remote.status,
                            last_error: remote.error,
                        };
                        queueStore.put(retryableMutation);
                        queueById.set(mutation.id, retryableMutation);
                    } else if (remote.error) {
                        // Fail closed if a future server response adds an error
                        // without either a conflict record or retryable disposition.
                        const failedMutation = {
                            ...mutation,
                            status: 'failed',
                            server_sequence: remote.sequence,
                            server_status: remote.status,
                            last_error: remote.error,
                        };
                        queueStore.put(failedMutation);
                        queueById.set(mutation.id, failedMutation);
                    } else {
                        const unconfirmedMutation = {
                            ...mutation,
                            server_sequence: remote.sequence,
                            server_status: remote.status,
                            last_error: remote.error || 'The server did not confirm this offline change',
                        };
                        queueStore.put(unconfirmedMutation);
                        queueById.set(mutation.id, unconfirmedMutation);
                    }
                }

                const relatedReconciliations = [];

                for (const mutation of mutations) {
                    const remote = recorded.get(mutation.id);
                    if (remote?.status !== 'applied') continue;

                    const identity = remote.identity;
                    if (identity) {
                        const localId = identity.local_id || mutation.payload?.local_id;
                        const record = findLocalRecord(nextState, identity.entity_type, localId, mutation);
                        if (record) {
                            record.id = Number(identity.server_id);
                            record.server_id = Number(identity.server_id);
                            record.local_id = localId;
                            record.revision = Number(identity.revision || record.revision || 1);
                        }
                    }

                    const relatedIdentities = Array.isArray(remote.related_identities)
                        ? remote.related_identities
                        : (remote.related_identity ? [remote.related_identity] : []);
                    for (const related of relatedIdentities) {
                        const target = identityTargets[related.entity_type];
                        const relatedRecord = target
                            ? (nextState[target] || []).find(item => item.local_id === related.local_id)
                            : null;
                        if (relatedRecord) {
                            relatedRecord.revision = Number(related.revision || relatedRecord.revision || 1);
                            relatedReconciliations.push({
                                entityType: related.entity_type,
                                localId: related.local_id,
                                record: relatedRecord,
                                sourceMutation: mutation,
                            });
                        }
                    }
                }

                const remainingMutations = [...queueById.values()]
                    .filter(item => item.status === 'pending')
                    .sort(compareMutationOrder);
                const openConflicts = nextState.conflicts.filter(conflict => conflict.status === 'open');

                for (const related of relatedReconciliations) {
                    if (related.record?.pending_sync !== true) continue;

                    const sourcePayload = related.sourceMutation.payload?.record || {};
                    const stillPending = remainingMutations.some(item => {
                        const payload = item.payload?.record || {};
                        const localId = payload.local_id || item.entity_id;

                        if (item.entity_type === related.entityType && localId === related.localId) return true;

                        if (related.entityType === 'purchase_order') {
                            return item.entity_type === 'purchase_receipt'
                                && payload.purchase_order_local_id === related.localId;
                        }

                        if (related.entityType === 'inventory_item') {
                            if (item.entity_type === 'inventory_movement'
                                && payload.inventory_item_local_id === related.localId) return true;

                            return related.sourceMutation.entity_type === 'purchase_receipt'
                                && item.entity_type === 'purchase_receipt'
                                && payload.purchase_order_local_id === sourcePayload.purchase_order_local_id;
                        }

                        return false;
                    }) || openConflicts.some(conflict => {
                        if (related.entityType === 'purchase_order') {
                            return conflict.entity_type === 'purchase_receipt';
                        }

                        if (related.entityType === 'inventory_item') {
                            return related.sourceMutation.entity_type === 'purchase_receipt'
                                ? conflict.entity_type === 'purchase_receipt'
                                : conflict.entity_type === 'inventory_movement';
                        }

                        return conflict.entity_type === related.entityType
                            && conflict.entity_id === related.localId;
                    });

                    if (!stillPending) {
                        related.record.pending_sync = false;

                        if (related.entityType === 'purchase_order') {
                            for (const line of related.record.items || []) {
                                if (line.pending_sync === true) line.pending_sync = false;
                            }
                        }

                        if (related.entityType === 'inventory_item') {
                            for (const movement of related.record.movements || []) {
                                if (movement.pending_sync === true) movement.pending_sync = false;
                            }
                        }
                    }
                }

                for (const mutation of mutations) {
                    const remote = recorded.get(mutation.id);
                    if (!['applied', 'discarded'].includes(remote?.status)) continue;

                    const localId = mutation.payload?.local_id || mutation.entity_id;
                    if (!localId) continue;

                    const sameRecordStillPending = remainingMutations.some(item =>
                        item.entity_type === mutation.entity_type
                        && (item.payload?.local_id || item.entity_id) === localId
                    );
                    if (sameRecordStillPending) continue;

                    const record = findLocalRecord(nextState, mutation.entity_type, localId, mutation);
                    if (record?.pending_sync === true) record.pending_sync = false;
                }

                nextState.local_dirty = remainingMutations.length > 0;
                stateStore.put(nextState, 'current');
            };

            currentStateRequest.onsuccess = () => {
                currentStateReady = true;
                reconcile();
            };
            queueRequest.onsuccess = () => {
                queueReady = true;
                reconcile();
            };
            currentStateRequest.onerror = () => tx.abort();
            queueRequest.onerror = () => tx.abort();
            tx.oncomplete = () => {
                if (!nextState) {
                    reject(new Error('Offline queue acknowledgement completed without reconciling its workspace state.'));
                    return;
                }
                if (!sessionCleared) {
                    replaceWorkspaceState(nextState);
                    durableStateSnapshot = cloneWorkspaceState(nextState);
                }
                resolve();
            };
            tx.onerror = () => reject(tx.error || new Error('Offline queue acknowledgement failed.'));
            tx.onabort = () => reject(sessionMismatch
                ? new Error('The device session changed before sync acknowledgement could be applied.')
                : (tx.error || new Error('Offline queue acknowledgement was aborted.')));
        });

        if (sessionCleared || !state?.sync?.token) return;
        window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
    }

    async function applyBootstrapIfQueueClean(bootstrap, expectedToken) {
        const db = await openDb();

        return new Promise((resolve, reject) => {
            const tx = db.transaction([STATE, QUEUE], 'readwrite');
            const queueStore = tx.objectStore(QUEUE);
            const pendingRequest = queueStore.index('status').getAll('pending');
            const failedRequest = queueStore.index('status').getAll('failed');
            const stateRequest = tx.objectStore(STATE).get('current');
            let pendingReady = false;
            let failedReady = false;
            let stateReady = false;
            let applied = false;

            const reconcile = () => {
                if (!pendingReady || !failedReady || !stateReady || applied) return;

                const persistedState = stateRequest.result;
                const hasOpenConflict = (persistedState?.conflicts || []).some(conflict => conflict.status === 'open');

                // A failed mutation remains part of the local working set until
                // its conflict is explicitly resolved. A remote bootstrap can
                // replace whole collections (invoices, inventory, assets, etc.),
                // so it must not erase locally saved records while a failed item
                // or open conflict still needs operator attention.
                if (
                    pendingRequest.result?.length
                    || failedRequest.result?.length
                    || hasOpenConflict
                    || sessionCleared
                ) return;

                if (!persistedState || persistedState.sync?.token !== expectedToken) return;

                replaceWorkspaceState(persistedState);
                applyBootstrap(bootstrap);
                tx.objectStore(STATE).put(state, 'current');
                applied = true;
            };

            pendingRequest.onsuccess = () => {
                pendingReady = true;
                reconcile();
            };
            failedRequest.onsuccess = () => {
                failedReady = true;
                reconcile();
            };
            stateRequest.onsuccess = () => {
                stateReady = true;
                reconcile();
            };
            pendingRequest.onerror = () => tx.abort();
            failedRequest.onerror = () => tx.abort();
            stateRequest.onerror = () => tx.abort();
            tx.oncomplete = () => {
                if (applied) durableStateSnapshot = cloneWorkspaceState(state);
                resolve(applied);
            };
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

            const deliveryQuery = new URLSearchParams();
            for (const mutation of mutations) {
                const sequence = Number(mutation.delivery_sequence || 0);
                if (Number.isSafeInteger(sequence) && sequence > 0) {
                    deliveryQuery.append('delivery_sequences[]', String(sequence));
                }
            }
            const deliveryQueryString = deliveryQuery.toString();
            const bootstrapPath = deliveryQueryString
                ? '/api/sync/bootstrap?' + deliveryQueryString
                : '/api/sync/bootstrap';
            const bootstrap = await api(bootstrapPath);
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
            || (!state?.sync?.token && !state?.sync_registration_consent)
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
                if (!state?.sync?.token && state?.sync_registration_consent) {
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
                    const [queuedMutations, retryableAttachments] = await Promise.all([
                        pending(),
                        hasRetryablePendingAttachments(),
                    ]);
                    if (queuedMutations.length || retryableAttachments) {
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
        const normalizedCode = String(pairingCode ?? '').replace(/\D/g, '');
        if (!/^\d{12}$/.test(normalizedCode)) {
            throw new Error('Enter the complete 12-digit phone pairing code.');
        }

        const response = await fetch('/api/sync/provision', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                pairing_code: normalizedCode,
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

    async function registerPrimaryDevice({ explicitConsent = false } = {}) {
        await ready;
        if (sessionCleared || !storageAvailable || !ACTIVE_BUSINESS_ID) return false;

        if (explicitConsent) {
            const reauthorizing = state?.sync_authorization_required === true;
            const firstApproval = state?.sync_registration_consent !== true;
            state.sync_registration_consent = true;
            if (reauthorizing) {
                state.sync = null;
                state.sync_authorization_required = false;
                state.sync_registration_blocked = false;
            }
            if (firstApproval || reauthorizing) {
                await writeState({ allowSessionChange: true });
                window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
            }
        }

        // Finish any previous logout revocation before issuing a fresh token
        // for this installation. The pending-revocation marker retains only the
        // non-secret installation identity; the trusted bearer remains in the
        // business-scoped IndexedDB workspace until the device is revoked or cleared.
        if (readPendingDeviceRevocation()) {
            const revocation = await retryPendingDeviceRevocation();
            if (!revocation.ok) {
                state.last_sync_error = 'The previous device credential still needs revocation. Keep this device online and retry from Settings.';
                await writeState({ allowSessionChange: true }).catch(() => {});
                window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
                return false;
            }
        }

        // A trusted sync credential is granted only after an owner explicitly
        // approves this browser/device in Settings. Previously every fresh web
        // login silently registered another long-lived device token.
        if (state?.sync_registration_consent !== true || state?.sync_registration_blocked) return false;
        if (primaryRegistrationAttempt) return primaryRegistrationAttempt;

        const attempt = registerPrimaryDeviceOnce();
        primaryRegistrationAttempt = attempt;
        try {
            const registered = await attempt;
            if (!registered && state?.sync_registration_consent === true
                && !state?.sync_registration_blocked && !state?.sync_authorization_required
                && navigator.onLine && (await pending()).length > 0) {
                scheduleSyncRetry();
            }
            return registered;
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
            state.sync_registration_blocked = [403, 409].includes(response.status);
            state.last_sync_error = response.status === 403
                ? 'Only the business owner can register a trusted offline device. Ask the owner to pair this device.'
                : response.status === 409
                    ? 'This installation is registered to a different business workspace. The owner must resolve the device registration before these changes can sync.'
                    : 'Zazu could not register this device (HTTP ' + response.status + '). Your local work remains saved and registration will retry automatically.';
            await writeState().catch(() => {});
            window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
            return false;
        }

        const result = await response.json();
        if (Number(result.device?.business_id || 0) !== ACTIVE_BUSINESS_ID) {
            state.sync_registration_blocked = true;
            state.last_sync_error = 'The registered device belongs to a different business workspace. The owner must resolve the device registration before these changes can sync.';
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

    async function getStorageProtectionStatus() {
        const manager = navigator.storage;
        if (!manager) {
            return {
                status: 'unsupported',
                requestSupported: false,
                usage: null,
                quota: null,
            };
        }

        let persisted = null;
        let usage = null;
        let quota = null;

        if (typeof manager.persisted === 'function') {
            try {
                persisted = await manager.persisted();
            } catch {
                persisted = null;
            }
        }

        if (typeof manager.estimate === 'function') {
            try {
                const estimate = await manager.estimate();
                usage = Number.isFinite(estimate?.usage) ? estimate.usage : null;
                quota = Number.isFinite(estimate?.quota) ? estimate.quota : null;
            } catch {
                usage = null;
                quota = null;
            }
        }

        const requestSupported = typeof manager.persist === 'function';
        const status = persisted === true
            ? 'granted'
            : requestSupported
                ? (persisted === false ? 'not_granted' : 'unknown')
                : 'unsupported';

        return { status, requestSupported, usage, quota };
    }

    function requestStorageProtection() {
        const manager = navigator.storage;
        if (!manager || typeof manager.persist !== 'function') {
            return getStorageProtectionStatus().then(status => ({
                ...status,
                requestResult: 'unsupported',
            }));
        }

        // Call persist() before the first await so a trusted button activation
        // is still available in browsers that require user activation.
        let request;
        try {
            request = manager.persist();
        } catch (error) {
            return getStorageProtectionStatus().then(status => ({
                ...status,
                requestResult: 'error',
                error: error?.message || 'The browser storage request failed.',
            }));
        }

        return Promise.resolve(request).then(async granted => ({
            ...await getStorageProtectionStatus(),
            requestResult: granted === true ? 'granted' : 'denied',
        })).catch(async error => ({
            ...await getStorageProtectionStatus(),
            requestResult: 'error',
            error: error?.message || 'The browser storage request failed.',
        }));
    }

    async function boot() {
        try {
            state = (await readState()) || blank();
            durableStateSnapshot = cloneWorkspaceState(state);
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

        if (storageAvailable && navigator.onLine && readPendingDeviceRevocation()) {
            retryPendingDeviceRevocation().catch(() => {});
        }

        if (storageAvailable && !state.sync?.token && state.sync_registration_consent === true
            && navigator.onLine && window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC !== true) {
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
        getStorageProtectionStatus,
        requestStorageProtection,
        sync,
        assessLogout,
        clearLocalSession,
        resolveConflict,
        refreshConflicts,
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

            if (!state?.sync?.token && state?.sync_registration_consent === true) {
                try {
                    await registerPrimaryDevice();
                } catch {
                    // Retry only after the owner has explicitly trusted this device.
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
