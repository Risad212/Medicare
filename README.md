# MediCare Hospital

MediCare is a hospital website and management portal. Visitors can learn about the hospital and request appointments. Patients can manage their care information, while doctors and authorized hospital staff use role-based dashboards to handle their work.

## Open the website

Use the website address supplied by the hospital or demo host. The public home page is the starting point. Choose **Login** to sign in or **Register** to create a patient account.

This repository does not have a hosted public demo. The demo accounts below work only on an installation where the sample database has been seeded; they may not work on a hosted website. Ask the host for the correct address and credentials.

## Demo login accounts

Seed the demo accounts first (local demo only):

```bash
php artisan migrate --seed
# or, if the tables already exist:
php artisan db:seed --class=DemoDataSeeder
```

### How to log in (same steps for every role)

1. Start the site, e.g. `php artisan serve` → `http://127.0.0.1:8000`.
2. Open `http://127.0.0.1:8000/login`.
3. Enter one of the email + password pairs below and click **Login**.
4. You are redirected automatically based on your role (see "Goes to" column).
5. To switch roles, **Logout** first, then log in again with another account.

All sample accounts use the password **`123456`**.

| Role | Demo email(s) | Password | Goes to after login | What this account can do |
| --- | --- | --- | --- | --- |
| Administrator (admin) | `admin@medicare.test` | `123456` | `/admin` (admin panel) | Full access: dashboard, appointments, doctors, patients, settings, users/roles, invoices, prescriptions, lab/pharmacy/blood-bank modules when enabled |
| Receptionist (front desk) | `receptionist@medicare.test` | `123456` | `/admin` (staff area, restricted) | Front-desk workflows: appointments and patient intake; other menus hidden if no permission |
| Lab technician | `lab@medicare.test` (role: `lab-technician`) | `123456` | `/admin` (staff area, restricted) | Lab workflows: lab tests/orders/reports where enabled |
| Pharmacist | `pharmacist@medicare.test` | `123456` | `/admin` (staff area, restricted) | Pharmacy workflows: medicines/prescriptions where enabled |
| Doctor | `doctor1@medicare.test`, `doctor2@medicare.test`, `doctor3@medicare.test`, `doctor4@medicare.test`, `doctor5@medicare.test`, `doctor6@medicare.test` | `123456` | `/doctor` (doctor dashboard) | Assigned appointments, update appointment status, profile, prescriptions; lab/blood-bank tools only if permitted |
| Patient | `patient1@medicare.test` through `patient8@medicare.test` (e.g. `patient1@medicare.test`) | `123456` | `/profile` (My Profile) | View own appointments, lab reports/orders, prescriptions; book and cancel eligible appointments |

Notes:

- Administrator login example: open `/login`, enter `admin@medicare.test` / `123456` → lands on `/admin`.
- Lab technician login example: open `/login`, enter `lab@medicare.test` / `123456` → lands on `/admin` with lab-only menus.
- Doctor login example: open `/login`, enter `doctor1@medicare.test` / `123456` → lands on `/doctor`.
- Patient login example: open `/login`, enter `patient1@medicare.test` / `123456` → lands on `/profile`. Patients can also self-register at `/register` or use Google sign-in (creates a `patient` account) instead of using a demo account.
- Staff accounts (`receptionist`, `lab-technician`, `pharmacist`) share the `/admin` area with the admin but see only permitted modules. If a menu is missing, the module is disabled or the role has no permission.
- These are public sample credentials for local demonstration only. Never use them on a live website or store real patient information in a demo installation. A hosted demo operator may disable or change these accounts. For live use, the admin should create individual accounts and replace all demo passwords.

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

## Application structure

For a visual overview of the application structure, see [ARCHITECTURE.md](ARCHITECTURE.md).
