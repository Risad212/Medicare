# Admin Panel React Migration Plan

## Goal

Convert the existing MediCare admin and staff panel from Blade-rendered pages to a React user interface styled with Tailwind CSS. Keep Laravel as the backend for authentication, authorization, validation, business rules, database access, and existing hospital workflows.

This plan covers the `/admin` area and staff modules shown within it. The public website, patient profile, and doctor dashboard are outside this migration unless separately approved.

## Current baseline

- The application uses Laravel 13 and Vite.
- Tailwind CSS v4 and its Vite plugin are already installed and used by the admin styles.
- React, React DOM, Inertia React, and the Laravel Inertia adapter are now installed for the migration.
- The admin dashboard and appointment register have been moved to React; remaining admin/staff pages are still Blade templates backed by Laravel controllers and routes.
- Admin routes use the `auth`, `admin`, and `staff.modules` middleware. Staff module permissions and enabled-module checks must continue to apply after the frontend migration.

## Implementation progress

- **Completed:** Added the Laravel Inertia and React/Vite foundation inside the existing monolith.
- **Completed:** Converted the role-aware admin/staff dashboard and appointment register page to React, preserving Laravel routes, search, pagination, appointment actions, module-aware widgets, and role-specific data.
- **Completed:** Converted appointment create/edit forms to React/Inertia, preserving the existing Laravel submission, validation, booking-conflict, notification, and status workflows.
- **In progress:** Patient and doctor/department pages, remaining admin CRUD forms, content/settings pages, and optional module screens are still Blade-rendered and should be migrated incrementally.
- **Next:** Convert patients and doctors/departments, then continue through the remaining screens in the migration phases below.

## Recommended architecture

Use Laravel with Inertia.js and React for the admin panel, served from the existing application and built through Vite. This keeps the frontend and backend on the same origin and allows the application to retain Laravel session authentication, CSRF protection, redirects, and authorization middleware without introducing a separate frontend server or duplicating authentication.

Laravel controllers remain responsible for loading and validating data and enforcing access. Controllers render React page components with the data they need. React handles page layout, interaction, navigation, and form state. Use the existing Tailwind CSS v4 setup and migrate admin-specific styling into a consistent React-friendly design system. Keep Blade for the public website and for the minimal HTML shell required by the React bridge.

Do not expose sensitive admin operations through unauthenticated endpoints or move permission checks into the client. Hiding a link or button in React is only a usability measure; Laravel must authorize every protected request.

## Scope and functional coverage

Inventory the current admin navigation and enabled staff modules before implementation. Preserve the existing role-based access and workflows for the following areas where currently available:

| Area | Expected coverage |
| --- | --- |
| Dashboard | Summary cards, operational counts, and existing dashboard information |
| Appointments | Browse, create or edit where supported, and update appointment status |
| Patients | Browse and manage patient records according to role permissions |
| Doctors and departments | Manage doctors, specialties, availability, off-days, and departments |
| Time slots | Manage appointment time slots |
| Website content | Manage services, sliders, home/about/contact settings, blogs, categories, tags, and comments |
| Users and audit | Manage permitted user roles/access, view activity logs, and use existing exports |
| Billing and prescriptions | Browse invoices, update supported statuses, view/download PDFs, and manage prescriptions where supported |
| Optional modules | Preserve each enabled and authorized module, such as laboratory, pharmacy, blood bank, beds, ambulance, vaccination, languages, and backups |
| Shared interface | Responsive navigation, notifications, search/filtering, validation messages, confirmations, and accessible loading/error states |

The inventory must record which roles can access each page and action, which modules are optional, and the relevant existing routes, controllers, requests, policies, and tests. The table is a coverage checklist, not permission to add new behavior or widen access.

## Phased implementation

### Phase 1: Discovery and baseline

1. Record the current admin/staff routes, navigation, Blade pages, controller responses, validations, and role/module permissions.
2. Map each current screen to its backend behavior and note upload, export, PDF, notification, and confirmation flows.
3. Identify current UI patterns and establish screenshots or browser checks for critical workflows.
4. Add or update feature tests for access boundaries and essential backend behavior before changing page rendering.
5. Agree on the migration acceptance criteria and identify a first low-risk page for the pilot.

**Deliverable:** a route/page/role/module inventory and a verified baseline for key workflows.

### Phase 2: Frontend foundation

1. Add React, React DOM, the Laravel Inertia adapter, and the Inertia React client using versions compatible with the project's Laravel, PHP, and Vite versions.
2. Configure the Inertia middleware, root view, Vite entry point, and React page resolver.
3. Keep the existing Tailwind CSS v4 and Vite plugin; avoid introducing a second Tailwind version or a separate CSS build.
4. Create a shared admin shell: header, sidebar, responsive navigation, page title/breadcrumb area, notification indicator, and content container.
5. Establish reusable React components and conventions for tables, filters, pagination, forms, field errors, dialogs, alerts, empty states, and loading states.
6. Support keyboard navigation, visible focus, semantic labels, responsive layouts, and readable contrast.

**Deliverable:** a working React admin shell with a pilot page, while unconverted pages continue to work.

### Phase 3: Pilot conversion

1. Convert the selected low-risk page end-to-end, including its list/detail or edit behavior as applicable.
2. Keep data loading, validation, authorization, and persistence in Laravel.
3. Preserve existing URLs where practical so bookmarks and links continue to work.
4. Verify success, validation failure, unauthorized access, empty data, and server-error behavior.
5. Check the page at desktop and mobile widths and compare its workflow with the baseline.

**Deliverable:** one production-quality React page demonstrating the agreed pattern.

### Phase 4: Shared workflows and page migration

1. Migrate the dashboard and common components first, then convert admin pages in small, testable groups.
2. Prioritize core operational pages: appointments, patients, doctors/departments, and time slots.
3. Migrate website content, settings, users/audit, exports, invoices, and prescriptions.
4. Migrate optional module pages only when those modules are enabled; preserve their existing permission and navigation rules.
5. Replace Blade-specific interactions with React interactions without changing business rules or data semantics.
6. Keep a page-level migration checklist and mark a page complete only after its behavior, permissions, and tests are verified.

**Deliverable:** all agreed admin/staff screens and enabled workflows rendered through React.

### Phase 5: Verification and rollout

1. Run targeted Laravel feature tests for each migrated route, role, permission, validation, and module boundary.
2. Run browser-based end-to-end tests for sign-in redirects, navigation, CRUD workflows, appointment status changes, uploads, notifications, and relevant downloads/exports.
3. Check security-sensitive actions, CSRF/session behavior, request validation, and that hidden UI controls remain protected server-side.
4. Test responsive behavior, keyboard operation, accessible errors, browser console output, and production Vite builds.
5. Review performance for large tables and use server-side pagination/filtering where the existing backend supports it.
6. Roll out incrementally. Keep unconverted pages available during migration; remove obsolete Blade admin views and scripts only after their React replacements pass acceptance.
7. Update user/developer documentation and support notes to describe the completed UI and any changed navigation.

**Deliverable:** a tested production build with the agreed admin panel running on React and Tailwind CSS.

## Acceptance criteria

- All agreed admin/staff pages are rendered by React and styled with the existing Tailwind CSS v4 setup.
- Existing URLs and Laravel session-based sign-in continue to work, including role-based redirects.
- Every existing role and module permission remains enforced by Laravel on every protected request.
- Existing validated workflows, data, status meanings, uploads, PDFs, exports, notifications, and optional-module behavior are preserved.
- No migrated workflow relies on client-only authorization or bypasses existing server-side validation.
- Critical workflows pass automated feature and browser tests; the production frontend build succeeds.
- The interface works at supported screen sizes and remains usable by keyboard.
- Public pages and patient/doctor areas remain unchanged unless separately scoped.

## Risks and safeguards

| Risk | Safeguard |
| --- | --- |
| Existing Blade pages contain workflow details that are easy to miss | Complete the page/route/action inventory and baseline checks before converting each area |
| React UI accidentally weakens role or module access | Keep authorization in Laravel middleware, policies, gates, and controller/service checks; add negative access tests |
| Broad conversion causes regressions | Migrate incrementally by page, retain unconverted Blade screens during transition, and test each page before removal |
| Optional modules differ by installation | Resolve navigation and routes from enabled modules and test both enabled and disabled conditions |
| Large tables or dashboards become slow | Continue server-side pagination and filtering; avoid loading unrestricted datasets into the browser |
| Styling conflicts with existing admin assets | Reuse the installed Tailwind/Vite pipeline, define clear admin style ownership, and remove legacy styles only after migration |
| Inertia/API response changes break forms or downloads | Preserve existing Laravel download/redirect behavior and test file uploads, PDFs, exports, and validation redirects explicitly |

## Out of scope

- Replacing Laravel, its database, or hospital business services.
- Rebuilding the public-facing website.
- Converting patient or doctor dashboards as part of this admin migration.
- Changing roles, permissions, appointment rules, or optional-module behavior.
- Replacing the existing authentication system with a separate SPA authentication service.
