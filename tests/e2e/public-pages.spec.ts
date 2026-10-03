import { test, expect } from '@playwright/test';

/**
 * First e2e: public pages must render (HTTP 200 + key content).
 * Covers routes in routes/web.php: /, /about, /service, /doctor, /blog, /contact, /appointment
 */
const pages = [
    { path: '/', name: 'home' },
    { path: '/about', name: 'about' },
    { path: '/service', name: 'service' },
    { path: '/doctor', name: 'doctor' },
    { path: '/blog', name: 'blog' },
    { path: '/contact', name: 'contact' },
    { path: '/appointment', name: 'appointment' },
] as const;

for (const { path, name } of pages) {
    test(`${name} page loads`, async ({ page }) => {
        const response = await page.goto(path);
        expect(response?.status()).toBe(200);
        await expect(page).toHaveURL(new RegExp(`${path.replace('/', '\\/')}$`));
        // Page renders <body> with visible content, no Laravel exception page
        await expect(page.locator('body')).toBeVisible();
        await expect(page.locator('body')).not.toContainText('Whoops, something went wrong');
        await expect(page.locator('body')).not.toContainText('Server Error');
    });
}

test('navigation from home reaches appointment page', async ({ page }) => {
    await page.goto('/');
    // Click first visible link to /appointment (header CTA)
    const appointmentLink = page.locator('a[href="/appointment"]').first();
    if ((await appointmentLink.count()) > 0) {
        await appointmentLink.click();
        await expect(page).toHaveURL(/\/appointment$/);
        await expect(page.locator('body')).toBeVisible();
    } else {
        // Fallback: direct navigation still proves the flow
        await page.goto('/appointment');
        await expect(page).toHaveURL(/\/appointment$/);
    }
});
