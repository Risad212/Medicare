import { test, expect } from '@playwright/test';

test.describe('Contact form', () => {
    test('visitor can submit contact form', async ({ page }) => {
        await page.goto('/contact');
        await expect(page.locator('body')).not.toContainText('Whoops, something went wrong');

        await page.fill('input[name="name"]', 'E2E Visitor');
        await page.fill('input[name="email"]', 'visitor@example.com');
        await page.fill('input[name="phone"]', '01700000000');
        await page.fill('input[name="subject"]', 'E2E test subject');
        await page.fill('textarea[name="message"]', 'This is an automated E2E test message.');

        await page.click('button[type="submit"]');
        await expect(page.locator('.alert-success')).toBeVisible({ timeout: 15000 });
    });
});
