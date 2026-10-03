---
name: laravel-testing
description: Write or run tests for MediCare — PHPUnit unit/feature tests or Playwright e2e. Use when asked to add tests, run the suite, fix failing tests, or verify a change with tests.
---

# Laravel Testing (MediCare)

## Commands

- Unit: `vendor/bin/phpunit --testsuite=Unit --testdox`
- Feature: `vendor/bin/phpunit --testsuite=Feature --testdox`
- All: `vendor/bin/phpunit`
- E2E: `npm run test:e2e` (Playwright, auto-starts `php artisan serve`)

## Setup facts

- `phpunit.xml` uses sqlite `:memory:` for tests (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`), array cache/session, sync queue.
- Base class: `tests/TestCase.php` (extends `Illuminate\Foundation\Testing\TestCase`; `createApplication()` is inherited, no trait needed).
- Feature tests use `RefreshDatabase` and `User::factory()`.
- `mockery/mockery` is required (Laravel Testing pulls it).
- Playwright config: `playwright.config.ts`, specs in `tests/e2e/`, chromium only, `baseURL http://127.0.0.1:8000`.

## Conventions

- Unit tests: pure logic (services like `BloodCompatibility`, `BlogSanitizer`) — extend `PHPUnit\Framework\TestCase`, no app boot.
- Feature tests: HTTP + DB — use `actingAs($user)` and assert status/redirect.
- Authorization tests assert guest → `/login` redirect, wrong-role → `/login`, right-role → 200.
- Prefer `assertEqualsCanonicalizing` for unordered array comparisons.
- After UI/Blade edits, run `php artisan view:cache` then `view:clear` to catch compile errors.

## Known gotchas

- Do not assert `body` does not contain "Exception" on frontend pages — copy like "Exceptional Expertise" triggers false positives. Assert on "Whoops" instead.
- Migrations are guarded, so `RefreshDatabase` on sqlite `:memory:` runs clean; MySQL-only DDL is skipped by driver check.
