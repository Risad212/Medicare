---
name: ui-ux-design
description: Design or restyle MediCare hospital UI (Blade views, Bootstrap 5, admin panel, patient profile, CSS). Use ONLY when the user asks for UI/UX design, redesign, styling, theming, or frontend look-and-feel work.
---

# MediCare UI/UX Design

Brand and rules for all interface work in this repo (Laravel 13 + Bootstrap 5 hospital site).

## Brand tokens

- Primary: `#05d3b0` (teal-bright). Deep clinical teal: `#0e7c66` / `#0b5c4c`.
- Ink: `#16202b`, secondary text `#64748b`, faint `#94a3b8`.
- Surfaces: content bg `#f2f5f9`, cards `#ffffff`, borders `#e3e9f0` / `#eef2f7`.
- Status: success green, warning amber `#92400e` on `#fef3c7`, danger `#d92d20` on `#fef1f0`.
- Radius: 10–16px cards/inputs, 999px pills/badges. Shadows soft and subtle.

## Hard rules

- Solid fills only — never use decorative background gradients in admin or patient UI (data-viz charts excluded). Flat clinical surfaces.
- Blade: `{{ }}` escaped output only, never `{!! !!}` (blog body is pre-sanitized server-side). `@csrf` in every POST form. Always use `asset()` for URLs.
- Admin shell lives in `public/backend-assets/css/admin-brand.css` (plain CSS, no build step) + `resources/views/backend/layouts/partial/header.blade.php`. Never break the `main.js` hooks: `data-toggle="sidebar"`, `data-toggle="treeview"`, `.is-expanded`, `.admin-sidebar`, `.admin-topbar`, `.admin-content`, `.sidebar-overlay`, `body.sidenav-toggled`.
- Frontend patient views live under `resources/views/frontend/` (layout `frontend.layouts.front-app`, Bootstrap + `frontend-assets/`). Keep brand font/scale consistent; avoid oversized extra-bold display type (prefer 600–700 weights).
- Scope styles per view (e.g. `.mx-`, `.mc-` prefixes) so a redesign never leaks into other pages.
- After CSS/Blade changes: `php artisan view:cache` then `view:clear` to verify compilation; run `vendor/bin/phpunit` before finishing.

## File map

- Admin shell CSS: `public/backend-assets/css/admin-brand.css`
- Admin shell markup: `resources/views/backend/layouts/app.blade.php`, `.../layouts/partial/header.blade.php`
- Admin sections: `resources/views/backend/*/` (30 folders, all inherit the shell)
- Patient profile: `resources/views/frontend/profile/index.blade.php`
- Frontend layout: `resources/views/frontend/layouts/front-app.blade.php`

## When to use me

Use when the user asks to design, redesign, restyle, modernize, or polish any UI in this repo. Ask which surface (admin panel, patient profile, public page) if unclear. Do not use for backend logic, migrations, or test work.
