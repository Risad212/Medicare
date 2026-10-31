# Code Deletion Log

## [2026-10-31] Refactor Session

### Unused Files Deleted
- app/View/Components/Backend/SeoSettings.php - Unused View Component; the SEO settings partial is included directly via `@include('backend.components.seo-settings')`, so the component class was never referenced.
- app/Console/Commands/MigrateLabReportsToLocal.php - Unused console command; no route, schedule, or test references it.

### Dead Code Removed
- app/Modules/Analytics/Services/AnalyticsService.php - Replaced month arithmetic with `subMonthNoOverflow()` to avoid PHP month-rollover on the 31st.
- tests/Feature/AnalyticsTest.php - Aligned test fixtures with `subMonthNoOverflow()` so "last month" data stays in the previous month.

### Duplicate / Consolidation
- go-calculator/calculator.go - `cachedCalculator.mu` changed from `sync.RWMutex` to `sync.Mutex`; only `Lock`/`Unlock` were used, so the read-lock API was dead surface area.

### Impact
- Files deleted: 2
- Lines of code removed: ~70
- Tests: 348/348 passing (was 347/348 due to a date-overflow flake on Oct 31)

### Remaining (manual review needed)
- Auth controllers under app/Http/Controllers/Auth are referenced by `Auth::routes()` via convention, not explicit imports; verify before removing.
- resources/css/app.css is not part of the Vite build input; confirm it is intentionally unused.
- storage/logs and storage/framework/views contain stale artifacts; safe to purge but not source code.
