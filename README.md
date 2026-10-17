# MediCare Hospital

MediCare is a hospital website and management portal. Visitors can learn about the hospital and request appointments. Patients can manage their care information, while doctors and authorized hospital staff use role-based dashboards to handle their work.

## Open the website

Use the website address supplied by the hospital or demo host. The public home page is the starting point. Choose **Login** to sign in or **Register** to create a patient account.

This repository does not have a hosted public demo. The demo accounts below work only on an installation where the sample database has been seeded; they may not work on a hosted website. Ask the host for the correct address and credentials.

## Demo login accounts

All sample accounts use the password **`123456`**. Visit `/login` on the running website and sign in with one of these email addresses:

| Role | Demo email | Where you go after signing in |
| --- | --- | --- |
| Administrator | `admin@medicare.test` | Admin panel (`/admin`) |
| Receptionist | `receptionist@medicare.test` | Staff/admin area |
| Lab technician | `lab@medicare.test` | Staff/admin area |
| Pharmacist | `pharmacist@medicare.test` | Staff/admin area |
| Doctor | `doctor1@medicare.test` through `doctor6@medicare.test` | Doctor dashboard |
| Patient | `patient1@medicare.test` through `patient8@medicare.test` | Patient profile |

These are public sample credentials for local demonstration only. The standard database seeder creates demo records in `local` and `testing` environments, and blocks them in `production` even if `DEMO_DATA_ENABLED=true`. Other non-production environments can explicitly enable demo seeding with `DEMO_DATA_ENABLED=true`. Never use these accounts on a live website or store real patient information in a demo installation. A hosted demo operator may disable or change these accounts.

## Using MediCare as a visitor

1. Browse the home page to see hospital information and featured content.
2. Open **About**, **Services**, or **Doctors** to learn about the hospital, available care, and doctor specialties.
3. Select a doctor to view their details and appointment availability.
4. Choose **Book Appointment**, select the doctor, date, and an available time, then enter the requested contact details and submit. You can submit an appointment request without registering.
5. Use **Blog** to read articles, or **Contact** to send a message to the hospital.

An appointment request is not necessarily a confirmed appointment. Check your email or contact the hospital for updates. If you booked as a guest, use the cancellation link provided for that booking when available.

## Using MediCare as a patient

### Create an account or sign in

- Select **Register** and provide your name, email, and password to create a patient account.
- Or select **Login** and enter your account email and password.
- If Google sign-in is enabled by the site, you can use it to create or access a patient account.
- If you cannot remember your password, use **Forgot password?** on the login form. A password-reset email can be sent only if email delivery has been configured by the website operator.
- Accounts created through Google start with a random unusable password. To also sign in with email and password, set one from **Profile → New password**, or use **Forgot password?** on the login form. Google-linked accounts can be disconnected from **Profile → Connected accounts** after confirming your password.

### Manage appointments and patient information

After signing in, open **My Profile**. From there you can review your patient information and available care records, such as appointments, lab reports, lab orders, and prescriptions. What appears depends on the services enabled and records associated with your account.

To request an appointment, use **Book Appointment** and submit your preferred doctor, date, and time. Hospital staff or the doctor will process the request. Appointment statuses are **Pending**, **Approved**, **Completed**, or **Cancelled**. You can cancel eligible appointments from your account; guest bookings can be cancelled using their booking link.

## Using the doctor dashboard

Doctors sign in at `/login` with the account provided by the hospital. They are taken to the doctor dashboard, where they can review assigned appointments, update appointment statuses, manage their profile, and view or manage prescriptions. Laboratory, blood bank, and other tools are available only when enabled and permitted for that account.

## Using the staff area and admin panel

### Sign in

Staff and administrators sign in at `/login`. Administrators and authorized staff are taken to the staff area at `/admin`. Access to specific pages and modules depends on the account role and the modules enabled by the hospital.

### Common administrator tasks

From the admin panel, an authorized administrator can use the navigation menu to:

- Review dashboard information and manage appointment requests and statuses.
- Add or update doctors, departments, doctor availability, time slots, and patient records.
- Manage services, home-page sliders, hospital content, blogs, categories, tags, and comments.
- Update general, home-page, about, service, doctor, blog, contact, and appointment settings.
- Manage the homepage section headlines and kickers (services, blog, brands) from the home-page settings; page titles and empty states follow the visitor's language.
- Manage user roles and access, review activity logs, and export appointment or patient information.
- Review invoices and prescriptions.
- Use additional hospital tools—such as laboratory, pharmacy, blood bank, beds, ambulance, or vaccination—when those modules are enabled and the account has access.

### Common staff tasks

Staff accounts have restricted access based on their role. For example, reception staff handle front-desk workflows, laboratory staff handle available lab workflows, and pharmacy staff handle pharmacy workflows. If a menu item is missing, the module may be disabled or the signed-in account may not have permission; contact the system administrator.

## Safety and account access

- Use only the account supplied or approved by the hospital. Do not share credentials.
- Demo accounts and sample patient records are for testing only.
- For a live deployment, the hospital administrator should create individual staff accounts and replace all demo passwords before the system is used with real data.
- Log out after using a shared computer.

## Language selection

The language selector supports Arabic, Chinese, English, French, German, Italian, Portuguese, Russian, Spanish, and Turkish. Existing Bangla support is retained. The selected language is remembered for signed-in users, and Arabic pages set right-to-left document direction.

Localization covers public pages and forms, validation messages, in-app notifications (rendered in the recipient's language), emails (locale-aware subjects and bodies), PDF documents (labels and right-to-left shell; clinical data such as drug names and results is never translated), the appointment cancellation flow, and the staff sidebar/topbar navigation. Staff CRUD screens intentionally stay in English as the operating language.

## Google Calendar for doctors

Google Calendar is an optional module (`MODULE_GOOGLE_CALENDAR=true`). Configure the existing `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` for a Google OAuth application, enable the Google Calendar API, and add the exact `GOOGLE_CALENDAR_REDIRECT_URI` (default: `${APP_URL}/doctor/calendar/callback`) as an authorized redirect URI. Apply migrations with `php artisan migrate` and run a queue worker so appointment changes are synchronized.

Doctors connect their own calendar from the doctor dashboard. Only approved appointments are synchronized, using a private generic event title and the configured timezone; patient names, contact details, and clinical information are not sent. The default event duration is 60 minutes and can be changed with `GOOGLE_CALENDAR_EVENT_DURATION_MINUTES`. Canceled or deleted appointments remove their linked events. Sync failures retry automatically and can be reviewed or retried from the doctor's Calendar page. Doctors can reconnect to refresh authorization. Disconnect removes linked events and local tokens; if Google token revocation cannot be confirmed, the page reports that explicitly.

Before enabling this integration for real patient workflows, review the organization's privacy/security obligations and Google's applicable terms and contractual requirements. The integration's generic event text does not itself establish HIPAA compliance.

## Zoom telehealth

Zoom telehealth is an optional module (`MODULE_ZOOM=true`, disabled by default). Create a Zoom Server-to-Server OAuth app with permission to create, update, and delete account meetings, and configure `ZOOM_ACCOUNT_ID`, `ZOOM_CLIENT_ID`, and `ZOOM_CLIENT_SECRET`. `ZOOM_HOST_USER_ID` defaults to `me`; set it to an account user ID if meetings should be hosted by another hospital user. Apply migrations with `php artisan migrate` and run a queue worker.

Patients choose in-person or online consultation while booking. Online bookings require an email address; after approval, the hospital Zoom account creates a meeting and sends a time-limited signed join link. The doctor starts the meeting from their appointment list. Zoom receives only a generic meeting topic and schedule, never the patient's name, email, or clinical data. Meetings use a waiting room, do not allow joining before the host, and disable automatic recording. Zoom account administrators must separately disable host recording permissions if recording must be prohibited; `auto_recording=none` does not prevent a host from manually starting a recording. Cancellation, completion, or appointment deletion triggers meeting cleanup. Meeting and join URLs are encrypted at rest.

The signed patient URL is a bearer secret: patients should not forward it. Configure Zoom account policy, host permissions, retention, and applicable vendor/privacy agreements before using this module for patient care. This integration does not establish HIPAA compliance.

## SMS notifications

SMS delivery is controlled by the `sms` module (`MODULE_SMS=true`) and the `SMS_DRIVER` setting: `log` (default — records messages without sending), `d7` (D7 Networks), `bulksmsbd` (BulkSMSBD), or `twilio` (Twilio). Set `SMS_DRIVER` plus the matching provider keys (`D7_API_TOKEN`/`D7_ORIGINATOR`, `BULKSMSBD_API_KEY`/`BULKSMSBD_SENDERID`, or `TWILIO_ACCOUNT_SID`/`TWILIO_AUTH_TOKEN`/`TWILIO_FROM`) in `.env`, apply migrations, and run a queue worker — delivery is queued. Every attempt (sent or failed) is recorded in the admin activity feed with the recipient number masked.

Patients opt in from **Profile → SMS notifications** once a phone number is saved. Opted-in patients receive booking confirmations, appointment status changes, and next-day appointment reminders on their phone; guest bookings receive the same messages on the phone number given at booking, and next-day reminders also reach them. An OTP service (`App\Services\Sms\OtpService`) is available for future phone-verification flows: 6-digit codes, 5-minute validity, five attempts, burned on success. With `SMS_DRIVER=log` nothing leaves the server — the safe development default.

## Clinical review advisories

The prescription screens show advisory-only clinical review flags (rules v1.0.0: allergy-overlap against patient-recorded allergies, polypharmacy count, missing dosage details). Each flag carries its rule id, rules version, severity, and references. Flags never block saving and make no dosing or interaction claims — every clinical decision stays with the prescribing clinician. Broader rules require clinician-defined logic plus a rules-version bump reviewed with clinical staff.

## Release readiness

Before release, run `php artisan release:readiness`. It gates on file permissions, environment, module/class resolution, translation key parity across all 11 locales, credentials presence, queue setup, Zoom recording review, failed sync triage, logging, backups, and pending migrations, exiting non-zero on any failure. Live Google/Zoom behavior additionally requires deployment credentials, callback registration, and a supervised queue worker.

## Application structure

For a visual overview of the application structure, see [ARCHITECTURE.md](ARCHITECTURE.md).

Optional modules are controlled by `config/modules.php` and matching `MODULE_*` environment flags. Disable a module before removing its `app/Modules/<Name>` code. Missing module providers are skipped during application boot, and existing migration tables are intentionally preserved. After physically removing a module directory, run `composer dump-autoload` (a stale optimized class map fatals the boot). Core touch points resolve module models only behind `Module::enabled()` guards, so removed modules degrade to 404s instead of 500s. Removal is covered by automated disabled-state and physical-removal tests; run the full test suite and verify core workflows before deleting module code. Do not roll back its migrations unless deleting that data is explicitly intended.

## Troubleshooting and failure logs

Each HTTP response includes an `X-Request-ID` header. When reporting a problem, include this ID and the approximate time so an administrator can find matching log entries. Application logs use Laravel's daily channel by default and are written to `storage/logs/laravel-YYYY-MM-DD.log` with 14 days of retention. Unhandled exceptions include the request ID in their log context; request IDs are also attached to other application log entries while a request is running.

In local development, run `php artisan pail` to follow application logs. On production, keep `APP_DEBUG=false`, ensure the web and queue processes can write to `storage/logs`, and configure `LOG_CHANNEL`, `LOG_STACK`, and `LOG_LEVEL` for the deployment. Do not include patient details, passwords, tokens, or other sensitive data in diagnostic messages.
