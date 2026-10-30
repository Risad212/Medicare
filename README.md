# MediCare Hospital

Hospital website + management portal. Public pages, patient accounts, doctor dashboards, staff/admin panel.

## Quick start (local)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed   # demo data (local/testing only)
npm install && npm run build
php artisan serve
```

Queue worker needed for email/SMS/Zoom/Calendar sync: `php artisan queue:work`.

## Demo accounts

Password for all: `123456` (local only; blocked in production).

| Role | Email |
|---|---|
| Admin | admin@medicare.test |
| Receptionist | receptionist@medicare.test |
| Lab tech | lab@medicare.test |
| Pharmacist | pharmacist@medicare.test |
| Doctor | doctor1..6@medicare.test |
| Patient | patient1..8@medicare.test |

## Using the site

- **Visitors**: browse home/about/services/doctors, book appointments (guest OK), read blog, contact.
- **Patients**: register/login → My Profile (appointments, reports, prescriptions). Cancel eligible bookings.
- **Doctors**: `/login` → dashboard (appointments, statuses, prescriptions, profile).
- **Staff/Admin**: `/login` → `/admin`. Access depends on role + enabled modules.

Appointment statuses: Pending / Approved / Completed / Cancelled.

## Optional modules

Controlled by `config/modules.php` + `MODULE_*` env flags. Enable before removing code; run `composer dump-autoload` after physical removal.

- Google Calendar (`MODULE_GOOGLE_CALENDAR=true`)
- Zoom telehealth (`MODULE_ZOOM=true`)
- SMS (`MODULE_SMS=true`, `SMS_DRIVER=log|d7|bulksmsbd|twilio`)

## Languages

Arabic, Chinese, English, French, German, Italian, Portuguese, Russian, Spanish, Turkish + Bangla. Arabic = RTL. Staff screens stay English.

## Release check

`php artisan release:readiness` (gates on env, modules, translations, migrations, etc.).

## Troubleshooting

Every response has `X-Request-ID` — include it when reporting. Logs: `storage/logs/laravel-YYYY-MM-DD.log` (14-day retention). Dev: `php artisan pail`.

## More

- App structure: [ARCHITECTURE.md](ARCHITECTURE.md)
- Project overview: [docs/project-overview.md](docs/project-overview.md)
