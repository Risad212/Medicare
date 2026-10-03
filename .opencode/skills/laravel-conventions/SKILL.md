---
name: laravel-conventions
description: Add or modify Laravel backend code in MediCare (controllers, routes, FormRequests, services, models, migrations, policies). Use for new endpoints, CRUD, business logic, DB schema, or middleware work.
---

# Laravel Conventions (MediCare)

Stack: Laravel 13, PHP `^8.4`, SQLite dev / MySQL prod, `laravel/ui` auth, `laravel/socialite`.

## Routing

- All routes live in `routes/web.php` (no `api.php`). Groups: public frontend, `['auth','admin','staff.modules']` under `/admin/*`, `['auth','doctor']` under `/doctor/*`, `['auth','patient']` under `/profile`.
- Middleware aliases are in `bootstrap/app.php` (`admin`, `doctor`, `patient`, `staff.modules`); `SecurityHeadersMiddleware` is global.
- Add `throttle:10,1` to public POST endpoints (contact/appointment/comment/cancel pattern).

## Controllers

- Thin controllers: validate, delegate to `app/Services/*`, return view/redirect.
- Use `FormRequest` + `$request->validated()`. NEVER use `all()`, `except()`, or raw `validate()` + `only()` in new code.
- Extract fields with `array_intersect_key($validated, array_flip([...]))` when only some fields are persisted.
- Wrap multi-write logic in `DB::transaction`.
- Authorize via Policy/Gate or a role check in the FormRequest `authorize()`.

## Models

- Fillable allowlists only (some use `#[Fillable([...])]` attribute form). Keep `password` nullable for OAuth users.
- `Appointment.status` enum: `0=Pending, 1=Approved, 2=Completed, 3=Cancelled`. Cancelled (`3`) is excluded from double-book checks.
- Never hard-delete records that own patient history (appointments/lab orders) — deactivate instead.

## Migrations

- Additive + nullable/default, reversible `down()`, never rename/drop without a deprecation path.
- Guard with `Schema::hasColumn` / `Schema::hasTable` for idempotency (several migrations do this — keep the guards).
- Avoid doctrine/dbal; use driver checks (`DB::getDriverName()`) with try/catch for MySQL-only DDL.

## Style

- 4-space indent, LF. Formatter is Pint: run `vendor/bin/pint` (or `--test` before push).
- No comments unless asked. Keep the `AppServiceProvider` `general_settings` guard.

## Verify before finishing

- `php -l` on touched PHP files
- `vendor/bin/pint --test`
- `vendor/bin/phpunit` (Unit + Feature)
