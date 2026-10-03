# MODULES.md — MediCare removable features

Every feature below can be disabled independently using its flag (URLs 404,
menus hide, and guarded core integrations stop using the module). Permanent
file removal is a separate maintenance operation: core models and services
still contain optional integration points for some modules, so do not assume
that deleting only the module folder is sufficient.

The `::class` operator itself produces a class-name string without loading the
class. However, calling a relationship, service, or notification that uses
that class will fail if its module files have been deleted. Keep module files
in place when only disabling a feature; before permanent removal, inspect and
remove or replace every core integration that references the module.

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
2. For permanent removal, inspect all core references with
   `rg 'App\\Modules\\<Name>\\' app bootstrap routes database/seeders resources/views`.
   Remove or replace references that can be executed without the module,
   including model relationships, service/notification hooks, seeders, and
   guarded UI/controller code. Imports and `::class` references can remain
   syntactically valid after file deletion, but execution of those paths cannot.
3. Delete `app/Modules/<Name>/`, its views, and its tests. Remove the module's
   provider entry from `bootstrap/providers.php`.
4. Clear generated Laravel caches as part of deployment after removing the
   provider entry; stale `bootstrap/cache` manifests may still try to load a
   deleted provider. Then rebuild optimized caches if used in production.
5. Tables stay (data kept); roll back its migrations only if tables must go.

Without routes (section/filter/middleware/behavior): Analytics, Search,
Language-middleware, Allergy, Lockout.

1. Set flag `false`.
2. Analytics: delete `app/Modules/Analytics/` + `resources/views/analytics/`.
   Remove or replace its core references in `AdminController` and
   `backend/home.blade.php`.
3. Search: delete `app/Modules/Search/` + `resources/views/search/`.
   Remove or replace its core references in `Frontend\DoctorController` and
   `frontend/doctor.blade.php`.
4. Language: delete `app/Modules/Language/` + `resources/views/languages/`
   + `lang/*/messages.php` + `lang/bn/validation.php`. Then remove: the
   `LanguageServiceProvider` line in `bootstrap/providers.php`, the
   `SetLocale` line in `bootstrap/app.php`, the `Language` seeding/import in
   `DatabaseSeeder`, the guarded sidebar link and
   the `__()` calls in the frontend header/footer (they render raw keys
   like `messages.nav.home` once dictionaries are gone — replace with
   plain English). Review locale-related user fields/migrations separately.
5. Allergy: hooks are `Module::enabled('allergy')` guards — leave them;
   removing the column needs a migration.
6. Lockout: flag off removes the route throttle after config and route caches
   are cleared/rebuilt, and disables the per-account lockout.
   Nothing to delete.

The module tests verify disabled-flag behavior while module code is still
present. They do not prove that deleting a module's files is safe; run the
relevant tests and a fresh application boot after any permanent removal.

## What core always keeps (minimum)

Auth + roles, appointments (booking, slots, schedules, off-days),
doctors/patients/users, dashboard shell, frontend pages, contact/blog —
i.e. everything outside the folders above plus module integrations that are
explicitly guarded or retained as core functionality.
