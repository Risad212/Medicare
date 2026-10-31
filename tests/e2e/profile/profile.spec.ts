import { test, expect } from '@playwright/test';

const PASSWORD = '123456';

test.describe('Patient profile', () => {
    test('patient can sign in and view profile', async ({ page }) => {
        await page.goto('/login');
        await page.fill('#email', 'patient1@medicare.test');
        await page.fill('#password', PASSWORD);
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/\/profile/);
        await expect(page.locator('body')).not.toContainText('Whoops, something went wrong');
        await expect(page.locator('body')).toContainText(/patient|profile/i);
    });
});
