# Medicare — Extensible Design (Add Feature Without Break)

**Goal:** Add features to existing Laravel 13 project without breaking core.

## 1. Principles (Non-Breaking)
- **Backward compatible:** New migrations `nullable`/`default`, never rename/delete columns without `->after` + deprecate. Use `php artisan make:migration` with `up()` safe for existing data.
- **Whitelist validation:** Every new controller uses `FormRequest` + `$request->validated()` not `except()`/`all()`. Prevents mass assignment (`User:12` `role` fix).
- **Service layer:** Keep controllers thin — move logic to `app/Services/FeatureService.php`. Enables test without HTTP.
- **Feature flag:** Gate new route with `config('features.new_payment')` or `Route::middleware('feature:payment')` to disable if break.
- **No core overwrite:** Extend via `traits`, `observers`, `events`, `policies` — don't edit `Auth` core, use `Gate`/`Policy`.

## 2. Structure
```
app/
  Http/Requests/Feature/StoreRequest.php  # validate
  Http/Controllers/FeatureController.php  # thin
  Services/FeatureService.php             # business logic
  Models/Feature.php                      # fillable whitelist
  Policies/FeaturePolicy.php              # authorize
routes/
  web.php          # throttle:10,1 + middleware
  api.php          # Sanctum if API feature
resources/views/
  feature/         # Blade with {{ }} escaped, @csrf
tests/Feature/
  FeatureTest.php  # php artisan test
```

## 3. Steps to Add Feature (e.g., Payment, Report)
1. `php artisan make:model Feature -m` → migration `nullable` + `foreignId`
2. `php artisan make:request StoreFeatureRequest` → rules `required|exists|max`
3. `php artisan make:controller FeatureController --resource`
4. `php artisan make:policy FeaturePolicy`
5. Add route: `Route::resource('/admin/features', FeatureController::class)->middleware(['auth','admin','throttle:10,1'])`
6. Service: `app/Services/FeatureService::handle()` with transaction `DB::transaction`
7. View: `resources/views/feature/*` with `{{ }}`, not `{!! !!}`
8. Test: `php artisan make:test FeatureTest` → `assertStatus(200)`, `assertDatabaseHas`
9. Migrate: `php artisan migrate --force` (on server after `composer install`)
10. Cache: `php artisan config:clear && route:cache`

## 4. Existing Medicare Guards Already Done
- `User:12` no `role` fillable, `Doctor:24` LIKE escaped, `Appointment:25` validated + double-book check, `public/.htaccess:1` headers, `composer.lock` 13.29.0 clean
- Keep these patterns for new code.

## 5. Checklist Before Push
- [ ] `composer audit` 0, `npm audit` 0
- [ ] `php artisan test` pass (fix `general_settings` seed for test DB)
- [ ] `php -l` syntax OK
- [ ] `throttle`, `validate`, `authorize` added
- [ ] Migration `down()` reversible, no data loss
- [ ] No `adminer.php` in push (local only)

## 6. Example: Add Payment Feature Without Break
```php
// routes/web.php
Route::middleware(['auth','throttle:10,1'])->group(fn()=> Route::post('/payment', [PaymentController::class,'store']));

// app/Http/Requests/PaymentRequest.php
public function rules(){ return ['amount'=>'required|integer|min:1', 'appointment_id'=>'required|exists:appointments,id']; }

// app/Services/PaymentService.php
public function pay(array $data){ return DB::transaction(fn()=> Payment::create($data)); }
```
This keeps existing `appointment` flow untouched.

---
*Teams: PR review via `architecture` skill, security via `security` skill, docs via `laravel-docs`.*
