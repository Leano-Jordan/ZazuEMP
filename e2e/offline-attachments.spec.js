import { test, expect } from '@playwright/test';

test('offline attachments can be queued for a locally created job', async ({ page }) => {
    await page.goto('/offline');
    await page.context().setOffline(true);

    await page.getByRole('button', { name: 'New job' }).click();
    await page.getByLabel('Job name').fill('Offline catering job');
    await page.getByRole('button', { name: 'Save on phone' }).click();
    await expect(page.getByText('Offline catering job')).toBeVisible();

    await page.getByRole('button', { name: 'Attachments' }).click();

    await expect(page.getByLabel('Job')).toContainText('Offline catering job');

    await page.getByLabel('File').setInputFiles({
        name: 'brief.txt',
        mimeType: 'text/plain',
        buffer: Buffer.from('offline attachment test'),
    });
    await page.getByLabel('Description').fill('Offline brief');
    await page.getByRole('button', { name: 'Save attachment' }).click();

    await expect(page.locator('#attachment-list')).toContainText('brief.txt');
    await expect(page.locator('#attachment-list')).toContainText('Waiting for connection');

    const records = await page.evaluate(async () => {
        const db = await new Promise((resolve, reject) => {
            const request = indexedDB.open('zazu-phone-attachments', 1);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });

        return await new Promise((resolve, reject) => {
            const tx = db.transaction('files');
            const request = tx.objectStore('files').getAll();
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    });

    expect(records).toHaveLength(1);
    expect(records[0].event_local_id).toBe('job-local-1');
    expect(records[0].event_id).toBeNull();
    expect(records[0].status).toBe('pending');
});
