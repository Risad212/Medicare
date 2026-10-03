import { defineConfig, devices } from '@playwright/test';

/**
 * First Playwright e2e setup for MediCare (Laravel 13).
 * - baseURL points at `php artisan serve`
 * - webServer auto-starts Laravel so `npm run test:e2e` is one command
 * Run: npm run test:e2e
 */
export default defineConfig({
    testDir: './tests/e2e',
    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    workers: process.env.CI ? 1 : undefined,
    reporter: [['list'], ['html', { open: 'never' }]],
    use: {
        baseURL: process.env.PLAYWRIGHT_BASE_URL ?? 'http://127.0.0.1:8000',
        trace: 'on-first-retry',
    },
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },
    ],
    webServer: {
        command: 'php artisan serve --host=127.0.0.1 --port=8000',
        url: 'http://127.0.0.1:8000/',
        reuseExistingServer: !process.env.CI,
        timeout: 60 * 1000,
        env: {
            APP_ENV: 'local',
        },
    },
});
