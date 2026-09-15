---
name: design-qa
description: Visual QA for Blade UI, dashboard redesigns, screenshots, responsive and accessibility checks. Use ONLY when user asks for design QA, visual review, or UI verification.
---

# Design-QA Skill (MediCare Blade)

Use for visual review of Blade views (`resources/views/backend/*`, `frontend/*`).

## Render + screenshot
- `php artisan view:clear` before checking (stale compiled views hide fixes).
- Render check: `php artisan tinker --execute="auth()->setUser(\App\Models\User::where('role','admin')->first()); echo strlen(app(\App\Http\Controllers\AdminController::class)->index()->render());"`
- Screenshot: `npx --yes playwright@1.63.0 screenshot "file:///.../page.html" out.png --viewport-size=1440,900` (+ 390x844 mobile pass).
- Read the PNG yourself before presenting — never ship an unseen UI.

## Standards
- Brand: teal `#05d3b0`, deep `#046b5a`; no purple gradients, no emoji icons, no SVG faces (initials only).
- Status enum: `0 Pending amber · 1 Confirmed green · 2 Completed teal · 3 Cancelled grey`; red = requires-action only.
- Type: body ≥14px, labels ≥12px, contrast ≥4.5:1; avatars need `?` fallback for empty names.
- New styles must be page-scoped (`.med-` prefix or page `<style>`); never edit `public/backend-assets/css/main.css` (shared theme).

## Blade gotchas (caused real 500s)
- Always space directives off text: `@endif Pending`, `today @if(` — `@endifPending` / `today@if(` do NOT compile (open `if(` → end-of-file ParseError).
- `php artisan view:cache` passes even with broken views (it only transforms, PHP parses at render) — always test-render + `php -l` the compiled file in `storage/framework/views/`.

## Checklist
- [ ] Desktop + mobile screenshots inspected
- [ ] Empty states, long names, zero-counts render sane
- [ ] No console errors, no external requests (offline file:// identical)
- [ ] `php artisan test` green after change
