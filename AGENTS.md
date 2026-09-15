# AGENTS.md — MediCare (Laravel 13 Hospital Site)

## Stack
- Laravel 13 (`laravel/framework ^13`), PHP `^8.3`, Vite 8, Bootstrap 5.3.2 (admin via CDN + local `jquery-3.7.0`), `laravel/socialite ^5.30`, `laravel/ui` auth scaffolding.
- DB: SQLite by default (`.env.example`: `DB_CONNECTION=sqlite`); MySQL 5.7+ in prod. Tests force `sqlite :memory:` via `phpunit.xml` — never change test DB env to MySQL.
- No CI workflows, no `app/Http/Kernel.php`. Middleware aliases live in `bootstrap/app.php`: `admin`, `doctor`; `SecurityHeadersMiddleware` is global.

## Commands (use these exact forms)
- Full dev: `composer run dev` (serve + queue:listen + pail + vite concurrently). Plain serve: `php artisan serve`.
- Tests: `composer run test` (= `config:clear` + `artisan test`) or `php artisan test --filter=GoogleAuthTest`. Suites: `tests/Feature/{GoogleAuthTest,ExampleTest}.php`.
- E2E (Laravel Dusk, `tests/Browser/`): `composer run dusk` (= `phpunit -c phpunit.dusk.xml`, needed — the `php artisan dusk` child-phpunit output gets mangled by the toolchain). After a Chrome update, resync the driver: `php artisan dusk:chrome-driver --detect`. Dusk uses its own DB (`database/dusk.sqlite` via `.env.dusk.local` + `phpunit.dusk.xml`, `APP_URL=http://127.0.0.1:8089`) and auto-starts a local server from `tests/DuskTestCase.php::prepare()` — never point Dusk at the dev MySQL DB.
- Frontend: `npm run dev` / `npm run build` (entrypoints `resources/sass/app.scss`, `resources/js/app.js`).
- Setup order: `composer install` → `cp .env.example .env` → `php artisan key:generate` → `php artisan migrate` → `php artisan storage:link` (required — image uploads break without it).
- Verify: `composer audit`, `php -l` on touched controllers/models.

## Architecture (not obvious from filenames)
- Routes all in `routes/web.php` (no `api.php`): public frontend (`/`, `/about`, `/service`, `/doctor`, `/blog`, `/contact`, `/appointment`, `/get-available-slots`), `auth`-gated patient `/profile`, `['auth','admin']` group under `/admin/*`, `['auth','doctor']` group under `/doctor/*`. `Auth::routes(['verify'=>false])`.
- Roles (`users.role` = `patient|doctor|admin`): redirect after login in `LoginController::authenticated()` by role; Google OAuth (`GoogleAuthController` + `/auth/google*`) auto-creates/links `patient` accounts — **never override existing `role`**, `password` nullable for OAuth users.
- Appointment `status` enum (per `backend/appointments/edit.blade.php`): `0=Pending, 1=Approved, 2=Completed, 3=Cancelled`. Cancel paths (`Frontend/AppointmentController::cancel/cancelByToken`) set `3`; double-book check excludes `3`. `AdminController@index` correctly counts `status=3` as cancelled and `doctorLoad` excludes `!= 3`. A generated `booking_active` column + `appointments_booking_unique` index reject duplicate active bookings at the DB level (cancelled rows excluded, so cancelled slots can be re-booked).
- Key relations: `Appointment` belongs to `doctor/user/timeSlot`. Guest bookings have `user_id=null`; `GuestRecordLinker` backfills by email on register/login/Google-link.
- `AppServiceProvider::boot()` guards `Schema::hasTable('general_settings')` + try/catch and shares `View::share('setting', ...)` — keep this guard; several migrations use `hasColumn` guards for the same reason. Do not "simplify" them away.
- Views: `resources/views/frontend.*`, `resources/views/admin/*` (Vali Admin theme), `resources/views/doctor/*`. Brand primary `#05d3b0`. Always use `asset()` helper; uploads live in `public/` via storage link.

## Conventions (repo-specific, per `docs/ARCHITECTURE.md`)
- New endpoints: `FormRequest` + `$request->validated()` (never `all()`/`except()`); thin controller → `app/Services/*` with `DB::transaction`; authorize via Policy/Gate; add `throttle:10,1` on public POSTs (contact/appointment/comment/cancel pattern).
- Blade: `{{ }}` escaped only, `@csrf` in forms, no `{!! !!}`.
- Migrations: additive + nullable/default, reversible `down()`, never rename/drop columns without deprecation path.
- Style: 4-space indent, LF (`.editorconfig`); formatter is Pint (`vendor/bin/pint --test` before push if PHP changed).
- Local skills in `.opencode/skills/`: `architecture`, `laravel-coding`, `laravel-docs`, `qa`, `security` — load the matching skill for non-trivial work in that area.
