---
name: tailwind-css
description: Tailwind CSS v4 work — converting/building the MediCare admin (Vali-admin) UI with Tailwind, design tokens, Vite integration, component patterns, and parity migration of mc-* / Bootstrap classes. Use ONLY when writing Tailwind CSS, configuring tailwind via Vite/PostCSS, converting Blade admin views to utility classes, or matching the existing clinical-calm design system.
---

# Tailwind CSS v4 — MediCare Admin Conversion

Utility-first CSS conversion for the Laravel admin panel. The app runs Tailwind
**v4** with the **Vite plugin** (`@tailwindcss/vite`), already in
`package.json`.

## Setup (repo facts)

- File: `tailwind.config.js` — **do NOT create one**; v4 is config-less (CSS-first via `@theme`).
- Entry: add an admin stylesheet to `vite.config.js` `input` (e.g. `resources/css/admin.css`) and load `@vite(['resources/css/admin.css'])` in `backend/layouts/app.blade.php`.
- Brand tokens (from `public/backend-assets/css/mc-admin.css`):
  - `--mc-teal:#0b8f74`, `--mc-teal-dk:#0a7c63`, `--mc-bright:#05d3b0`, `--mc-wash:#e6faf5`
  - `--mc-ink:#18181b`, `--mc-ink2:#3f3f46`, `--mc-mut:#71717a`, `--mc-faint:#a1a1aa`
  - `--mc-bg:#fafafa`, `--mc-card:#fff`, `--mc-line:#e4e4e7`, `--mc-line2:#f0f0f2`
  - status: amber `#d97706/#92400e/#fef3c7`, green `#03695c/#dcf7ef`, red `#dc2626/#991b1b/#fef2f2`, blue `#1d4ed8/#eff6ff`
  - `--mc-r:12px` radius, shadow `0 1px 2px rgba(24,24,27,.05) 0 6px 18px rgba(24,24,27,.05)`
  - fonts: display `Fraunces`, body `Public Sans` (via Google Fonts link in the layout head)
- Map these into `@theme { --color-*: ...; --font-display: ...; }` so classes are `bg-teal`, `text-ink`, `bg-wash`, `font-display`, `rounded-[12px]`.

## Converter mapper (Source → Tailwind)

Convert source classes to utilities; the mapping below is the standard
reference for page-by-page migration:

| Source class | Tailwind equivalent |
|---|---|
| `mc-card` | `rounded-xl border border-zinc-200 bg-white shadow-sm overflow-hidden` |
| `mc-tbl` / `th` / `td` | `w-full border-collapse` + `text-[11px] uppercase tracking-wide text-zinc-500` th / `px-4 py-3 border-b border-zinc-100 align-middle` td |
| `mc-btn` | `inline-flex items-center gap-2 rounded-[10px] bg-teal text-white font-bold px-5 py-2.5 hover:bg-teal-dk shadow-sm` |
| `mc-btn.ghost` | `bg-white border border-zinc-200 text-zinc-900 hover:border-teal-dk hover:text-teal-dk` |
| `mc-btn.sm` | `text-xs px-3 py-1.5 rounded-lg` |
| `mc-pill` / `.p-*` | `inline-flex items-center gap-1.5 rounded-full text-xs font-bold px-3 py-1` + dot `<i class="h-1.5 w-1.5 rounded-full bg-current">` |
| `mc-head` | `flex flex-wrap items-end justify-between gap-4` |
| `mc-title` / `em` | `font-display text-[38px] font-bold tracking-tight leading-[1.05]` + `text-teal` |
| `mc-kicker` | `text-xs font-bold uppercase tracking-widest text-teal-dk` |
| `mc-sub` | `text-zinc-500 mt-2 max-w-[620px]` |
| `mc-bar` / `mc-search` | `flex flex-wrap items-center gap-2 rounded-xl border border-zinc-200 bg-white p-3 shadow-sm mb-3` / `flex-1 min-w-[200px] flex items-center gap-2 rounded-lg border px-3` |
| `mc-grid` / `mc-side` | `grid grid-cols-[300px_1fr] gap-4 items-start` (1 col <900px) / `rounded-xl bg-zinc-900 text-white p-5 sticky top-4 shadow-sm` |
| `mc-sec` / `hd` / `bd` | `rounded-xl border bg-white shadow-sm mb-4 overflow-hidden` / header `p-3.5 border-b bg-zinc-50 flex items-baseline gap-2.5` / body `p-4.5 grid grid-cols-2 gap-3.5` (1 col <640px) |
| `mc-f` | `flex flex-col gap-1.5` + label `text-xs font-bold text-zinc-700` |
| `mc-empty` | `py-11 text-center text-zinc-500` |
| `mc-pg` | `flex flex-wrap items-center justify-between gap-3 px-4 py-3 border-t text-sm text-zinc-500` |
| `mc-av` / `.r/.t` | `h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold` + bg variant |
| `mc-whob` | `flex items-center gap-3 min-w-[160px]` |

Bootstrap source classes (legacy views): `card` → `rounded-xl border border-zinc-200 bg-white shadow-sm`;
`btn btn-primary` → `bg-teal hover:bg-teal-dk text-white rounded-lg px-4 py-2 font-bold`;
`badge bg-*` → status pill; `table` → `mc-tbl` equivalents; `nav-tabs`/`tab-content`
→ a small Tab component (keeps `.active` state via Alpine `x-show`).

## Rules

- Whitespace/indent: 4 spaces, LF — same as PHP/Blade (`archive.editor`).
- Keep semantics + IDs/hooks used by `main.js` (sidebar `data-toggle`, treeview
  `.is-expanded`, `data-bs-toggle`) OR replace JS fully in the same commit —
  never strand half-working markup.
- Use `@layer components` with `@apply` for repeated components (btn, pill,
  card, tbl) instead of pasting the same 8 classes everywhere. `@utility` for
  one-offs like `.card`.
- No `{!! !!}` inline styles unless truly dynamic; prefer utility classes +
  CSS vars.
- Icons: keep `bootstrap-icons` CDN (or swap to inline SVG) — do not change
  icon source mid-migration.
- Never mix new `mc-*` skin and Tailwind on the same view — a view is either
  migrated or not.
- After each phase: `npm run build` (or `npm run dev` visual check),
  `php artisan test`, `composer run dusk` if layout/JS changed, `vendor/bin/pint --test` only if PHP touched.

## Verification

- `npm run build` — must succeed; watch for Tailwind scan warnings.
- `php artisan test` — full suite green (221 tests baseline).
- `composer run dusk` — 5 e2e tests; run after any layout / JS change.
- Visual: admin dashboard + one CRUD flow (e.g. blood requests approve→issue)
  on `npm run dev`; confirm sidebar toggle, treeview expand/collapse, dropdowns,
  tabs, pagination still work.
- `php -l` / `vendor/bin/pint --test` if Blade/PHP touched alongside views.

## Scope notes for this repo

- Admin shell + ~78 Blade views under `resources/views/backend/**` plus
  `resources/views/doctor/**` convert with this skin.
- Baseline test = 221 PHPUnit + 5 Dusk; keep green after conversion.
- Vite inputs currently only serve the **public** frontend (`resources/sass/app.scss`,
  `resources/js/app.js`). Admin loads from `public/backend-assets/` directly —
  move admin to Vite as part of the conversion.