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
        const pageErrors: string[] = [];
        page.on('pageerror', (error) => pageErrors.push(error.message));
        const response = await page.goto(path);
        expect(response?.status()).toBe(200);
        await expect(page).toHaveURL(new RegExp(`${path.replace('/', '\\/')}$`));
        await expect(page.getByRole('navigation', { name: 'Main navigation' })).toBeVisible();
        await expect(page.getByRole('heading', { level: 1 }).first()).toBeVisible();
        await expect(page.locator('body')).not.toContainText('Whoops, something went wrong');
        await expect(page.locator('body')).not.toContainText('Server Error');
        expect(pageErrors).toEqual([]);
    });
}

test('navigation from home reaches appointment page', async ({ page }) => {
    const pageErrors: string[] = [];
    page.on('pageerror', (error) => pageErrors.push(error.message));
    await page.goto('/');
    // Click first visible link to /appointment (header CTA)
    await page.getByRole('link', { name: /Appointment/ }).first().click();
    await expect(page).toHaveURL(/\/appointment$/);
    await expect(page.getByRole('heading', { name: 'Request an appointment' })).toBeVisible();
    await expect(page.getByLabel('Patient name')).toBeVisible();
    await expect(page.getByLabel('Doctor')).toBeVisible();
    await expect(page.getByLabel('Appointment date')).toBeVisible();
    expect(pageErrors).toEqual([]);
});

test('contact form submits successfully', async ({ page }) => {
    test.skip(Boolean(process.env.PLAYWRIGHT_BASE_URL), 'The contact workflow sends mail; only run it against the isolated local test server.');

    await page.goto('/contact');
    await page.getByLabel('Your name').fill('Playwright Visitor');
    await page.getByLabel('Email address').fill('playwright@example.test');
    await page.getByLabel('Phone number').fill('01700000000');
    await page.getByLabel('Subject').fill('Test message');
    await page.getByLabel('Message').fill('This is an end-to-end contact form test.');
    await page.getByRole('button', { name: /Send message/ }).click();

    await expect(page.getByText('Your message has been sent. Thank you for contacting us.')).toBeVisible();
});
