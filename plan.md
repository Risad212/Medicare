# Future Features Roadmap (build later, not now)

Missing features vs. a complete hospital site, in build order. Each phase is
independently shippable: FormRequest + validated(), thin controller,
app/Services + DB::transaction, Policy/Gate, throttle:10,1 on public POSTs,
additive migrations, Pint + full suite green before merge.

## Phase 1 — SMS notifications
- SMS driver interface (D7 Networks / BulkSMSBD / Twilio)
- Appointment reminders, OTP option, status-change alerts
- Per-user channel preference; log to activity feed
- Extends existing `SendAppointmentReminders` command

## Phase 2 — Patient experience
- Doctor reviews/ratings (moderated, one per completed appointment)
- Public weekly duty-roster view per doctor/department
- Patient medical timeline (allergies, chronic conditions, visit history)
- FAQ + Gallery pages (Publishing module extension)

## Phase 3 — Operations
- Receptionist role (scoped admin: appointments + patients only)
- Discharge summaries / IPD records
- Ambulance live tracking link
- Newsletter subscription + campaign send

## Phase 4 — Polish
- Translation cache warmer (`translate:warm {locales}`)
- Locale-prefixed URLs (`/bn/doctor`) for non-English SEO
- Curated DB-content translation via `spatie/laravel-translatable`
  (doctor bios, service descriptions) replacing machine output

## Acceptance bar (every phase)
- Tests cover new endpoints incl. negative auth cases
- `vendor/bin/pint --test`, `php artisan test`, `composer audit` clean
- `php artisan release:readiness` 0 failing
- Docs updated (README feature list + this file marked done)
