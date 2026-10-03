# MODULES.md — MediCare removable features

Every feature below is independently removable. Two-step process for any
module: **flag off first** (URLs 404, menus hide, core degrades), then
optionally **delete its files** to keep the app light.

Core rule enforced in code: while a flag is off, core never autoloads that
module's classes (`::class` references are plain strings until `app()`
resolves them inside an `if (Module::enabled(...))` block).

## Switch table

| Feature | Flag (`config/modules.php` / env) | Code folder | Views | DB tables |
|---|---|---|---|---|
| Pharmacy | `pharmacy` / `MODULE_PHARMACY` | `app/Modules/Pharmacy/` | `resources/views/pharmacy/` | `medicines`, `prescription_items` (+dispense cols) |
| Beds | `beds` / `MODULE_BEDS` | `app/Modules/Beds/` | `resources/views/{beds,wards,rooms}/` | `wards`, `rooms`, `beds` |
| Ambulance | `ambulance` / `MODULE_AMBULANCE` | `app/Modules/Ambulance/` | `resources/views/ambulance/` | `ambulance_requests` |
| Vaccination | `vaccination` / `MODULE_VACCINATION` | `app/Modules/Vaccination/` | `resources/views/vaccinations/` | `vaccinations` |
| Allergy | `allergy` / `MODULE_ALLERGY` | flag-only (hooks in `ProfileController`, profile view, prescription view) | — | `users.allergies` col |
| Blood Bank | `bloodbank` / `MODULE_BLOODBANK` | `app/Modules/BloodBank/` | `resources/views/bloodbank/` | `blood_groups`, `blood_donors`, `blood_donations`, `blood_requests`, `blood_issues`, `blood_inventory_settings` |
| Lab | `lab` / `MODULE_LAB` | `app/Modules/Lab/` | `resources/views/lab/` | `lab_tests`, `lab_orders`, `lab_order_items`, `lab_reports`, `invoices` |
| Backups | `backups` / `MODULE_BACKUPS` | `app/Modules/Backups/` | `resources/views/backups/` | none (`spatie/laravel-backup` + `config/backup.php`) |
| Analytics | `analytics` / `MODULE_ANALYTICS` | `app/Modules/Analytics/` (service only) | `resources/views/analytics/` | none |
| Language | `language` / `MODULE_LANGUAGE` | `app/Modules/Language/` | `resources/views/languages/`, `lang/en+bn/messages.php`, `lang/bn/validation.php` | `languages`, `users.locale` col |
| Search | `search` / `MODULE_SEARCH` | `app/Modules/Search/` (service only) | `resources/views/search/` | none |
| Lockout | `lockout` / `MODULE_LOCKOUT` | flag-only: `routes/web.php` login throttle + `LoginController::hasTooManyLoginAttempts()` | — | none |

## Removal steps per module

With routes: Pharmacy, Beds, Ambulance, Vaccination, BloodBank, Lab,
Backups, Language.

1. Set flag `false` (or `MODULE_X=false` in `.env`).
2. Delete `app/Modules/<Name>/`, its `resources/views/<name>/`, its test
   (`tests/Feature/<Name>*Test.php`).
3. Delete the provider line in `bootstrap/providers.php`.
4. Tables stay (data kept); roll back its migrations only if tables must go.

Without routes (section/filter/middleware/behavior): Analytics, Search,
Language-middleware, Allergy, Lockout.

1. Set flag `false`.
2. Analytics: delete `app/Modules/Analytics/` + `resources/views/analytics/`.
   Core hook left behind: one guarded `app(...)` call in
   `AdminController@index` + one `@include` in `backend/home.blade.php`
   (both no-op while off).
3. Search: delete `app/Modules/Search/` + `resources/views/search/`.
   Core hook left behind: guarded branch in
   `Frontend\DoctorController@index` (falls back to plain listing) + one
   `@include` in `frontend/doctor.blade.php`.
4. Language: delete `app/Modules/Language/` + `resources/views/languages/`
   + `lang/*/messages.php` + `lang/bn/validation.php`. Then remove: the
   `LanguageServiceProvider` line in `bootstrap/providers.php`, the
   `SetLocale` line in `bootstrap/app.php`, the guarded sidebar link and
   the `__()` calls in the frontend header/footer (they render raw keys
   like `messages.nav.home` once dictionaries are gone — replace with
   plain English). Seeder guards itself.
5. Allergy: hooks are `Module::enabled('allergy')` guards — leave them;
   removing the column needs a migration.
6. Lockout: flag off removes the route throttle after reboot
   (`route:clear` if cached) and the per-account lockout immediately.
   Nothing to delete.

## What core always keeps (minimum)

Auth + roles, appointments (booking, slots, schedules, off-days),
doctors/patients/users, dashboard shell, frontend pages, contact/blog —
i.e. everything outside the folders above plus the guarded one-line hooks.
