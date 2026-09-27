import { test, expect } from '@playwright/test';

test('registration flows into catalogue, business setup and dashboard', async ({ page }) => {
    const username = 'browserowner' + Date.now().toString().slice(-6);

    await page.goto('http://127.0.0.1:8000/register');
    await expect(page.getByRole('heading', { name: 'Set up your Zazu workspace' })).toBeVisible();

    await page.getByLabel('Your name').fill('Browser Owner');
    await page.getByLabel('Username').fill(username);
    await page.getByLabel('Business name').fill('Browser Catering');
    await page.getByLabel('Email address').fill(username + '@example.com');
    await page.getByLabel('Password').fill('password123');
    await page.getByLabel('Confirm password').fill('password123');
    await page.getByRole('button', { name: 'Create workspace' }).click();

    await expect(page).toHaveURL(/\/setup\/catalogue$/);
    await expect(page.getByRole('heading', { name: 'Set up what you offer' })).toBeVisible();

    await page.getByLabel('What do you offer?').fill('Wedding catering');
    await page.getByRole('button', { name: 'Continue' }).click();

    await expect(page).toHaveURL(/\/setup\/business$/);
    await expect(page.getByRole('heading', { name: /Business details|Set up your business/i })).toBeVisible();

    await page.getByRole('button', { name: /Save|Continue/i }).last().click();

    await expect(page).toHaveURL(/\/dashboard$/);
    await expect(page.getByRole('heading', { name: 'Business overview' })).toBeVisible();
    await expect(page.getByText('Wedding catering')).toBeVisible();
});
