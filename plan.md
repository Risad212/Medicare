# MediCare — Frontend Quality Improvement Plan

> Created: 2026-10-19 · Branch: `laravel-blade-template` · Baseline: **315/315 phpunit tests green** after duty-roster page removal.

## 0. Grounded current-state findings (inspected, no assumptions)

| # | Finding | Evidence |
|---|---------|----------|
| F1 | **No `<h1>` exists in any frontend view** — breadcrumb partial renders `<h2 class="page-name">` and is included by 10 pages; section titles use `<h3>` | `resources/views/frontend/components/breadcrumb.blade.php:6`; only admin views contain `<h1>` |
| F2 | Appointment form uses **placeholders instead of labels**; only the date input has `aria-label`; `required` exists only on conditional email | `resources/views/frontend/appointment.blade.php:32-149`; server rules `required` on name/phone/doctor/date/slot/gender, `required_if:consultation_mode,telehealth` on email — `app/Http/Controllers/Frontend/AppointmentController.php:66-79` |
| F3 | Responsive CSS exists but only at **600 px / 768 px** breakpoints; topbar hidden ≤600px; hero locked to `height: 85vh` | `public/frontend-assets/css/main.css:211-311, 552` |
| F4 | **No JSON-LD / structured data** anywhere (0 matches, including the SEO settings partial) | grep `ld+json` across `resources/views` |
| F5 | Blog lives in the Publishing module (`publishing/site-blog*.blade.php`); verified-reviewer feature already exists (`DoctorReview`, `resources/views/reviews/`) | earlier session work |
| F6 | Service detail view is `resources/views/directory/site-services-show.blade.php` with card component `components-service-card.blade.php` | view files present |
| F7 | All user-visible strings must go through `__('messages.*')` — the `TranslateResponse` auto-translate middleware rewrites HTML text (skips `data-notranslate`) | `app/Http/Middleware/TranslateResponse.php` |
| F8 | Frontend = 11 Blade front pages (`frontend/` + `publishing/` + `directory/`), one layout (`front-app.blade.php`), one main.css | file inventory |

## 1. Summary of proposed improvements

1. **Mobile responsiveness** — add tablet/mobile rules, remove fixed-height hero on mobile, guarantee no horizontal overflow (`overflow-x` guard + fix specific offenders).
2. **Touch-friendly controls** — ≥44×44 px hit areas for nav links, buttons, form controls, dropdown items on ≤768 px.
3. **Page headings & hierarchy** — one meaningful `<h1>` per page (breadcrumb lever + missing pages), `h2` subdivisions, no skipped levels.
4. **Service → doctor navigation** — on service detail: "Doctors in this department" list link-out + "Book appointment" CTA reusing existing `/doctor?department=` search and appointment `?doctor_id=` preselect.
5. **Doctor profile enrichment** — surface *existing* DB fields (qualification, degrees, department, designations) in structured sections; render nothing (or honest "Not provided") when a field is empty. **No invented data.**
6. **Trust & credibility** — blog author block from real `user` relation; verified medical reviewer detail on doctor reviews (reuse `DoctorReview` fields).
7. **Structured data (Schema.org)** — `Organization` + `WebSite` site-wide in layout; `Physician` on doctor detail; `MedicalService`/`Service` on service detail; `BlogPosting` with author on posts; `BreadcrumbList` where breadcrumb is included. All values from real DB/settings data, conditionally output.
8. **Appointment UX** — real `<label>` ↔ `for`, HTML validation attributes mirroring server rules (`required`, `min`/`max` age, autocomplete, `inputmode`), accessible error summary, keyboard-visible focus; zero server-behavior change.
9. **Testing** — feature tests per stage (H1 presence, label/validation attrs, JSON-LD validity on pages, no plan regressions) + fixed manual viewport matrix.

## 2. Prioritized task list

| P | Task | Can start now? | Depends on |
|---|------|----------------|------------|
| **High** | T1 One-H1-per-page via `breadcrumb.blade.php` (h2→h1) + H1 on `appointment`, `profile`, `notifications`, homepage | Yes | — |
| **High** | T2 Touch-target & spacing CSS pass (≤768px), nav/buttons/forms/dropdowns | Yes | T3 for audit targets |
| **High** | T3 Overflow audit + fixes + `overflow-x: clip` guard (non-masking targeted) | Yes | — |
| **High** | T4 Appointment a11y labels/validation attrs (mirror `AppointmentController` rules exactly) | Yes | — |
| **Medium** | T5 Service detail → department doctors + booking CTA | Yes (check query params first) | T4 optional |
| **Medium** | T6 Doctor profile field surface + layout (conditional on data) | Yes | **Input A** |
| **Medium** | T7 Blog author block + verified-reviewer detail | Yes | **Input B** |
| Medium | T8 JSON-LD scoped JSON (Org/WebSite → Physician → Service → BlogPosting → BreadcrumbList) | Yes after T1/T4 | T1, T6, real-data confirm |
| **Low** | T9 Final regression sweep (suite + viewport matrix + heading order audit) | after stages 1-4 | all |

Task 2 of the user list is folded into T2/T3; task 9 is continuous per stage + T9.

## 3. Files / components affected

**Views (frontend):**
- `resources/views/frontend/components/breadcrumb.blade.php` (F1 lever)
- `resources/views/frontend/appointment.blade.php`, `appointment-cancel.blade.php`
- `resources/views/frontend/index.blade.php`, `about.blade.php`, `doctor.blade.php`, `doctors/show.blade.php`, `contact.blade.php`
- `resources/views/frontend/layouts/front-app.blade.php` + `partial/header.blade.php` + `partial/footer.blade.php`
- `resources/views/directory/site-service.blade.php`, `site-services-show.blade.php`, `components-service-card.blade.php`
- `resources/views/publishing/site-blog*.blade.php`, `site-faq.blade.php`, `site-gallery.blade.php`
- `resources/views/frontend/profile/index.blade.php`, `notifications/index.blade.php`

**CSS:** `public/frontend-assets/css/main.css` only (`mc-`/`mx-` scoped additions at file end; no cm build step — per repo convention).

**Backend (minimal, behavior-preserving):**
- `app/Http/Controllers/Frontend/DoctorController.php` (only if services need a `department` query-param link — param already exists in search filter UI)
- SEO/JSON-LD partial: new `resources/views/frontend/components/seo-jsonld.blade.php` (guarded, `!! !!` only for **pre-escaped** server-built JSON — skill rule)

**Lang:** `lang/{en,bn,ar,ru,zh,es,fr,de,it,pt,tr}/messages.php` for any new visible strings (11 files, translated, e.g. `labels.*`, `trust.verified_reviewer`).

**Tests (new):** `tests/Feature/FrontendA11yTest.php` (H1 per page, appointment labels/attrs), `tests/Feature/StructuredDataTest.php` (JSON-LD present & decodable, no empty required props).

## 4. Implementation sequence (stages)

```
Stage 1  H1 hierarchy + appointment form a11y + overflow guard/target CSS   [T1 T3 T4]
Stage 2  Service→doctor CTA + doctor profile field surface                  [T5 T6]
Stage 3  Trust blocks (blog author, verified reviewers)                     [T7]
Stage 4  JSON-LD structured data (Org/WebSite → pages) + lang keys          [T8]
Stage 5  Regression sweep + viewport matrix + heading audit + report        [T9]
```
Each stage: edit → `php artisan view:cache && view:clear` → new tests → full `vendor/bin/phpunit` → report.

## 5. Acceptance criteria & testing requirements

1. **Responsiveness** — at 320/375/600/768/1024/1440 px: no horizontal scrollbar on any front page (asserted per build with CSS audit; manual matrix in L2 row of tests table), tap targets ≥44px on ≤768px (CSS-encoded assertions via feature tests checking classes/where feasible).
2. **Headings** — every public page renders exactly one `<h1>` that names the page; `h2` level follows; automated `FrontendA11yTest` counts `<h1>` == 1 on: home, about, doctors, doctor.show, services, service.show, blog, blog.show, faq, gallery, contact, appointment (11 pages; appointment currently has none → added).
3. **Appointment** — each visible input has a bound `<label for>` or screen-reader-only label; `patient_name/phone/doctor_id/gender/appointment_date/time_slot_id/visit_type` carry `required` matching `AppointmentController:66-79`; email keeps conditional `required` JS logic; age input gains `min=0 max=120`; phone gains `inputmode="tel"`; server behavior and existing tests unchanged (existing `AppointmentTest` must stay green).
4. **Service→doctor** — service detail shows a real doctors-in-department link (`/doctor?department=<id>`, respecting `duty` deletion? duty untouched — removed already) **only** when matching doctors exist; CTA "Book appointment" ⇒ `/appointment?doctor_id=…` only when preselect param is supported; deep-link fallback = plain `/appointment`.
5. **Doctor profiles / trust** — no fabricated text; sections render only when data exists; reviewer block shows name/qualification as stored; `assertSee` tests use seeded demo values.
6. **Structured data** — JSON-LD blocks present on layout & 3 detail types; decodable via `json_decode`; contains no empty/`null` required props; unit/feature test validates structure; `data-notranslate` guard added so auto-translate never rewrites attribute-embedded JSON (JSON sits in `<script type="application/ld+json">` which `TranslateResponse` already skips via SKIP_TAGS `script`).
7. **No regressions** — full phpunit green after every stage; blade cache compiles; RTL/`dir` not broken; module flags (`language`, `publishing`) still gate markup.

## 6. Risks, dependencies, missing information

| Risk / gap | Mitigation |
|---|---|
| **Input A — doctor qualification/degree/experience data exist in DB?** Doctor model inspected partially; if columns exist but are empty in prod data, profile sections must render placeholders, not fake text | Check `doctors` migration + a sample seeder first; conditional rendering |
| **Input B — blog author convention:** posts may belong to admin user accounts, not clinicians | Render `post->user?->name` with role label ("MediCare editorial team") rather than inventing a clinician; verified-reviewer = `DoctorReview` verified-at data only |
| Auto-translate middleware rewrites text nodes (F7) — could mangle my new H1/labels on non-source locales | Keep keys in 11 `messages.php` files (source-of-truth), so translation output is stable |
| `overflow-x: hidden` on `body` masks bugs | Use `overflow-x: clip` on `html`/`body` **plus** fix specific offenders; guard is last line, not the fix |
| 85vh hero on short screens hides content | Only change `@media` ranges, never base desktop look |
| Touch-target bump may crowd ≤360 px dense areas (topbar/footer socials) | Enlarge selectively (interactive elements), test at 320px |
| JSON-LD dead keys if settings absent (logo/address) | Emit JSON-LD sections only when their data completes (`if` guards) |
| Playwright/browser automation not set up in repo | L2 matrix stays manual (documented checklist); PHP tests cover structural checks |

## 7. "Next task" checklist (Stage 1)

- [x] T1 breadcrumb h2→h1 + per-page H1 audit list
- [x] T4 appointment labels/attrs mirroring validation
- [x] T3 small overflow audit patches (`hero` ≤768px, guard rule, button/nav/select hit-areas ≤768px)
- [x] New `FrontendA11yTest` (2 tests) + full suite green — **VERIFIED 2026-10-19: 317/317 passed (1380 assertions); blade cache compiles**
- [ ] Stage 2: T5/T6 (needs Input A check for doctor data columns)
- [ ] Stage 3: T7 (needs Input B author convention decision)
- [ ] Stage 4: T8 JSON-LD
- [ ] Stage 5: T9 sweep + final report

### Stage 1 verification record
- `vendor/bin/phpunit` → **317 passed / 0 failed** (315 baseline + 2 new `FrontendA11yTest`)
- `php artisan view:cache && view:clear` → compiles clean
- H1 coverage: 10 breadcrumb pages + home first-slide title (+ appointment/profile/notifications via breadcrumb includes) = exactly one `<h1>` per page (test-enforced for 7 guest routes)
- Appointment form: bound `mc-sr-only` labels on all 10 controls; `required` mirrors `AppointmentController::store`; age `min=0 max=120`; phone `inputmode=tel pattern maxlength=20`; autocompletes added; alert boxes get `role`/`aria-live`
