import { test, expect } from '@playwright/test';

const DEMO_EMAIL = process.env.ZAZU_DEMO_EMAIL || 'demo@zazu.local';
const DEMO_PASSWORD = process.env.ZAZU_DEMO_PASSWORD || 'password';

async function login(page) {
    await page.goto('/?auth=login', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-auth-modal]')).toBeVisible();
    await page.getByLabel('Username or email').fill(DEMO_EMAIL);
    await page.locator('#auth_password').fill(DEMO_PASSWORD);
    await page.locator('[data-auth-submit]').click();
    await page.waitForURL(url => url.pathname !== '/' && url.pathname !== '/login');
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
    await expect.poll(() => page.evaluate(() => Boolean(window.ZazuOffline?.ready))).toBe(true);
}

test('Settings resolves stale offline edits without losing either version', async ({ page }, testInfo) => {
    test.setTimeout(35_000);
    test.skip(testInfo.project.name !== 'chromium', 'The conflict-resolution UI contract runs once in Chromium.');

    await login(page);
    const businessId = Number(await page.locator('body').getAttribute('data-zazu-business-id'));
    expect(businessId).toBeGreaterThan(0);

    await page.context().setOffline(true);
    const fixtures = await page.evaluate(async () => {
        await window.ZazuOffline.ready;
        const state = window.ZazuOffline.getState();
        state.sync = {
            token: 'conflict-ui-acceptance-token',
            device: { id: 987701, business_id: Number(document.body.dataset.zazuBusinessId) },
        };
        state.local_dirty = false;
        state.customers = [
            { id: 701, server_id: 701, local_id: 'local:conflict-keep', revision: 1, name: 'Phone version to keep' },
            { id: 702, server_id: 702, local_id: 'local:conflict-server', revision: 1, name: 'Phone version to replace' },
        ];
        await window.ZazuOffline.persist({ allowSessionChange: true });

        const keep = await window.ZazuOffline.queueMutation('customer', 'update', {
            local_id: 'local:conflict-keep',
            server_id: 701,
            expected_revision: 1,
            record: { name: 'Phone version to keep' },
        });
        const server = await window.ZazuOffline.queueMutation('customer', 'update', {
            local_id: 'local:conflict-server',
            server_id: 702,
            expected_revision: 1,
            record: { name: 'Phone version to replace' },
        });

        const conflicts = [
            {
                id: 987711,
                mutation_id: keep.id,
                entity_type: 'customer',
                entity_id: 'local:conflict-keep',
                conflict_type: 'stale_record_version',
                status: 'open',
                message: 'This customer changed on another trusted device.',
                comparison: [{ field: 'Name', local: 'Phone version to keep', remote: 'Owner PC version' }],
                local_payload: keep.payload,
                remote_payload: { revision: 5, record: { id: 701, name: 'Owner PC version' } },
            },
            {
                id: 987712,
                mutation_id: server.id,
                entity_type: 'customer',
                entity_id: 'local:conflict-server',
                conflict_type: 'stale_record_version',
                status: 'open',
                message: 'This customer changed on another trusted device.',
                comparison: [{ field: 'Name', local: 'Phone version to replace', remote: 'Latest server customer' }],
                local_payload: server.payload,
                remote_payload: { revision: 8, record: { id: 702, name: 'Latest server customer' } },
            },
        ];

        const db = await new Promise((resolve, reject) => {
            const request = indexedDB.open('zazu-client-business-' + document.body.dataset.zazuBusinessId, 1);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error || new Error('Could not open offline workspace'));
        });
        await new Promise((resolve, reject) => {
            const tx = db.transaction(['mutations', 'state'], 'readwrite');
            const store = tx.objectStore('mutations');
            for (const mutation of [keep, server]) {
                store.put({
                    ...mutation,
                    status: 'failed',
                    conflict_id: mutation.id === keep.id ? 987711 : 987712,
                    last_error: 'Stale record version',
                });
            }
            tx.objectStore('state').put({ ...state, conflicts }, 'current');
            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error || new Error('Could not seed conflict fixtures'));
            tx.onabort = () => reject(tx.error || new Error('Conflict fixture transaction aborted'));
        });
        db.close();
        window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
        return { keepId: keep.id, serverId: server.id };
    });

    const resolutionRequests = [];
    await page.route('**/api/sync/push', route => route.abort());
    await page.route('**/api/sync/conflicts*', route => route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
            conflicts: [
                {
                    id: 987711,
                    mutation_id: fixtures.keepId,
                    entity_type: 'customer',
                    entity_id: 'local:conflict-keep',
                    conflict_type: 'stale_record_version',
                    status: 'open',
                    message: 'This customer changed on another trusted device.',
                    comparison: [{ field: 'Name', local: 'Phone version to keep', remote: 'Owner PC version' }],
                    local_payload: { record: { name: 'Phone version to keep' } },
                    remote_payload: { revision: 5, record: { id: 701, name: 'Owner PC version' } },
                },
                {
                    id: 987712,
                    mutation_id: fixtures.serverId,
                    entity_type: 'customer',
                    entity_id: 'local:conflict-server',
                    conflict_type: 'stale_record_version',
                    status: 'open',
                    message: 'This customer changed on another trusted device.',
                    comparison: [{ field: 'Name', local: 'Phone version to replace', remote: 'Latest server customer' }],
                    local_payload: { record: { name: 'Phone version to replace' } },
                    remote_payload: { revision: 8, record: { id: 702, name: 'Latest server customer' } },
                },
            ],
        }),
    }));
    await page.route('**/api/sync/conflicts/*/resolve', async route => {
        const id = Number(new URL(route.request().url()).pathname.split('/').at(-2));
        const { action } = route.request().postDataJSON();
        resolutionRequests.push({ id, action });
        const isKeep = id === 987711;
        await route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({
                mutation_status: action === 'retry' ? 'new_mutation_required' : 'discarded',
                rebase: action === 'retry' ? {
                    new_mutation_id: 'rebased-conflict-mutation-987711',
                    expected_revision: 5,
                } : undefined,
                conflict: {
                    id,
                    conflict_type: 'stale_record_version',
                    remote_payload: isKeep
                        ? { revision: 5, record: { id: 701, name: 'Owner PC version' } }
                        : { revision: 8, record: { id: 702, name: 'Latest server customer' } },
                },
            }),
        });
    });

    await page.context().setOffline(false);
    await page.goto('/settings', { waitUntil: 'domcontentloaded' });
    const section = page.locator('[data-zazu-offline-conflicts]');
    await expect(section).toBeVisible();
    await expect(section.getByText('Phone version to keep')).toBeVisible();
    await expect(section.getByText('Owner PC version')).toBeVisible();
    await expect(section.getByText('Latest server customer')).toBeVisible();

    await section.getByRole('button', { name: 'Keep my changes' }).first().click();
    await expect(section.getByText(/rebased onto the latest server version/i)).toBeVisible();

    const rebased = await page.evaluate(async oldMutationId => {
        const db = await new Promise((resolve, reject) => {
            const request = indexedDB.open('zazu-client-business-' + document.body.dataset.zazuBusinessId, 1);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error || new Error('Could not open offline workspace'));
        });
        const result = await new Promise((resolve, reject) => {
            const tx = db.transaction(['mutations', 'state'], 'readonly');
            const queue = tx.objectStore('mutations').getAll();
            const state = tx.objectStore('state').get('current');
            tx.oncomplete = () => resolve({ queue: queue.result || [], state: state.result });
            tx.onerror = () => reject(tx.error || new Error('Could not inspect rebased conflict'));
        });
        db.close();
        return {
            oldMutationExists: result.queue.some(item => item.id === oldMutationId),
            replacement: result.queue.find(item => item.id === 'rebased-conflict-mutation-987711'),
            keepConflictStatus: result.state.conflicts.find(item => item.id === 987711)?.status,
        };
    }, fixtures.keepId);
    expect(rebased.oldMutationExists).toBe(false);
    expect(rebased.replacement).toMatchObject({
        id: 'rebased-conflict-mutation-987711',
        status: 'pending',
        payload: { expected_revision: 5, record: { name: 'Phone version to keep' } },
    });
    expect(rebased.keepConflictStatus).toBe('resolved');

    page.once('dialog', dialog => dialog.accept());
    await section.getByRole('button', { name: 'Use server version' }).click();
    await expect(section.getByText(/local record was replaced with the current server version/i)).toBeVisible();

    const restored = await page.evaluate(async () => {
        const db = await new Promise((resolve, reject) => {
            const request = indexedDB.open('zazu-client-business-' + document.body.dataset.zazuBusinessId, 1);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error || new Error('Could not open offline workspace'));
        });
        const result = await new Promise((resolve, reject) => {
            const tx = db.transaction(['mutations', 'state'], 'readonly');
            const queue = tx.objectStore('mutations').getAll();
            const state = tx.objectStore('state').get('current');
            tx.oncomplete = () => resolve({ queue: queue.result || [], state: state.result });
            tx.onerror = () => reject(tx.error || new Error('Could not inspect server restoration'));
        });
        db.close();
        return {
            discardedMutationPresent: result.queue.some(item => item.conflict_id === 987712),
            customer: result.state.customers.find(item => item.local_id === 'local:conflict-server'),
            serverConflictStatus: result.state.conflicts.find(item => item.id === 987712)?.status,
        };
    });

    expect(restored.discardedMutationPresent).toBe(false);
    expect(restored.customer).toMatchObject({
        name: 'Latest server customer',
        revision: 8,
        pending_sync: false,
    });
    expect(restored.serverConflictStatus).toBe('resolved');
    expect(resolutionRequests).toEqual([
        { id: 987711, action: 'retry' },
        { id: 987712, action: 'discard' },
    ]);
});
