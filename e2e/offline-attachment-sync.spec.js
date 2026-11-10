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

    const requests = { uploads: 0, key: null, eventId: null };
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
            requests.key = body.match(/name="idempotency_key"\r\n\r\n([^\r\n]+)/)?.[1] || null;
            requests.eventId = Number(body.match(/name="event_id"\r\n\r\n([^\r\n]+)/)?.[1] || 0);
            expect(requests.key).toMatch(/^[0-9a-f-]{36}$/i);
            expect(requests.eventId).toBe(eventId);
            expect(body).toContain('offline-evidence.txt');

            await route.fulfill({
                status: 201,
                contentType: 'application/json',
                body: JSON.stringify({
                    duplicate: false,
                    attachment: {
                        id: 801,
                        event_id: eventId,
                        idempotency_key: requests.key,
                        original_name: 'offline-evidence.txt',
                        mime_type: 'text/plain',
                        size: Buffer.byteLength('saved on phone for later'),
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
                    ],
                }),
            });
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
