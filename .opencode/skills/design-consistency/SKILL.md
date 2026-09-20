---
name: design-consistency
description: Website design consistency for MediCare Blade UI — shared tokens, toolbar/search/select standards, and component reuse. Use ONLY when user asks for design consistency, UI consistency, matching styles, or standardizing components across pages.
---

# Design-Consistency Skill (MediCare)

Keep every page looking like the same website. Fix the shared component, not the single page.

## The two skins (never mix on one view)

- Public frontend (`resources/views/frontend/*`): Bootstrap 5.3.2 + `resources/sass/app.scss`. Brand primary `#05d3b0`. Use `asset()` for uploads.
- Admin/doctor (`resources/views/backend/*`, `resources/views/doctor/*`): Tailwind v4 skin in `resources/css/admin.css` (loaded via `@vite(['resources/css/admin.css'])`). Reuse `mc-*` classes; do not paste raw utilities per view.

## Shared admin standards (`resources/css/admin.css`)

- Tokens (`@theme`): teal `#0b8f74`, teal-dk `#0a7c63`, bright `#05d3b0`, wash `#e6faf5`, ink `#1a1d21`, mut `#71717a`, faint `#a1a1aa`, line `#e9ebf0`, panel `#f4f6fa`, card `#fff`. Fonts: body `Public Sans`, display `Fraunces`.
- Toolbar: `.mc-bar` wraps the row. Width split 70 / 25 / 5 — search (`flex: 7 1 0%`, `min-w-[200px]`) / status select (`flex: 2.5 1 0%`, `min-w-[120px]`) / Filter button (`flex: 0.5 1 0%`, `min-w-[96px]`, `whitespace-nowrap`). Reset stays `flex-none` outside the ratio.
- Uniform control height: search container `min-h-[42px]`, selects `h-[42px]`, so search/select/buttons align on one baseline.
- Search inputs: the `input` itself is always borderless (`border: 0`, `outline: none`, `box-shadow: none`, including `:focus`/`:focus-visible` and Bootstrap `.form-control` inside `.mc-search`). The focus ring lives on the container via `:focus-within` (`border-teal` + soft `0 0 0 3px rgb(11 143 116 / 0.12)`).
- Table/cards/pills: `.mc-card`, `.mc-tbl`, `.mc-pill` + status classes (`p-pending`, `p-confirmed`, `p-completed`, `p-cancelled`, `p-paid`, `p-void`). Status colors: amber pending, green confirmed/paid/completed, grey cancelled/void; red only for requires-action.
- Pagination: `AppServiceProvider` sets Bootstrap pagination markup (`ul.pagination`), but Bootstrap CSS is not loaded in admin — so pagination is styled structurally under `.mc-pg` in `resources/css/admin.css` (34px pills, current page filled teal). Keep it there; don't add per-page pagination CSS.
- No `{!! !!}`, no inline `style=` unless truly dynamic (flex ratios like the search/select split are the accepted exception). Icons stay `bootstrap-icons`.

## Workflow

1. Before styling, grep for the existing component (e.g. `mc-search`) and reuse it. If 3+ pages need the same tweak, change `resources/css/admin.css`, not each Blade file.
2. Match heights, spacing, and radius to siblings on the same toolbar before inventing new values.
3. Blade hygiene: `{{ }}` escaped only, `@csrf` in forms, 4-space indent, LF.
4. Verify: `npm run build` must succeed after any CSS change; `php -l` any touched Blade/PHP; `php artisan test` if PHP changed. For visual changes, follow the `design-qa` skill (desktop + mobile screenshots, empty states).
