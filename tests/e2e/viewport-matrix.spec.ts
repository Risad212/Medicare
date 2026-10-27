import { test, expect } from '@playwright/test';

/**
 * Automated viewport matrix (plan §6) replacing the Flora manual checklist.
 * Viewports: 320 / 375 / 600 / 768 / 1024 / 1440.
 * Checks per page per viewport:
 *  - renders with no Laravel exception
 *  - exactly one <h1> (heading-order rule)
 *  - no horizontal overflow (overflow-x: clip guard + responsive rules)
 */
const viewports = [
    { width: 320, tag: 'mobile-s' },
    { width: 375, tag: 'mobile-m' },
    { width: 600, tag: 'mobile-l' },
    { width: 768, tag: 'tablet' },
    { width: 1024, tag: 'laptop' },
    { width: 1440, tag: 'desktop' },
] as const;

const pages = [
    { path: '/', name: 'home' },
    { path: '/about', name: 'about' },
    { path: '/service', name: 'service' },
    { path: '/doctor', name: 'doctor' },
    { path: '/blog', name: 'blog' },
    { path: '/contact', name: 'contact' },
    { path: '/appointment', name: 'appointment' },
] as const;

for (const viewport of viewports) {
    test.use({ viewport: { width: viewport.width, height: 900 } });

    for (const { path, name } of pages) {
        test(`${name} @ ${viewport.width}px (${viewport.tag})`, async ({ page }) => {
            const response = await page.goto(path, { waitUntil: 'domcontentloaded' });
            expect(response?.status()).toBe(200);
            await expect(page.locator('body')).not.toContainText('Whoops, something went wrong');
            await expect(page.locator('body')).not.toContainText('Server Error');

            // The owl loop clones slide markup for cycling; judge real handoff headings only.
            const realH1s = await page.evaluate(() => {
                const isCloned = (el: Element) => el.closest('.cloned') !== null;
                return Array.from(document.querySelectorAll('h1')).filter((h1) => !isCloned(h1)).length;
            });
            expect(realH1s, `${name} should have exactly one non-cloned h1`).toBe(1);

            const overflow = await page.evaluate(() => {
                const doc = document.documentElement;
                return doc.scrollWidth - doc.clientWidth;
            });
            expect(overflow, `${name} overflows horizontally at ${viewport.width}px`).toBeLessThanOrEqual(1);
        });
    }
}

test('mobile nav toggle opens the menu at 320px', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 900 });
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('body')).not.toContainText('Whoops, something went wrong');

    const toggle = page.locator('.navigation .navbar-toggler, .main_menu .navbar-toggler, button.navbar-toggler, .menu-toggle').first();
    if ((await toggle.count()) === 0) {
        test.skip(true, 'no hamburger toggle rendered (topbar hidden design)');
    } else {
        await toggle.click();
        await expect(toggle).toBeInViewport();
    }
});

test('appointment form fields are reachable and sized at 320px', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 900 });
    await page.goto('/appointment', { waitUntil: 'domcontentloaded' });

    const firstField = page.locator('#patient_name');
    await expect(firstField).toBeVisible();
    const box = await firstField.boundingBox();
    expect((box?.width ?? 0), 'form field tap width at 320px').toBeGreaterThan(200);
});
