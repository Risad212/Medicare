---
name: laravel-security
description: Audit or harden MediCare for security — dependency audits, XSS/CSRF, authz, OAuth, file uploads, throttling. Use for security reviews, vulnerability checks, or hardening auth/input handling.
---

# Laravel Security (MediCare)

## Baseline check (run first)

- `composer audit` (expect 0 advisories)
- `npm audit --omit=dev` (expect 0 vulnerabilities)
- `vendor/bin/phpunit` — `tests/Feature/AuthorizationTest.php` covers guest/patient/doctor/admin guards.

## What this repo already does (keep it working)

- **XSS**: blog body is sanitized server-side by `app/Services/BlogSanitizer.php` (`strip_tags` allowlist + strips `on*` handlers) before the `{!! !!}` render. Never render unsanitized user HTML. Blade uses escaped `{{ }}` elsewhere.
- **CSRF**: every POST form has `@csrf`; `@method(...)` on PUT/PATCH/DELETE.
- **Headers**: `SecurityHeadersMiddleware` is global (see `bootstrap/app.php`).
- **Throttle**: public POSTs (contact/appointment/comment/cancel) and Google OAuth use `throttle:10,1`; auth flows throttle 6–10/min.
- **OAuth**: `GoogleAuthController` requires a verified Google email, never overrides an existing `role`, regenerates the session on login, and refuses linking to unverified local accounts.
- **Uploads**: validate `image|mimes:jpg,jpeg,png,webp|max:2048`, store via `store(..., 'public')`.
- **CSV export**: `CsvExport` neutralizes formula injection (`= + - @`, tab/CR prefix).

## Rules for new code

- Validate with `FormRequest` + `validated()`; never mass-assign raw request arrays.
- Authorize with Policy/Gate or FormRequest `authorize()` by role; test it.
- Do not log or commit secrets; `.env` is gitignored.
- Ensure `APP_DEBUG=false` in production.
- Prefer parameterized Eloquent/query builder; avoid raw SQL with user input (`DB::raw` only for constant expressions like `COALESCE(SUM(...))`).

## Reporting

List findings as: severity, file:line, why it matters, concrete fix. Confirm with a test where possible.
