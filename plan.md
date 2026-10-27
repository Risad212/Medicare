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
- [x] Stage 2: T5/T6 (needs Input A check for doctor data columns) — **RESOLVED 2026-10-21: doctors has populated `degree/department/specialist/services/availability/phone`; services had NO department relation → nullable `services.department` added (migration 2026_10_21_000001), admin form department select, demo Cardiology service linked**
- [x] Stage 3: T7 (needs Input B author convention decision) — **Input B resolved per §6: stored `blogs.author` string surfaced (admin name at publish time / "MediCare Team"), never an invented clinician; verified reviewer = `doctor_reviews.appointment_id` FK (required+unique, patient-flow only)**
- [x] Stage 4: T8 JSON-LD — partial `frontend/components/seo-jsonld.blade.php`; Org+WebSite in layout; Physician/MedicalService/BlogPosting/BreadcrumbList per page; all conditional on real data
- [x] Stage 5: T9 sweep + final report — **337/337 passed, cache compiles, h1 audit 12/12 pages**

### Stage 1 verification record
- `vendor/bin/phpunit` → **317 passed / 0 failed** (315 baseline + 2 new `FrontendA11yTest`)
- `php artisan view:cache && view:clear` → compiles clean
- H1 coverage: 10 breadcrumb pages + home first-slide title (+ appointment/profile/notifications via breadcrumb includes) = exactly one `<h1>` per page (test-enforced for 7 guest routes)
- Appointment form: bound `mc-sr-only` labels on all 10 controls; `required` mirrors `AppointmentController::store`; age `min=0 max=120`; phone `inputmode=tel pattern maxlength=20`; autocompletes added; alert boxes get `role`/`aria-live`

### Stage 2 verification record (2026-10-21)
- `vendor/bin/phpunit` → **325 passed / 0 failed** (317 + 8 new `ServiceDoctorNavigationTest`); blade cache compiles
- T5: `services.department` nullable string + fillable; admin `_form` select from `Department` names (free-text fallback when none exist); admin store/update validation `nullable|string|max:100`
- T5: service detail lists "Doctors in this department" (`hospital-doctors` section) only when the linked department has active doctors; per-doctor "View profile" + booking deep-link `/appointment?doctor_id=N`
- T5: appointment page `?doctor_id=` preselect via guarded JS (URLSearchParams; applies only to an empty select; stale ids ignored; server untouched)
- T6: doctor detail rebuilt — `h2` name, specialist subtitle, `<dl class="mc-doctor-facts">` rows only for populated fields, `data-notranslate` kept on stored values, breadcrumb `h1` unchanged; empty-profile doctor renders no facts list (no invented text)
- Seeder: demo Cardiology service linked to Cardiology department (dev DB patched too); other demo services stay unlinked to exercise the hidden path
- New lang groups `doctor_profile.*` + `service_detail.*` added to **all 11** message files (translated); `LocalizationCoverageTest` parity unaffected

### Stage 3 verification record (2026-10-21)
- `frontend/doctors/show.blade.php` review cards: "Verified patient" badge on every review (`doctor_reviews.appointment_id` is a required unique FK, created only by the own-completed-appointment flow — no other review path exists)
- `site-blogs-show.blade.php`: author meta item now conditional (`trim(author) !== ''`) with role label `trust.editorial_team`, stored `data-notranslate`
- `site-blog.blade.php` list cards: "By :name" via `trust.by_author` key; removed invented `?? 'Unknown'` fallback
- New lang group `trust.*` added to **all 11** files; badge/role CSS appended to `main.css`
- `vendor/bin/phpunit` → trust stage local: 5/5 (`FrontendTrustTest`)

### Stage 4 verification record (2026-10-21)
- New partial `frontend/components/seo-jsonld.blade.php`: callers pass a pre-built array; payload printed `{!! !!}` only after `json_encode` with `JSON_HEX_TAG|AMP|APOS|QUOT` (cannot emit `</script>`)
- Layout (`front-app`): `Organization` (name/logo/address/contactPoint when set) + `WebSite`, guarded by real `GeneralSetting` data
- `breadcrumb.blade.php`: `BreadcrumbList` (Home → page title) on the 10 pages that include it
- `doctor.show`: `Physician` (name/url/medicalSpecialty/image/telephone/aggregateRating — each present only when stored data exists)
- `service.show`: `MedicalService` (title/limited description/provider from settings)
- `blog.show`: `BlogPosting` (headline/dates/image = stored author/publisher) — blog post title demoted h1→h2 (both h1s violation; CSS class-based, unaffected)
- `vendor/bin/phpunit` → **337 passed / 0 failed** (325 + 5 `FrontendTrustTest` + 7 `StructuredDataTest`)

### Stage 5 verification record (2026-10-21)
- Full `vendor/bin/phpunit` → **337 passed / 0 failed** (1549 assertions); `php artisan view:cache && view:clear` → compiles clean
- Heading audit (12 public pages): exactly one `<h1>` everywhere; `h2` follows inside the page body (blog.show fixed from 2×h1); deep h4/h5 usage inside card/sidebar components is pre-existing template furniture, listed as follow-up polish
- RTL: no `dir` changes made; JSON-LD sits in `<script>` + `data-notranslate` so auto-translate middleware cannot touch it
- Viewport matrix (manual, per plan §6 — no browser automation in repo): checklist below; all CSS deltas are additive at `main.css` end and never alter base desktop rules
  - 320px: topbar hidden by template design; nav links ≥44px (Stage 1 rule); `overflow-x: clip` guard active; dept doctor cards stack 1-col; facts list rows stack via col-5/col-7 (fits 320px)
  - 375px / 600px: hero 62vh (Stage 1 pulse); service detail single column; "Book an Appointment" CTA full-width tap ≥44px
  - 768px: tablet breakpoint (Stage 1 nav/tablet rules); doctor facts 4/8 split; verified badge inline fits
  - 1024px / 1440px: unchanged desktop look (only additive mc- rules loaded)
  - RTL Arabic spot check: `dir` untouched; facts list uses row grid that flips with RTL body direction (Bootstrap row-based, no fixed lefts)
- Plan complete: T1–T9 all implemented and verified; suite totals 317 → 337 across the whole plan

### Stage 6: follow-up polish record (2026-10-21, post-completion)
- Committed plan stages 2–5 work as `9cbe438` (30 files, +1113/−43)
- Follow-up a — blog detail (publishing/site-blogs-show.blade.php): comment/sidebar heading order fixed h1→h2(post)→h3(sections: comments count, comment form, recent posts, blog categories, popular tags)→h4(comment authors, recent post titles); all sidebar/comment strings localized via new `blog_page.*` lang group ×11; comment form inputs gained sr-only bound labels (`comment_name/_email/_text` ids), maxlength + required mirroring StoreBlogCommentRequest
- Follow-up b — doctor detail embedded booking form now mirrors AppointmentController exactly: visible labels bound with for/id (9 ids incl. `doctor_name_display`, `patient_gender`), all server-required fields carry `required` (+ disabled placeholder options for visit_type/gender/time_slot where the browser would otherwise silently preselect), maxlength/pattern/autocompletes/age min–max/date min floor; heading + button + placeholders localized via 3 new `appointment.*` keys (`doctor_name`, `name_placeholder`, `time_slot_label`) ×11; email-required JS flow unchanged
- New coverage: FrontendA11yTest::test_doctor_detail_booking_form_labels_and_mirrored_constraints (checks visible text only — scripts/JSON-LD stripped — so JS identifiers cannot count as hardcoded strings)
- Verification: full suite **338 passed / 0 failed** (1597 assertions); heading audit 12/12 pages one-h1, blog.show outline now skip-free `1·2·3·4`; blade cache compiles
- Remaining known follow-ups: browser-automation viewport matrix (no Playwright in repo), department listing pages (`/department/{name}`), service–doctor relation shown bidirectionally on doctor profile

### Stage 7: department listing pages (2026-10-27, post-completion)
- New route `GET /department/{name}` → `Frontend\DoctorController::department` (route name `department.show`); 404 when trimmed name is empty or nothing matches; exact-name match, consistent with the existing service→department doctor query
- New view `frontend/department.blade.php`: breadcrumb h1 = department name, doctors section (`hospital-doctors` card style with View profile + Book appointment deep links), empty state via existing `public.no_doctors`, related-services section reuses `directory.components-service-card`, meta description comes from the stored `departments` row (status 1, description-trimmed)
- Service detail page: department doctors section now carries a localized "View all" link to `/department/{name}` (reuses `public.view_all`); controller passes the department string through
- New localized key `service_detail.related_services` ×11 (+php -l clean)
- New coverage: `DepartmentPageTest` (8 tests — active-doctor filtering, service matching, empty/404 flows, view-all link, single h1 outline, stored meta description)
- 346 tests / 1623 assertions; pint clean
