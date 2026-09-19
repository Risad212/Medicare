# MediCare — System Architecture Overview

> Visual companion to [ARCHITECTURE.md](ARCHITECTURE.md) (how to **extend** the app without breaking it).
> This file explains how the app **is built today**: layers, request flow, roles, panels, data model, and key lifecycles.
> Diagrams are [Mermaid](https://mermaid.js.org) — they render on GitHub / GitLab / VS Code.

---

## 1. Layered architecture

Every feature is split into the same five layers. HTTP never talks to the database directly; Blade never contains business logic.

```mermaid
flowchart TB
    Browser["Browser\n(Blade + Tailwind + jQuery)"]
    Routes["routes/web.php\n(all routes, grouped by middleware)"]
    MW["Middleware\n(auth · admin · doctor · throttle · SecurityHeaders)"]
    Ctrl["Controllers\napp/Http/Controllers/\n(thin: validate → delegate → respond)"]
    Svc["Services\napp/Services/\n(business logic + DB::transaction)"]
    Models["Models\napp/Models/ (33 Eloquent models)"]
    DB[("SQLite dev/test\nMySQL prod")]
    Obs["Observers\n(ActivityLogObserver)"]
    Mail["Mail + Notifications\n(queueable, async)"]

    Browser --> Routes --> MW --> Ctrl --> Svc --> Models --> DB
    Models -.-> Obs --> DB
    Svc -.-> Mail
    Ctrl --> Browser
```

**Layer rules**

| Layer | May do | Must NOT do |
|---|---|---|
| `routes/web.php` | Group by middleware, `throttle:10,1` on public POSTs | Contain logic |
| Controller | `$request->validated()`, call Service, return view/redirect | Write 3 tables, send mail, use `all()` |
| Service | Transactions, stock moves, emails, CSV, guards | Return views |
| Model | Relations, scopes, casts, constants | Send mail, touch session |
| Blade | Display only, `{{ }}` escaped, `@csrf` | Queries, `{!! !!}` |

---

## 2. Request lifecycle (sequence)

What happens from click to pixels — e.g. admin approving an appointment:

```mermaid
sequenceDiagram
    participant B as Browser
    participant R as routes/web.php
    participant M as Middleware (auth+admin)
    participant C as AppointmentController
    participant S as AppointmentNotifier (Service)
    participant DB as Database
    participant V as Blade view

    B->>R: PATCH /admin/appointments/5/status
    R->>M: check auth + role=admin + throttle
    M->>C: updateStatus(validated data)
    C->>S: changeStatus(appointment, 1)
    S->>DB: BEGIN → update status → log activity → COMMIT
    S-->>B: queue confirmation mail (async)
    C->>V: redirect back + success flash
    V-->>B: HTML (Welly skin)
```

---

## 3. Roles & access control

One `users` table, three roles. Login redirects by role; every staff route is middleware-gated.

```mermaid
flowchart LR
    Login["Login / Google OAuth"] --> Role{"users.role"}
    Role -->|admin| Admin["/admin/*\nAdmin panel (Welly skin)"]
    Role -->|doctor| Doctor["/doctor/*\nDoctor panel"]
    Role -->|patient| Patient["/profile\nPatient area"]
    Google["GoogleAuthController"] -->|new email| NewP["auto-create patient\n(password nullable)"]
    Google -->|known email| Link["link google_id\n(never override role)"]

    style Admin fill:#e6faf5,stroke:#0b8f74
    style Doctor fill:#eff6ff,stroke:#1d4ed8
    style Patient fill:#fef3c7,stroke:#d97706
```

- `Auth::routes(['verify'=>false])` + `LoginController::authenticated()` does the redirect.
- Middleware aliases (`admin`, `doctor`) live in `bootstrap/app.php`; `SecurityHeadersMiddleware` is global.
- Guests can book (`appointments.user_id = NULL`); `GuestRecordLinker` backfills ownership on register / login / Google-link.

---

## 4. The three panels

```mermaid
flowchart TB
    subgraph Public ["PUBLIC — resources/views/frontend/*"]
        Home["/ · /about · /service"]
        DocPub["/doctor · /doctor/{id}"]
        BlogPub["/blog · /blog/{slug} + comments"]
        Book["/appointment\n(booking + slots AJAX + token cancel)"]
        Contact["/contact"]
    end

    subgraph AdminP ["ADMIN /admin/* — Welly skin"]
        Dash["Dashboard\n(stats · donut · calendar · revenue)"]
        Ops["Appointments · Doctors · Patients\nLaboratory · Prescriptions"]
        Bank["Blood Bank\n(dashboard → inventory → requests → issues)"]
        CMS["Content\n(departments · services · blogs · sliders)"]
        Sys["Settings (8 pages + SEO) · Activity Logs · Exports"]
    end

    subgraph DocP ["DOCTOR /doctor/*"]
        DDash["Dashboard (own stats)"]
        DWork["Appointments · Lab Requests\nPrescriptions · Blood Requests"]
        DProf["Profile"]
    end

    Book -->|creates status=0| Ops
    DWork -->|raises| Bank
    Ops -->|moderates| BlogPub
```

**Admin shell files** (the Welly redesign):

- `resources/views/backend/layouts/app.blade.php` — HTML frame, fonts, `@vite(['resources/css/admin.css'])`, notif poller.
- `resources/views/backend/layouts/partial/header.blade.php` — white topbar + sidebar, `data-toggle="sidebar|treeview"` hooks for `public/backend-assets/js/main.js`, self-contained avatar menu.
- `resources/css/admin.css` — Tailwind v4 `@theme` tokens + `mc-*` / `welly-*` components; one skin for all 92 backend/doctor views.
- `resources/views/backend/home.blade.php` + `AdminController@index` — the dashboard.

---

## 5. Core data model (ER)

```mermaid
erDiagram
    users ||--o{ appointments : books
    doctors ||--o{ appointments : receives
    time_slots ||--o{ appointments : scheduled_in
    users ||--o{ lab_orders : owns
    doctors ||--o{ lab_orders : requests
    lab_tests ||--o{ lab_order_items : priced_in
    lab_orders ||--o{ lab_order_items : contains
    lab_orders ||--o{ lab_reports : has
    lab_orders ||--o| invoices : billed_by
    blood_groups ||--o{ blood_donors : typed_as
    blood_donors ||--o{ blood_donations : gives
    blood_donations ||--o{ blood_issues : bag_used
    blood_requests ||--o{ blood_issues : fulfilled_by
    doctors ||--o{ blood_requests : raises
    blogs ||--o{ blog_comments : moderated
    categories ||--o{ blogs : groups
    blogs }o--o{ tags : tagged

    users {
        int id PK
        string role "patient|doctor|admin"
        string google_id "nullable"
    }
    appointments {
        int id PK
        int doctor_id FK
        int user_id FK "nullable = guest"
        int time_slot_id FK
        int status "0 pending · 1 approved · 2 completed · 3 cancelled"
        date appointment_date
        string cancellation_token
        int booking_active "generated: NULL when cancelled"
    }
    blood_requests {
        int id PK
        string status "pending|approved|partially_approved|fulfilled|rejected|cancelled"
    }
```

**Two DB-level guarantees worth knowing**

1. `appointments.booking_active` is a *generated* column + unique index — the database itself rejects double-booking; cancelled rows are excluded so slots can be re-booked.
2. Blood reservation uses `lockForUpdate()` inside a transaction (`BloodBankService`) — oldest-expiry bags first, no over-issue under concurrency.

---

## 6. Appointment lifecycle

```mermaid
stateDiagram-v2
    [*] --> Pending : book (guest or patient)
    Pending --> Approved : admin / doctor approves
    Pending --> Cancelled : patient token-cancel / admin cancel
    Approved --> Completed : visit done
    Approved --> Cancelled : admin cancel
    Completed --> [*]
    Cancelled --> [*]
    note right of Pending : reminder cmd 08:00\n(appointments:send-reminders)
```

---

## 7. Blood request lifecycle

```mermaid
stateDiagram-v2
    [*] --> pending : doctor/admin raises
    pending --> approved : reserve bags (oldest expiry)
    pending --> partially_approved : stock short
    pending --> rejected : reject (reservations released)
    approved --> fulfilled : issues cover volume
    approved --> partially_approved : partial issue
    partially_approved --> fulfilled : remaining issued
    approved --> cancelled : cancel (reservations released)
    partially_approved --> cancelled : cancel
    fulfilled --> [*]
    rejected --> [*]
    cancelled --> [*]
    pending --> deleted : delete pending only
    note right of approved : expire cmd 02:00\n(bloodbank:expire releases overdue bags)
```

---

## 8. Testing & quality gates

```mermaid
flowchart LR
    Code["Code change"] --> Pint["vendor/bin/pint --test\n(style)"]
    Pint --> Unit["php artisan test\n(281 tests, sqlite :memory:)"]
    Unit --> Dusk["composer run dusk\n(5 browser tests, own DB)"]
    Dusk --> Audit["composer audit + php -l\n(security + syntax)"]
    Audit --> Done["Push"]

    style Unit fill:#e6faf5,stroke:#0b8f74
```

- Never change the test DB env to MySQL (`phpunit.xml` forces `sqlite :memory:`).
- Dusk uses `database/dusk.sqlite` + `APP_URL=http://127.0.0.1:8089` and boots its own server — never point it at dev MySQL.

---

*Keep the companion guide's rules when adding features: FormRequest + `validated()`, thin controller → `app/Services/*` with `DB::transaction`, Policy/Gate authorize, `throttle:10,1` on public POSTs, additive nullable reversible migrations. See [ARCHITECTURE.md](ARCHITECTURE.md).*
