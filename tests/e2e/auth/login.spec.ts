import { test, expect } from '@playwright/test';

const PASSWORD = '123456';

test.describe('Authentication: login', () => {
    test('admin can sign in and reaches admin dashboard', async ({ page }) => {
        await page.goto('/login');
        await page.fill('#email', 'admin@medicare.test');
        await page.fill('#password', PASSWORD);
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/\/admin/);
        await expect(page.locator('body')).not.toContainText('Whoops, something went wrong');
    });

    test('doctor can sign in and reaches doctor dashboard', async ({ page }) => {
        await page.goto('/login');
        await page.fill('#email', 'doctor1@medicare.test');
        await page.fill('#password', PASSWORD);
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/\/doctor\/dashboard/);
    });

    test('patient can sign in and reaches profile', async ({ page }) => {
        await page.goto('/login');
        await page.fill('#email', 'patient1@medicare.test');
        await page.fill('#password', PASSWORD);
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/\/profile/);
    });

    test('invalid credentials show an error and stay on login', async ({ page }) => {
        await page.goto('/login');
        await page.fill('#email', 'nobody@medicare.test');
        await page.fill('#password', 'wrong-password');
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/\/login/);
        await expect(page.locator('body')).toContainText(/These credentials do not match|credentials/i);
    });

    test('unauthenticated visit to /admin redirects to login', async ({ page }) => {
        await page.goto('/admin');
        await expect(page).toHaveURL(/\/login/);
    });

    test('patient cannot access admin area', async ({ page }) => {
        await page.goto('/login');
        await page.fill('#email', 'patient1@medicare.test');
        await page.fill('#password', PASSWORD);
        await page.click('button[type="submit"]');
        const resp = await page.goto('/admin');
        // AdminMiddleware aborts with 403 for patients.
        expect(resp?.status()).toBe(403);
    });
});

test.describe('Authentication: logout', () => {
    test('admin can sign out', async ({ page }) => {
        await page.goto('/login');
        await page.fill('#email', 'admin@medicare.test');
        await page.fill('#password', PASSWORD);
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/\/admin/);

        // Logout link lives inside a Bootstrap dropdown; submit the hidden form directly.
        await page.evaluate(() => {
            const form = document.getElementById('logout-form') as HTMLFormElement | null;
            if (form) { form.submit(); return; }
            const f = document.createElement('form');
            f.method = 'POST';
            f.action = '/logout';
            const t = document.createElement('input');
            t.type = 'hidden';
            t.name = '_token';
            t.value = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';
            f.appendChild(t);
            document.body.appendChild(f);
            f.submit();
        });

        await expect(page).toHaveURL(/\/(login)?$/);
    });
});
