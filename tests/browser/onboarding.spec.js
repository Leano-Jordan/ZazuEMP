import { test, expect } from '@playwright/test';

test('registration flows into catalogue, business setup and dashboard', async ({ page }) => {
    const unique = Date.now().toString().slice(-6);
    const username = 'browserowner' + unique;
    const businessName = 'Browser Catering ' + unique;

    await page.goto('http://127.0.0.1:8000/register');
    await expect(page.getByRole('heading', { name: 'Set up your Zazu workspace' })).toBeVisible();

    await page.getByLabel('Your name').fill('Browser Owner');
    await page.getByLabel('Username').fill(username);
    await page.getByLabel('Business name').fill(businessName);
    await page.getByLabel('Email address').fill(username + '@example.com');
    await page.locator('#password').fill('password123');
    await page.locator('#password_confirmation').fill('password123');
    await page.getByRole('button', { name: 'Create workspace' }).click();

    await expect(page).toHaveURL(/\/setup\/catalogue$/);
    await expect(page.getByRole('heading', { name: 'Set up what you offer' })).toBeVisible();

    await page.getByLabel('What do you offer?').fill('Wedding catering');
    await page.getByRole('button', { name: 'Continue' }).click();

    await expect(page).toHaveURL(/\/setup\/business$/);
    await expect(page.getByRole('heading', { name: 'Tell Zazu about your business' })).toBeVisible();

    await page.getByRole('button', { name: 'Finish setup' }).click();

    await expect(page).toHaveURL(/\/dashboard$/);
    await expect(page.getByRole('heading', { name: 'Business overview' })).toBeVisible();
    await expect(page.getByTitle('Active business workspace')).toHaveText(businessName);

    const moduleLinks = await page.locator('nav[aria-label="Primary"] a').evaluateAll((links) =>
        links.map((link) => ({ href: link.href, name: link.textContent?.trim() }))
            .filter((link) => link.href && link.name)
    );

    for (const link of moduleLinks) {
        const response = await page.goto(link.href, { waitUntil: 'domcontentloaded' });
        expect(response?.status(), link.name).toBe(200);
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('body')).not.toContainText('Server Error');
    }

    await page.goto('http://127.0.0.1:8000/dashboard');
    await page.locator('[data-theme-toggle]').click();
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');
    await page.locator('[data-theme-toggle]').click();
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'light');
});
