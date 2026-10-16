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
    test('logout protects queued offline work and clears the local session after explicit discard', async ({ page }) => {
        test.setTimeout(60_000);

        await login(page);
        await page.evaluate(async () => {
            await window.ZazuOffline.ready;
            await window.ZazuOffline.queueMutation('customer', 'create', {
                local_id: crypto.randomUUID(),
                name: 'Logout guard test fixture',
            });
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
        expect(confirmMessages[0]).toContain('pending change(s)');
        expect(await page.evaluate(async () => (await window.ZazuOffline.pending()).length)).toBe(1);

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
