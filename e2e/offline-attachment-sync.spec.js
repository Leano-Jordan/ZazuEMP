import { test, expect } from '@playwright/test';

const DEMO_EMAIL = process.env.ZAZU_DEMO_EMAIL || 'demo@zazu.local';
const DEMO_PASSWORD = process.env.ZAZU_DEMO_PASSWORD || 'password';
const DEVICE_TOKEN = 'offline-attachment-e2e-token';

async function login(page) {
    await page.goto('/?auth=login', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-auth-modal]')).toBeVisible();
    await page.getByLabel('Username or email').fill(DEMO_EMAIL);
    await page.locator('#auth_password').fill(DEMO_PASSWORD);
    await page.locator('[data-auth-submit]').click();
    await expect(page).toHaveURL(/\/dashboard$/);
}

test('Work attachments persist offline, upload on reconnect and cache remote files', async ({ page }, testInfo) => {
    test.setTimeout(45_000);
    test.skip(testInfo.project.name !== 'chromium', 'Attachment reconnect contract runs once; responsive browser coverage is separate.');

    await login(page);
    await page.goto('/work', { waitUntil: 'domcontentloaded' });

    const jobRow = page.locator('[data-zazu-job-row]').filter({ hasText: 'Mokoena Family Celebration' }).first();
    await expect(jobRow).toBeVisible();
    await jobRow.locator('[data-zazu-inspector-open]').first().click();
    await page.getByRole('tab', { name: 'Financial Ledger' }).click();
    await expect(page.locator('[data-zazu-tab-panel="finance"]')).toBeVisible();
    await page.getByRole('link', { name: 'Open job record' }).click();
    await expect(page.locator('[data-zazu-offline-attachment-form]')).toBeVisible();

    const attachmentForm = page.locator('[data-zazu-offline-attachment-form]');
    const eventId = Number(await attachmentForm.getAttribute('data-zazu-event-id'));
    expect(eventId).toBeGreaterThan(0);

    await page.evaluate(async token => {
        await window.ZazuOffline.ready;
        const state = window.ZazuOffline.getState();
        state.sync = { token, device: { id: 987654, business_id: Number(document.body.dataset.zazuBusinessId) } };
        state.local_dirty = false;
        await window.ZazuOffline.persist();
    }, DEVICE_TOKEN);

    const requests = { uploads: 0, key: null, eventId: null, wrongJobDownloads: 0 };
    const remoteKey = '00000000-0000-4000-8000-000000000099';

    await page.route('**/api/sync/**', async route => {
        const request = route.request();
        const url = new URL(request.url());
        const authorization = request.headers()['authorization'];
        expect(authorization).toBe('Bearer ' + DEVICE_TOKEN);

        if (url.pathname === '/api/sync/pull') {
            await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ mutations: [] }) });
            return;
        }

        if (url.pathname === '/api/sync/bootstrap') {
            await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({}) });
            return;
        }

        if (url.pathname === '/api/sync/acknowledge') {
            await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ acknowledged: true }) });
            return;
        }

        if (url.pathname === '/api/sync/attachments' && request.method() === 'POST') {
            requests.uploads++;
            const body = request.postData() || '';
            const requestKey = body.match(/name="idempotency_key"\r\n\r\n([^\r\n]+)/)?.[1] || null;
            if (!requests.key) requests.key = requestKey;
            requests.eventId = Number(body.match(/name="event_id"\r\n\r\n([^\r\n]+)/)?.[1] || 0);
            expect(requestKey).toMatch(/^[0-9a-f-]{36}$/i);
            expect(requests.eventId).toBe(eventId);
            const mismatchedResponse = body.includes('mismatched-response.txt');
            expect(body).toContain(mismatchedResponse ? 'mismatched-response.txt' : 'offline-evidence.txt');

            await route.fulfill({
                status: 201,
                contentType: 'application/json',
                body: JSON.stringify({
                    duplicate: false,
                    attachment: {
                        id: mismatchedResponse ? 803 : 801,
                        event_id: mismatchedResponse ? eventId + 1 : eventId,
                        idempotency_key: requestKey,
                        original_name: mismatchedResponse ? 'mismatched-response.txt' : 'offline-evidence.txt',
                        mime_type: 'text/plain',
                        size: mismatchedResponse ? Buffer.byteLength('must remain pending') : Buffer.byteLength('saved on phone for later'),
                        description: '',
                        created_at: new Date().toISOString(),
                    },
                }),
            });
            return;
        }

        if (url.pathname === '/api/sync/attachments' && request.method() === 'GET') {
            await route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify({
                    attachments: [
                        {
                            id: 801,
                            event_id: eventId,
                            idempotency_key: requests.key,
                            original_name: 'offline-evidence.txt',
                            mime_type: 'text/plain',
                            size: Buffer.byteLength('saved on phone for later'),
                            description: '',
                            created_at: new Date().toISOString(),
                        },
                        {
                            id: 802,
                            event_id: eventId,
                            idempotency_key: remoteKey,
                            original_name: 'server-brief.txt',
                            mime_type: 'text/plain',
                            size: Buffer.byteLength('cached server-side file'),
                            description: 'Server copy',
                            created_at: new Date().toISOString(),
                        },
                        {
                            id: 803,
                            event_id: eventId + 1,
                            idempotency_key: '00000000-0000-4000-8000-000000000098',
                            original_name: 'wrong-job.txt',
                            mime_type: 'text/plain',
                            size: Buffer.byteLength('belongs to another Work record'),
                            description: 'Mismatched parent',
                            created_at: new Date().toISOString(),
                        },
                    ],
                }),
            });
            return;
        }

        if (url.pathname === '/api/sync/attachments/803' && request.method() === 'GET') {
            requests.wrongJobDownloads++;
            await route.fulfill({ status: 200, contentType: 'text/plain', body: 'belongs to another Work record' });
            return;
        }

        if (url.pathname === '/api/sync/attachments/802' && request.method() === 'GET') {
            await route.fulfill({
                status: 200,
                contentType: 'text/plain',
                headers: { 'Content-Disposition': 'attachment; filename="server-brief.txt"' },
                body: 'cached server-side file',
            });
            return;
        }

        await route.fulfill({ status: 404, contentType: 'application/json', body: JSON.stringify({ message: 'Unexpected sync request' }) });
    });

    await page.context().setOffline(true);
    await attachmentForm.locator('input[name="files[]"]').setInputFiles({
        name: 'offline-evidence.txt',
        mimeType: 'text/plain',
        buffer: Buffer.from('saved on phone for later'),
    });
    await attachmentForm.getByRole('button', { name: 'Add to job' }).click();
    await expect(attachmentForm.locator('[data-zazu-offline-attachment-status]')).toContainText('saved on this phone');

    const pending = await page.evaluate(async id => window.ZazuOffline.loadAttachments(id), eventId);
    expect(pending).toHaveLength(1);
    expect(pending[0].status).toBe('pending');
    expect(pending[0].business_id).toBeGreaterThan(0);
    const pendingFile = await page.evaluate(async key => {
        const file = await window.ZazuOffline.getAttachmentFile(key);
        return { name: file.name, text: await file.text() };
    }, pending[0].idempotency_key);
    expect(pendingFile.name).toBe('offline-evidence.txt');
    expect(pendingFile.text).toBe('saved on phone for later');

    await page.context().setOffline(false);
    await page.evaluate(async () => window.ZazuOffline.syncAttachments());
    await expect.poll(() => requests.uploads).toBe(1);
    await expect.poll(async () => page.evaluate(async id => {
        const records = await window.ZazuOffline.loadAttachments(id);
        return records.find(record => record.original_name === 'offline-evidence.txt')?.status || null;
    }, eventId)).toBe('synced');
    expect(requests.key).toBe(pending[0].idempotency_key);
    expect(requests.eventId).toBe(eventId);

    await page.evaluate(async id => {
        await window.ZazuOffline.queueAttachment(
            id,
            new File(['must remain pending'], 'mismatched-response.txt', { type: 'text/plain' })
        );
        await window.ZazuOffline.syncAttachments();
    }, eventId);
    await expect.poll(() => requests.uploads).toBe(2);
    const rejectedConfirmation = await page.evaluate(async id => {
        const records = await window.ZazuOffline.loadAttachments(id);
        const record = records.find(item => item.original_name === 'mismatched-response.txt');
        return record ? { status: record.status, last_error: record.last_error } : null;
    }, eventId);
    expect(rejectedConfirmation.status).toBe('pending');
    expect(rejectedConfirmation.last_error).toContain('did not match');

    const cached = await page.evaluate(async id => {
        const records = await window.ZazuOffline.loadAttachments(id);
        const keys = records.map(record => record.idempotency_key);
        const uploaded = await window.ZazuOffline.getAttachmentFile(keys.find(key => key !== '00000000-0000-4000-8000-000000000099'));
        const downloaded = await window.ZazuOffline.getAttachmentFile('00000000-0000-4000-8000-000000000099');
        return {
            records: records.map(record => ({ name: record.original_name, status: record.status })),
            uploadedText: await uploaded.text(),
            downloadedText: await downloaded.text(),
        };
    }, eventId);

    expect(cached.records).toContainEqual({ name: 'offline-evidence.txt', status: 'synced' });
    expect(cached.records).toContainEqual({ name: 'server-brief.txt', status: 'synced' });
    expect(cached.records.some(record => record.name === 'wrong-job.txt')).toBe(false);
    expect(requests.wrongJobDownloads).toBe(0);
    expect(cached.uploadedText).toBe('saved on phone for later');
    expect(cached.downloadedText).toBe('cached server-side file');
});


test('reconciles an offline-created Work attachment after its job receives a server ID', async ({ page }, testInfo) => {
    test.setTimeout(30_000);
    test.skip(testInfo.project.name !== 'chromium', 'Offline Work identity reconciliation runs once.');

    await login(page);
    await page.goto('/work', { waitUntil: 'domcontentloaded' });

    const jobRow = page.locator('[data-zazu-job-row]').filter({ hasText: 'Mokoena Family Celebration' }).first();
    await expect(jobRow).toBeVisible();
    await jobRow.locator('[data-zazu-inspector-open]').first().click();
    await page.getByRole('tab', { name: 'Financial Ledger' }).click();
    await expect(page.locator('[data-zazu-tab-panel="finance"]')).toBeVisible();
    await page.getByRole('link', { name: 'Open job record' }).click();
    await expect(page.locator('[data-zazu-offline-attachment-form]')).toBeVisible();

    const eventId = Number(await page.locator('[data-zazu-offline-attachment-form]').getAttribute('data-zazu-event-id'));
    const localEventId = 'offline-job-' + crypto.randomUUID();
    const deviceToken = 'offline-job-attachment-e2e-token';

    const fixture = await page.evaluate(async ({ eventId, localEventId, deviceToken }) => {
        await window.ZazuOffline.ready;
        const businessId = Number(document.body.dataset.zazuBusinessId);
        const state = window.ZazuOffline.getState();
        state.sync = { token: deviceToken, device: { id: 987655, business_id: businessId } };
        state.jobs = [
            ...(state.jobs || []),
            { local_id: localEventId, id: eventId, server_id: eventId, name: 'Synced offline-created job' },
        ];
        state.local_dirty = false;
        await window.ZazuOffline.persist();

        const database = await new Promise((resolve, reject) => {
            const request = indexedDB.open('zazu-phone-attachments-business-' + businessId);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error || new Error('Could not open phone attachment storage.'));
        });

        const key = crypto.randomUUID();
        const file = new File(['saved before job sync completed'], 'offline-created-job.txt', { type: 'text/plain' });
        const record = {
            business_id: businessId,
            event_id: null,
            event_local_id: localEventId,
            idempotency_key: key,
            original_name: file.name,
            mime_type: file.type,
            size: file.size,
            description: '',
            status: 'pending',
            file,
            created_at: new Date().toISOString(),
            attempts: 0,
            last_error: null,
        };

        await new Promise((resolve, reject) => {
            const tx = database.transaction('files', 'readwrite');
            tx.objectStore('files').put(record);
            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error || new Error('Could not seed the pending offline attachment.'));
            tx.onabort = () => reject(tx.error || new Error('Offline attachment fixture transaction aborted.'));
        });
        database.close();
        return { key, businessId };
    }, { eventId, localEventId, deviceToken });

    let uploadedEventId = null;
    let uploadedKey = null;
    await page.route('**/api/sync/attachments', async route => {
        const request = route.request();
        expect(request.method()).toBe('POST');
        expect(request.headers()['authorization']).toBe('Bearer ' + deviceToken);
        const body = request.postData() || '';
        uploadedEventId = Number(body.match(/name="event_id"\r\n\r\n([^\r\n]+)/)?.[1] || 0);
        uploadedKey = body.match(/name="idempotency_key"\r\n\r\n([^\r\n]+)/)?.[1] || null;
        expect(body).toContain('offline-created-job.txt');

        await route.fulfill({
            status: 201,
            contentType: 'application/json',
            body: JSON.stringify({
                duplicate: false,
                attachment: {
                    id: 901,
                    event_id: eventId,
                    idempotency_key: fixture.key,
                    original_name: 'offline-created-job.txt',
                    mime_type: 'text/plain',
                    size: Buffer.byteLength('saved before job sync completed'),
                    description: '',
                    created_at: new Date().toISOString(),
                },
            }),
        });
    });

    const result = await page.evaluate(async () => window.ZazuOffline.syncAttachments());
    expect(result.uploaded).toBe(1);
    expect(result.pending).toBe(0);
    expect(uploadedEventId).toBe(eventId);
    expect(uploadedKey).toBe(fixture.key);
    const fileText = await page.evaluate(async key => (await window.ZazuOffline.getAttachmentFile(key)).text(), fixture.key);
    expect(fileText).toBe('saved before job sync completed');
});


test('operator creates an offline Work job, attaches a file from its directory row, then syncs it', async ({ page }, testInfo) => {
    test.setTimeout(60_000);
    test.skip(testInfo.project.name !== 'chromium', 'The full offline attachment journey runs once in Chromium.');

    await login(page);
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
    await page.goto('/customers/create', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('form[data-zazu-offline-entity="customer"]')).toBeVisible();
    await page.goto('/customers', { waitUntil: 'domcontentloaded' });
    await page.goto('/work', { waitUntil: 'domcontentloaded' });
    await page.goto('/work/create', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#new-job-form')).toBeVisible();

    await page.context().setOffline(true);
    await page.goto('/customers/create', { waitUntil: 'domcontentloaded' });
    const customerForm = page.locator('form[data-zazu-offline-entity="customer"]');
    await customerForm.locator('input[name="name"]').fill('Attachment journey customer');
    await customerForm.locator('input[name="primary_contact_name"]').fill('Test contact');
    await customerForm.locator('input[name="primary_contact_phone"]').fill('0123456789');
    await customerForm.getByRole('button', { name: 'Save customer' }).click();
    await expect(customerForm.getByText('Saved on this device. It will sync automatically when Zazu reconnects.')).toBeVisible();

    const customerLocalId = await page.evaluate(() =>
        window.ZazuOffline.getState().customers.find(item => item.name === 'Attachment journey customer')?.local_id || null
    );
    expect(customerLocalId).toBeTruthy();

    await page.goto('/work/create', { waitUntil: 'domcontentloaded' });
    const customerSelect = page.locator('#customer_id');
    await expect(customerSelect.locator('option[value="local:' + customerLocalId + '"]')).toHaveCount(1);
    await customerSelect.selectOption('local:' + customerLocalId);
    await page.locator('#new-job-form label.zazu-choice-card:has(input[name="event_type"])').first().click();
    await page.locator('#new-job-form label.zazu-service-choice:has(input[name="services[]"])').first().click();
    await page.locator('#new-job-form input[name="name"]').fill('Attachment journey offline job');
    await page.locator('#new-job-form input[name="event_date"]').fill('2026-11-20');
    await page.locator('#new-job-form').getByRole('button', { name: /Create job/ }).click();
    await expect(page.getByText('Saved on this device. It will sync automatically when Zazu reconnects.')).toBeVisible();

    const localJob = await page.evaluate(() =>
        window.ZazuOffline.getState().jobs.find(item => item.name === 'Attachment journey offline job') || null
    );
    expect(localJob?.local_id).toBeTruthy();
    expect(localJob?.server_id).toBeNull();

    await page.goto('/work', { waitUntil: 'domcontentloaded' });
    const localRow = page.locator('[data-zazu-local-record="job"]').filter({ hasText: 'Attachment journey offline job' });
    await expect(localRow).toBeVisible();
    const fileInput = localRow.locator('input[type="file"][data-zazu-local-job-attachments]');
    await expect(fileInput).toHaveAttribute('data-zazu-local-job-attachments', localJob.local_id);
    await fileInput.setInputFiles({
        name: 'operator-job-brief.txt',
        mimeType: 'text/plain',
        buffer: Buffer.from('captured through the actual Work directory action'),
    });
    await expect(localRow.getByRole('button', { name: '1 files saved locally for Attachment journey offline job' })).toBeVisible();
    await expect(localRow.getByRole('button', { name: 'operator-job-brief.txt' })).toBeVisible();
    const localDownload = page.waitForEvent('download');
    await localRow.getByRole('button', { name: 'operator-job-brief.txt' }).click();
    expect((await localDownload).suggestedFilename()).toBe('operator-job-brief.txt');

    const locallySaved = await page.evaluate(async localId => {
        const records = await window.ZazuOffline.loadAttachments(null, localId);
        const file = records[0] ? await window.ZazuOffline.getAttachmentFile(records[0].idempotency_key) : null;
        return {
            records: records.map(record => ({
                event_id: record.event_id,
                event_local_id: record.event_local_id,
                status: record.status,
                name: record.original_name,
            })),
            fileText: file ? await file.text() : null,
        };
    }, localJob.local_id);
    expect(locallySaved.records).toEqual([{
        event_id: null,
        event_local_id: localJob.local_id,
        status: 'pending',
        name: 'operator-job-brief.txt',
    }]);
    expect(locallySaved.fileText).toBe('captured through the actual Work directory action');

    const deviceToken = 'offline-work-attachment-journey-token';
    await page.evaluate(async token => {
        const state = window.ZazuOffline.getState();
        state.sync = { token, device: { id: 987656, business_id: Number(document.body.dataset.zazuBusinessId) } };
        state.local_dirty = false;
        await window.ZazuOffline.persist();
    }, deviceToken);

    const uploaded = { count: 0, eventId: null, key: null };
    const pushed = { customer: null, job: null };

    await page.route('**/api/sync/push', async route => {
        const request = route.request();
        expect(request.method()).toBe('POST');
        expect(request.headers()['authorization']).toBe('Bearer ' + deviceToken);
        const body = request.postDataJSON();
        const recorded = (body.mutations || []).map((mutation, index) => {
            const serverId = mutation.entity_type === 'job' ? 9901
                : mutation.entity_type === 'customer' ? 9900
                    : 9910 + index;
            const identity = {
                entity_type: mutation.entity_type,
                local_id: mutation.payload?.local_id || mutation.entity_id,
                server_id: serverId,
            };
            if (mutation.entity_type === 'job') pushed.job = identity;
            if (mutation.entity_type === 'customer') pushed.customer = identity;
            return {
                id: mutation.id,
                sequence: index + 1,
                status: 'applied',
                applied: true,
                error: null,
                identity,
            };
        });

        await route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({ recorded, applied: recorded.map(item => item.id) }),
        });
    });

    await page.route('**/api/sync/pull**', async route => {
        await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ mutations: [] }) });
    });

    await page.route('**/api/sync/bootstrap', async route => {
        await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({}) });
    });

    await page.route('**/api/sync/attachments', async route => {
        const request = route.request();
        expect(request.method()).toBe('POST');
        expect(request.headers()['authorization']).toBe('Bearer ' + deviceToken);
        const body = request.postData() || '';
        uploaded.count++;
        uploaded.eventId = Number(body.match(/name="event_id"\r\n\r\n([^\r\n]+)/)?.[1] || 0);
        uploaded.key = body.match(/name="idempotency_key"\r\n\r\n([^\r\n]+)/)?.[1] || null;
        expect(body).toContain('operator-job-brief.txt');
        await route.fulfill({
            status: 201,
            contentType: 'application/json',
            body: JSON.stringify({
                duplicate: false,
                attachment: {
                    id: 990,
                    event_id: uploaded.eventId,
                    idempotency_key: uploaded.key,
                    original_name: 'operator-job-brief.txt',
                    mime_type: 'text/plain',
                    size: Buffer.byteLength('captured through the actual Work directory action'),
                    description: '',
                    created_at: new Date().toISOString(),
                },
            }),
        });
    });

    await page.context().setOffline(false);
    const unresolvedSync = await page.evaluate(() => window.ZazuOffline.syncAttachments());
    expect(unresolvedSync.uploaded).toBe(0);
    expect(unresolvedSync.pending).toBe(1);
    expect(uploaded.count).toBe(0);

    await page.evaluate(() => window.ZazuOffline.sync());
    await expect.poll(() => uploaded.count).toBe(1);
    expect(pushed.customer?.server_id).toBe(9900);
    expect(pushed.job?.server_id).toBe(9901);
    expect(uploaded.eventId).toBe(9901);
    expect(uploaded.key).toBeTruthy();

    const syncedJob = await page.evaluate(localId => {
        const job = window.ZazuOffline.getState().jobs.find(item => item.local_id === localId);
        return { id: job?.id, server_id: job?.server_id };
    }, localJob.local_id);
    expect(syncedJob).toEqual({ id: 9901, server_id: 9901 });

    const finalRecord = await page.evaluate(async key => {
        const records = await window.ZazuOffline.loadAttachments(9901);
        const record = records.find(item => item.idempotency_key === key);
        return record ? { status: record.status, name: record.original_name } : null;
    }, uploaded.key);
    expect(finalRecord).toEqual({ status: 'synced', name: 'operator-job-brief.txt' });
});

test('late attachment upload response cannot recreate local files after logout', async ({ page }, testInfo) => {
    test.setTimeout(45_000);
    test.skip(testInfo.project.name !== 'chromium', 'Attachment/logout race runs once in Chromium.');

    await login(page);
    await page.goto('/work', { waitUntil: 'domcontentloaded' });

    const jobRow = page.locator('[data-zazu-job-row]').filter({ hasText: 'Mokoena Family Celebration' }).first();
    await expect(jobRow).toBeVisible();
    await jobRow.locator('[data-zazu-inspector-open]').first().click();
    await page.getByRole('tab', { name: 'Financial Ledger' }).click();
    await expect(page.locator('[data-zazu-tab-panel="finance"]')).toBeVisible();
    await page.getByRole('link', { name: 'Open job record' }).click();
    const eventId = Number(await page.locator('[data-zazu-offline-attachment-form]').getAttribute('data-zazu-event-id'));
    expect(eventId).toBeGreaterThan(0);

    const deviceToken = 'attachment-logout-race-token';
    await page.evaluate(async token => {
        await window.ZazuOffline.ready;
        const state = window.ZazuOffline.getState();
        state.sync = { token, device: { id: 987657, business_id: Number(document.body.dataset.zazuBusinessId) } };
        await window.ZazuOffline.persist();
    }, deviceToken);

    let releaseUpload;
    const uploadGate = new Promise(resolve => { releaseUpload = resolve; });
    let uploadStarted;
    const started = new Promise(resolve => { uploadStarted = resolve; });

    await page.route('**/api/sync/attachments**', async route => {
        const request = route.request();
        const url = new URL(request.url());
        if (url.pathname === '/api/sync/attachments' && request.method() === 'POST') {
            const body = request.postData() || '';
            const key = body.match(/name="idempotency_key"\\r\\n\\r\\n([^\\r\\n]+)/)?.[1] || null;
            expect(key).toBeTruthy();
            uploadStarted();
            await uploadGate;
            await route.fulfill({
                status: 201,
                contentType: 'application/json',
                body: JSON.stringify({
                    attachment: {
                        id: 9901,
                        event_id: eventId,
                        idempotency_key: key,
                        original_name: 'late-upload.txt',
                        mime_type: 'text/plain',
                        size: Buffer.byteLength('late response'),
                        created_at: new Date().toISOString(),
                    },
                }),
            });
            return;
        }
        if (url.pathname === '/api/sync/attachments' && request.method() === 'GET') {
            await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ attachments: [] }) });
            return;
        }
        await route.fulfill({ status: 404, contentType: 'application/json', body: JSON.stringify({ message: 'Unexpected attachment request' }) });
    });

    await page.route('**/api/sync/revoke', async route => {
        await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ revoked: true }) });
    });

    await page.evaluate(async id => {
        await window.ZazuOffline.queueAttachment(
            id,
            new File(['late response'], 'late-upload.txt', { type: 'text/plain' })
        );
    }, eventId);
    await started;

    await page.evaluate(() => window.ZazuOffline.clearLocalSession());
    releaseUpload();

    await expect.poll(async () => page.evaluate(async () => {
        const businessId = Number(document.body.dataset.zazuBusinessId);
        const database = await new Promise((resolve, reject) => {
            const request = indexedDB.open('zazu-phone-attachments-business-' + businessId);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error || new Error('Could not inspect attachment storage.'));
        });
        const records = await new Promise((resolve, reject) => {
            const request = database.transaction('files', 'readonly').objectStore('files').getAll();
            request.onsuccess = () => resolve(request.result || []);
            request.onerror = () => reject(request.error || new Error('Could not read attachment storage.'));
        });
        database.close();
        return records.length;
    })).toBe(0);
});

test('re-pair invalidates the old sync session and retries queued work with the new token', async ({ page }, testInfo) => {
    test.setTimeout(45_000);
    test.skip(testInfo.project.name !== 'chromium', 'Re-pair session transition runs once in Chromium.');

    await login(page);
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });

    const businessId = await page.evaluate(() => Number(document.body.dataset.zazuBusinessId));
    await page.evaluate(async businessId => {
        await window.ZazuOffline.ready;
        const state = window.ZazuOffline.getState();
        state.sync = { token: 'old-pair-session-token', device: { id: 987658, business_id: businessId } };
        await window.ZazuOffline.persist();
        await window.ZazuOffline.queueMutation('customer', 'create', {
            local_id: 're-pair-race-customer',
            name: 'Re-pair race customer',
        });
    }, businessId);

    let releaseOldPush;
    const oldPushGate = new Promise(resolve => { releaseOldPush = resolve; });
    let oldPushStarted;
    const oldPushSeen = new Promise(resolve => { oldPushStarted = resolve; });
    let newPushSeen;
    const newPush = new Promise(resolve => { newPushSeen = resolve; });
    let mutationId = null;
    let newPushCount = 0;

    await page.route('**/api/sync/**', async route => {
        const request = route.request();
        const url = new URL(request.url());
        const token = request.headers()['authorization'];

        if (url.pathname === '/api/sync/push' && request.method() === 'POST') {
            const body = request.postDataJSON();
            mutationId = body.mutations?.[0]?.id || mutationId;
            if (token === 'Bearer old-pair-session-token') {
                oldPushStarted();
                await oldPushGate;
                await route.fulfill({
                    status: 200,
                    contentType: 'application/json',
                    body: JSON.stringify({ recorded: [{ id: mutationId, status: 'applied', applied: true, identity: null }] }),
                }).catch(() => {
                    // The client intentionally aborts this old-session request during re-pair.
                });
                return;
            }

            expect(token).toBe('Bearer new-pair-session-token');
            newPushCount++;
            newPushSeen();
            await route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify({ recorded: [{ id: mutationId, status: 'applied', applied: true, identity: null }] }),
            });
            return;
        }

        expect(token).toBe('Bearer new-pair-session-token');
        if (url.pathname === '/api/sync/pull') {
            await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ mutations: [] }) });
            return;
        }
        if (url.pathname === '/api/sync/bootstrap') {
            await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({}) });
            return;
        }
        if (url.pathname === '/api/sync/acknowledge') {
            await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ acknowledged: true }) });
            return;
        }
        await route.fulfill({ status: 404, contentType: 'application/json', body: JSON.stringify({ message: 'Unexpected sync request' }) });
    });

    await page.route('**/api/sync/provision', async route => {
        await route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({
                token: 'new-pair-session-token',
                device: { id: 987659, business_id: businessId },
                bootstrap: {},
            }),
        });
    });

    const oldSync = page.evaluate(() => window.ZazuOffline.sync().catch(() => null));
    await oldPushSeen;
    await page.evaluate(() => window.ZazuOffline.pair('123456'));
    releaseOldPush();
    await oldSync;
    await newPush;

    await expect.poll(() => newPushCount).toBe(1);
    await expect.poll(async () => page.evaluate(() => window.ZazuOffline.pending())).toHaveLength(0);
    const finalState = await page.evaluate(() => ({
        token: window.ZazuOffline.getState().sync?.token,
        dirty: window.ZazuOffline.getState().local_dirty,
    }));
    expect(finalState).toEqual({ token: 'new-pair-session-token', dirty: false });
});

test('late private attachment download cannot repopulate local files after logout', async ({ page }, testInfo) => {
    test.setTimeout(45_000);
    test.skip(testInfo.project.name !== 'chromium', 'Attachment download/logout race runs once in Chromium.');

    await login(page);
    await page.goto('/work', { waitUntil: 'domcontentloaded' });

    const jobRow = page.locator('[data-zazu-job-row]').filter({ hasText: 'Mokoena Family Celebration' }).first();
    await expect(jobRow).toBeVisible();
    await jobRow.locator('[data-zazu-inspector-open]').first().click();
    await page.getByRole('tab', { name: 'Financial Ledger' }).click();
    await expect(page.locator('[data-zazu-tab-panel="finance"]')).toBeVisible();
    await page.getByRole('link', { name: 'Open job record' }).click();
    const eventId = Number(await page.locator('[data-zazu-offline-attachment-form]').getAttribute('data-zazu-event-id'));

    const deviceToken = 'attachment-download-logout-race-token';
    await page.evaluate(async token => {
        await window.ZazuOffline.ready;
        const state = window.ZazuOffline.getState();
        state.sync = { token, device: { id: 987660, business_id: Number(document.body.dataset.zazuBusinessId) } };
        await window.ZazuOffline.persist();
    }, deviceToken);

    let releaseDownload;
    const downloadGate = new Promise(resolve => { releaseDownload = resolve; });
    let downloadStarted;
    const started = new Promise(resolve => { downloadStarted = resolve; });

    await page.route('**/api/sync/attachments**', async route => {
        const request = route.request();
        const url = new URL(request.url());
        expect(request.headers()['authorization']).toBe('Bearer ' + deviceToken);
        if (url.pathname === '/api/sync/attachments' && request.method() === 'GET') {
            await route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify({ attachments: [{
                    id: 9910,
                    event_id: eventId,
                    idempotency_key: 'server-private-download-9910',
                    original_name: 'private-brief.txt',
                    mime_type: 'text/plain',
                    size: Buffer.byteLength('private file response'),
                    description: '',
                    created_at: new Date().toISOString(),
                }] }),
            });
            return;
        }
        if (url.pathname === '/api/sync/attachments/9910' && request.method() === 'GET') {
            downloadStarted();
            await downloadGate;
            await route.fulfill({
                status: 200,
                contentType: 'text/plain',
                body: 'private file response',
            }).catch(() => {
                // Logout may end the page request before the held response is released.
            });
            return;
        }
        await route.fulfill({ status: 404, contentType: 'application/json', body: JSON.stringify({ message: 'Unexpected attachment request' }) });
    });

    await page.route('**/api/sync/revoke', async route => {
        await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ revoked: true }) });
    });

    const pendingRead = page.evaluate(id => window.ZazuOffline.loadAttachments(id), eventId);
    await started;
    await page.evaluate(() => window.ZazuOffline.clearLocalSession());
    releaseDownload();
    await pendingRead;

    await expect.poll(async () => page.evaluate(async () => {
        const businessId = Number(document.body.dataset.zazuBusinessId);
        const database = await new Promise((resolve, reject) => {
            const request = indexedDB.open('zazu-phone-attachments-business-' + businessId);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error || new Error('Could not inspect attachment storage.'));
        });
        const records = await new Promise((resolve, reject) => {
            const request = database.transaction('files', 'readonly').objectStore('files').getAll();
            request.onsuccess = () => resolve(request.result || []);
            request.onerror = () => reject(request.error || new Error('Could not read attachment storage.'));
        });
        database.close();
        return records.length;
    })).toBe(0);
});

test('re-pair in one tab replaces the stale device session in other open tabs', async ({ page, context }, testInfo) => {
    test.setTimeout(45_000);
    test.skip(testInfo.project.name !== 'chromium', 'Cross-tab device session contract runs once in Chromium.');

    await login(page);
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
    const businessId = await page.evaluate(() => Number(document.body.dataset.zazuBusinessId));
    await page.evaluate(async businessId => {
        await window.ZazuOffline.ready;
        const state = window.ZazuOffline.getState();
        state.sync = { token: 'cross-tab-old-token', device: { id: 987661, business_id: businessId } };
        await window.ZazuOffline.persist();
    }, businessId);

    const secondTab = await context.newPage();
    await secondTab.addInitScript(() => {
        window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC = true;
    });
    await secondTab.goto('/dashboard', { waitUntil: 'domcontentloaded' });
    await expect.poll(() => secondTab.evaluate(() => window.ZazuOffline.getState().sync?.token))
        .toBe('cross-tab-old-token');

    let newTokenSyncStarted;
    const newTokenSync = new Promise(resolve => { newTokenSyncStarted = resolve; });
    await secondTab.route('**/api/sync/**', async route => {
        const request = route.request();
        const url = new URL(request.url());
        expect(request.headers()['authorization']).toBe('Bearer cross-tab-new-token');

        if (url.pathname === '/api/sync/pull') {
            newTokenSyncStarted();
            await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ mutations: [] }) });
            return;
        }
        if (url.pathname === '/api/sync/bootstrap') {
            await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({}) });
            return;
        }
        if (url.pathname === '/api/sync/attachments') {
            await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ attachments: [] }) });
            return;
        }
        await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ acknowledged: true }) });
    });

    await page.route('**/api/sync/provision', async route => {
        await route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({
                token: 'cross-tab-new-token',
                device: { id: 987662, business_id: businessId },
                bootstrap: {},
            }),
        });
    });

    await page.evaluate(() => window.ZazuOffline.pair('654321'));
    await newTokenSync;
    await expect.poll(() => secondTab.evaluate(() => window.ZazuOffline.getState().sync?.token))
        .toBe('cross-tab-new-token');
    await expect.poll(() => secondTab.evaluate(() => window.ZazuOffline.isPaired())).toBe(true);
    await secondTab.close();
});
