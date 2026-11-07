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
                        size: 22,
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
                            size: 22,
                            description: '',
                            created_at: new Date().toISOString(),
                        },
                        {
                            id: 802,
                            event_id: eventId,
                            idempotency_key: remoteKey,
                            original_name: 'server-brief.txt',
                            mime_type: 'text/plain',
                            size: 22,
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
