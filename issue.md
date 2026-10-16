# MediCare — Issue Tracker (AI fix specs)

Generated from full feature review. Severity: P0 breaks booking, P1 data/security/UX, P2 hygiene.
Conventions: FormRequest + `$validated()`, thin controller → `app/Services` + `DB::transaction`, `throttle:10,1` on public POSTs, `{{ }}` escaped, `vendor/bin/pint --test` before push.

## P0-1 Zoom consultation_mode missing on doctor-detail form
- Where: `resources/views/doctors/show.blade.php:67-178` vs `app/Http/Controllers/Frontend/AppointmentController.php:70-72`, ref `resources/views/frontend/appointment.blade.php:47-55`
- Problem: detail form has no `consultation_mode` input, store requires it when `Module::enabled('zoom')` → 422.
- Do: add `<select name="consultation_mode">` (in_person/telehealth) gated by Zoom flag, with `old()` + `@error`, plus email required-toggle JS.
- Accept: Zoom on → in_person books without email, telehealth requires email. Zoom off → no field, books.
- Verify: `php artisan test --filter=Appointment`, `vendor/bin/pint --test`

## P0-2 Phone input type mangles intl numbers
- Where: `doctors/show.blade.php:130-133` vs `AppointmentController.php:68`
- Do: `type="number"` → `type="tel"` + `inputmode="tel"` + `pattern="[0-9+\-\s]+"`, keep server regex, preserve `old()`, show `@error('phone')`.
- Accept: `+880 1xxx` submits intact.

## P0-3 Inactive-doctor picker mismatch
- Where: `AppointmentController.php:181` vs `:80`
- Do: `Doctor::find` → `Doctor::where(status,1)->find()`. Return same `{slots,bookedSlotIds,unavailableSlotIds}` shape when null.
- Accept: inactive doctor shows unavailable, store still 404.

## P1-4 Cancel button hidden for approved
- Where: `frontend/profile/index.blade.php:232-238` vs `AppointmentController.php:228-250`
- Do: `@if status==0` → `@if in_array(status,[0,1])` for Cancel. Keep Join-Zoom only approved telehealth.
- Accept: approved in-person shows Cancel.

## P1-5 Appointment history lacks email fallback
- Where: `Frontend/ProfileController.php:27-30`, ref `:48-84`
- Do: mirror lab/Rx pattern: `where(user_id) OR (verified email match)`.
- Accept: post-link guest history visible; unverified sees nothing.

## P1-6 Blog stored-XSS surface
- Where: `blogs/show.blade.php:43`, `App\Services\BlogSanitizer`
- Do: sanitize display or use `{{ }}`. No `{!! !!}` for user content.
- Accept: `<script>` inert.

## P1-7 Contact mail sync
- Where: `Frontend/ContactController.php:42-44`
- Do: `Mail::send` → `Mail::queue`, add `string|max` rules, keep `throttle:10,1`.
- Accept: SMTP down still redirects success.

## P1-8 Guest linker exact-email only
- Where: `App\Services\GuestRecordLinker.php:24-33`
- Do: normalize `strtolower(trim())`, extend to prescriptions/invoices, keep `DB::transaction` + module guards.
- Accept: `Test@X.com ` links `test@x.com`.

## P1-9 Admin hard-delete bypass
- Where: `Admin/AppointmentController.php:324-330`
- Do: refuse delete if `status in [0,1]` or convert to cancel `status=3` + notifiers.
- Accept: active cannot vanish silently.

## P1-10 Auth hardening
- Where: `User.php:31`, `LoginController.php:32`, `*Middleware.php:22`
- Do: remove `role` from `$fillable`, set `$redirectTo='/'`, wrong-role → `abort(403)`, add throttle to password-reset routes.
- Accept: patient `POST /admin/*` → 403.

## P2 hygiene backlog
- Unrouted `ExportController@labOrders:76-100`, `Invoice@createFromOrder:64-70`; dead `Admin/Blog@show:71-74`; orphan `backend/comments/edit.blade.php`; settings doctor/blog/contact/appointment GET-only.
- File leaks on overwrite: `Admin/ServiceController.php:80-82`, `General/Home/About/ServiceSettingController`; slider `Storage::delete('public/')` inconsistent — use `Storage::disk('public')->delete()`.
- No FK guard on slots/depts/cats/tags delete; patient `show` over-matches (`PatientController.php:59-66`).
- Past-slot fail-open on unparseable time; cancel expiry date-midnight (~57h late); `lockForUpdate()->exists()` 0-row gap — index is real guard.
- Hardcoded English flashes/views; footer `#` links; dashboard in-memory grouping; unbounded doctor `get()`; Rx picker `limit(200)`; dispense keyed by exact name.

## Additional existing-app issues (verified 2026-10-14, full suite 160 passed)
- PROD-1 `APP_DEBUG=true` in both `.env` and `.env.example`, `QUEUE_CONNECTION=database`. Do: set `APP_DEBUG=false` + `APP_ENV=production` in prod env, keep example safe. Backup download is SAFE (allowlist `BackupController.php:16-23`), CSRF present in all forms (grep 100 hits).
- AUTH-1 Password reset/forgot from `Auth::routes` has no explicit `throttle:10,1` unlike contact/appointment pattern (`routes/web.php:54`). Do: wrap with throttle.
- AUTH-2 No OAuth unlink / set-password for Google users (random unknown password `GoogleAuthController.php:94`). Do: add set-password via reset flow doc or `DELETE /auth/google`.
- NOTIF-1 `NotificationController.php:44` returns 403 not 404 for others' IDs (existence oracle); `markAsRead` unthrottled; per-tab 60s poll unthrottled. Do: 404 + `throttle:30,1`.
- DOC-1 Dashboard mislabels `status=3 Cancelled` as Completed (`dashboard.blade.php:79-81`); counts include cancelled (`DashboardController.php:22-30`). Do: separate pill + exclude cancelled from today/total.
- DOC-2 `DoctorProfileController.php:13,20` uses `first()` not `firstOrFail()` — orphan doctor fatals. Do: `firstOrFail()`. Profile form omits `services/availability` inputs yet request saves them (`profile.blade.php:36-63`).
- DOC-3 Unbounded `DashboardController.php:50-54 appointments()->get()` — paginate like prescriptions (`paginate(10)`).
- RX-1 `PrescriptionController.php:42-47,96-101` picker `limit(200)` no search; same pattern in Beds/Vaccination (`BedController.php:30`, `VaccinationController.php:37-38,60-61`). Do: searchable paginate or ajax select.
- RX-2 Notify only on store (`PrescriptionController.php:57-70`); update/destroy silent. Do: re-notify on update, notify on destroy.
- RX-3 Dispense history keyed by exact `medicine_name` (`PrescriptionService.php:113-116,130`) — rename drops history. Do: key by `medicine_id` or normalized lower-trim.
- MOD-1 Phantom `allergy` flag (`config/modules.php:36`) with no `app/Modules/Allergy/`; Search/Analytics have no routes/providers — not detachable. Do: document or scaffold providers.
- ZOOM-1 Legacy rows `zoom_meetings.sync_status=null` show perpetual pending (`appointments.blade.php:45`). Do: seed default + null-coalesce.
- BLOG-1 Comment allowed on unpublished posts (`BlogCommentController.php:14 findOrFail` ignores status); tag filter substring (`BlogController.php:22 like %tag%`); no honeypot. Do: scope to `status=1`, exact tag match, add honeypot.
- PROF-1 `profile/index.blade.php:114` hardcoded `Verified patient` ignores `email_verified_at`; Google `avatar` never displayed vs `profile_image`; old image delete without `exists()`, no resize.
- DASH-1 `AdminController.php:53-59,121-138` in-memory `get()->groupBy` + unfiltered `hospitalEarning:153-157`. Do: DB aggregates with date scopes.
