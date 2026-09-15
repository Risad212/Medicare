# Direction approval — MediCare admin dashboard redesign
- Shown: A (Editorial Brutalism, roulette #1) · B (Stripe-calm benchmark) · C (IDEO care timeline).
- Screenshots: design-demos/shot-a.png, shot-b.png, shot-c.png.
- Spec: design-demos/dashboard-spec.md. Brand: design-demos/brand-spec.md.
- User selection (verbatim): "A · Brutalist ledger".
- UPDATE: user rejected A after seeing it live ("don't like, want modern medical feel").
  Chose "Fresh: modern medical" — built directly into backend/home.blade.php
  (hero + ECG pulse, KPI cards, queue table, duty roster, voices, visit mix).
- Next: rebuild A as Blade `resources/views/backend/home.blade.php` with live data.
  Fix noted flaw: avatar circles clipping the table's left edge (add cell padding/overflow).
  Replace invented mock phones with real `$a->phone` + age.
