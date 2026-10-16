import { test, expect } from '@playwright/test';

const DEMO_EMAIL = process.env.ZAZU_DEMO_EMAIL || 'demo@zazu.local';
const DEMO_PASSWORD = process.env.ZAZU_DEMO_PASSWORD || 'password';

async function login(page) {
    await page.goto('/?auth=login', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-auth-modal]')).toBeVisible();
    await page.getByLabel('Username or email').fill(DEMO_EMAIL);
    await page.locator('#auth_password').fill(DEMO_PASSWORD);
    await page.locator('[data-auth-submit]').click();
    await expect(page).toHaveURL(/\/dashboard$/);
}

test.describe('Zazu installed PWA offline shell', () => {
    test('local client becomes ready before an unresponsive initial sync', async ({ page }, testInfo) => {
        test.setTimeout(20_000);
        test.skip(testInfo.project.name !== 'chromium', 'Network-stall startup contract runs once; responsive viewports are covered separately.');

        await login(page);
        await page.evaluate(async () => {
            await window.ZazuOffline.ready;
            const state = window.ZazuOffline.getState();
            state.sync = { token: 'startup-readiness-test-token', device: { id: 999999 } };
            state.local_dirty = false;
            await window.ZazuOffline.persist();
        });

        // Simulate a network request which never resolves instead of a fast 401/500.
        // A local-first boot must still resolve ready from IndexedDB.
        await page.addInitScript(() => {
            const originalFetch = window.fetch.bind(window);
            window.fetch = (input, init) => {
                const rawUrl = typeof input === 'string' ? input : input?.url;
                const url = new URL(rawUrl, window.location.href);
                if (url.pathname === '/api/sync/pull') return new Promise(() => {});
                return originalFetch(input, init);
            };
        });

        await page.reload({ waitUntil: 'domcontentloaded' });
        await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();

        const readiness = await Promise.race([
            page.evaluate(() => window.ZazuOffline.ready.then(() => 'ready')),
            page.waitForTimeout(4_000).then(() => 'blocked'),
        ]);
        expect(readiness).toBe('ready');
    });

    test('offline logout keeps local data when the server cannot end the web session', async ({ page, context }, testInfo) => {
        test.setTimeout(20_000);
        test.skip(testInfo.project.name !== 'chromium', 'Disconnected logout contract runs once; viewport behaviour is covered elsewhere.');

        await login(page);
        await page.evaluate(async () => {
            await window.ZazuOffline.ready;
            const state = window.ZazuOffline.getState();
            state.business = { id: 987654, name: 'Offline logout safety fixture' };
            await window.ZazuOffline.persist();
            await window.ZazuOffline.queueMutation('customer', 'create', {
                local_id: crypto.randomUUID(),
                name: 'Keep this queued while the server is unavailable',
            });
        });

        await context.setOffline(true);
        const dialogs = [];
        page.on('dialog', async dialog => {
            dialogs.push({ type: dialog.type(), message: dialog.message() });
            if (dialog.type() === 'confirm') await dialog.accept();
            else await dialog.dismiss();
        });

        await page.evaluate(() => {
            const form = document.querySelector('form[action$="/logout"]');
            if (!form) throw new Error('Logout form was not found');
            form.requestSubmit();
        });

        await expect.poll(() => dialogs.length).toBe(2);
        expect(dialogs[1].type).toBe('alert');
        expect(dialogs[1].message).toContain('Your local offline data and device token were left untouched');
        await expect.poll(() => new URL(page.url()).pathname).toBe('/dashboard');

        const localState = await page.evaluate(async () => ({
            businessId: window.ZazuOffline.getState()?.business?.id,
            pendingCount: (await window.ZazuOffline.pending()).length,
        }));
        expect(localState.businessId).toBe(987654);
        expect(localState.pendingCount).toBe(1);
    });

    test('logout protects queued offline work and clears the local session after explicit discard', async ({ page }) => {
        test.setTimeout(60_000);

        await login(page);
        await page.evaluate(async () => {
            await window.ZazuOffline.ready;
            await window.ZazuOffline.queueMutation('customer', 'create', {
                local_id: crypto.randomUUID(),
                name: 'Logout guard test fixture',
            });

            // Use a failed mutation for a deterministic persistence test. A
            // pending mutation would legitimately auto-sync during the reload
            // and make the fixture depend on server timing.
            const db = await new Promise((resolve, reject) => {
                const request = indexedDB.open('zazu-client', 1);
                request.onsuccess = () => resolve(request.result);
                request.onerror = () => reject(request.error || new Error('Could not open local offline storage'));
            });
            await new Promise((resolve, reject) => {
                const tx = db.transaction(['mutations'], 'readwrite');
                const store = tx.objectStore('mutations');
                const request = store.getAll();
                request.onsuccess = () => {
                    for (const record of request.result || []) {
                        store.put({ ...record, status: 'failed' });
                    }
                };
                tx.oncomplete = resolve;
                tx.onerror = () => reject(tx.error || new Error('Could not mark mutation as failed'));
                tx.onabort = () => reject(tx.error || new Error('Offline mutation transaction aborted'));
            });
            db.close();
        });

        let confirmDiscard = false;
        const confirmMessages = [];
        page.on('dialog', async dialog => {
            if (dialog.type !== 'confirm') {
                await dialog.dismiss();
                return;
            }

            confirmMessages.push(dialog.message());
            if (confirmDiscard) await dialog.accept();
            else await dialog.dismiss();
        });

        await page.evaluate(() => {
            const form = document.querySelector('form[action$="/logout"]');
            if (!form) throw new Error('Logout form was not found');
            form.requestSubmit();
        });

        await expect.poll(() => new URL(page.url()).pathname).toBe('/dashboard');
        await expect.poll(() => confirmMessages.length).toBe(1);
        expect(confirmMessages[0]).toContain('rejected/failed change(s)');
        const queueCount = () => page.evaluate(() => new Promise((resolve, reject) => {
            const request = indexedDB.open('zazu-client', 1);
            request.onerror = () => reject(request.error || new Error('Could not inspect offline queue'));
            request.onsuccess = () => {
                const db = request.result;
                const tx = db.transaction(['mutations'], 'readonly');
                const read = tx.objectStore('mutations').getAll();
                tx.oncomplete = () => {
                    resolve((read.result || []).length);
                    db.close();
                };
                tx.onerror = () => reject(tx.error || new Error('Could not inspect offline queue'));
            };
        }));
        expect(await queueCount()).toBe(1);

        // A cancelled sign-out must survive a cold page reload without losing queued work.
        await page.reload({ waitUntil: 'domcontentloaded' });
        await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
        await page.evaluate(async () => window.ZazuOffline.ready);
        await expect.poll(queueCount).toBe(1);

        confirmDiscard = true;
        await Promise.all([
            page.waitForURL(url => !url.pathname.endsWith('/dashboard')),
            page.evaluate(() => document.querySelector('form[action$="/logout"]').requestSubmit()),
        ]);

        const localData = await page.evaluate(() => new Promise((resolve, reject) => {
            const request = indexedDB.open('zazu-client', 1);
            request.onerror = () => reject(request.error || new Error('Could not inspect local offline storage'));
            request.onsuccess = () => {
                const db = request.result;
                const tx = db.transaction(['state', 'mutations'], 'readonly');
                const stateRequest = tx.objectStore('state').get('current');
                const queueRequest = tx.objectStore('mutations').getAll();
                tx.oncomplete = () => {
                    resolve({
                        tokenPresent: Boolean(stateRequest.result?.sync?.token),
                        queueCount: (queueRequest.result || []).length,
                        workspacePresent: Boolean(stateRequest.result?.business),
                    });
                    db.close();
                };
                tx.onerror = () => reject(tx.error || new Error('Could not inspect local offline state'));
            };
        }));

        expect(localData.tokenPresent).toBe(false);
        expect(localData.queueCount).toBe(0);
        expect(localData.workspacePresent).toBe(false);
    });

    test('authenticated dashboard survives a real offline navigation', async ({ page, context }) => {
        test.setTimeout(60_000);

        await login(page);
        await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
        await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();

        await page.evaluate(async () => {
            if (!('serviceWorker' in navigator)) throw new Error('Service workers are unavailable');
            await navigator.serviceWorker.ready;
        });

        // The first registration is not necessarily controlling the page yet.
        // Reload once online so the installed shell is definitely under SW control.
        await page.reload({ waitUntil: 'domcontentloaded' });
        await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
        await expect.poll(() => page.evaluate(() => Boolean(navigator.serviceWorker.controller))).toBe(true);

        await context.setOffline(true);

        const offlineResponse = await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
        expect(offlineResponse?.ok()).toBe(true);
        await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
        await expect(page.locator('.zazu-error-shell')).toHaveCount(0);
    });
});
