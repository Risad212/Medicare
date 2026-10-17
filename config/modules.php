<?php

/*
|--------------------------------------------------------------------------
| Feature Modules
|--------------------------------------------------------------------------
|
| The app is a modular monolith: each feature lives in its own
| app/Modules/<Name>/ folder with its own routes, controllers, models,
| requests, services and views. A module is active only when its flag
| below is true.
|
| To detach a module (e.g. you do not need Pharmacy):
|   1. Set its flag to false here (or via env, e.g. MODULE_PHARMACY=false).
|      Routes, sidebar links and permission grants disappear; core pages
|      degrade gracefully (dispense UI hides, history stays readable).
|   2. Optionally remove app/Modules/<Name>/, its views in
|      resources/views/<name>/, and its tests. Registered modules with
|      missing service providers are treated as unavailable, and bootstrap
|      skips their providers. Keep the module flag off.
|      Then run `composer dump-autoload`: optimized installs keep a class
|      map of module classes, and a stale map fatals the boot when a
|      removed file is first referenced. Re-dumping is part of removal.
|   3. Module migrations already run stay in the database (data kept).
|      Roll them back first only if you want the tables gone too.
|   4. Run the test suite after removal. Preserve module data and verify
|      guarded integrations with core workflows before deleting module code.
|
| Not every flag below owns an app/Modules/<Name>/ folder. Three shapes exist:
|
|   a. Full modules  — own folder with routes, controllers, models, views and
|      (usually) a ServiceProvider. ambulance, backups, beds, billing,
|      bloodbank, directory, google_calendar, lab, language, pharmacy,
|      prescriptions, publishing, vaccination, zoom.
|
|   b. Service-only modules — own folder, but only a Services/ class; no
|      routes and no provider. Callers resolve them with app(<Service>::class)
|      behind the flag and fall back to core behaviour when it is off, so they
|      are still fully detachable. search, analytics.
|
|   c. Core feature flags — no folder at all; they gate a field or behaviour
|      that lives in core. allergy gates the users.allergies column (profile
|      input, prescription history, validation). lockout gates the extra
|      login throttle. sms gates app/Services/Sms/* and the users.sms_enabled
|      column. Do not create an empty app/Modules/ directory for
|      these; deleting the folder would change nothing.
|
*/

return [
    'pharmacy' => env('MODULE_PHARMACY', true),
    'beds' => env('MODULE_BEDS', true),
    'ambulance' => env('MODULE_AMBULANCE', true),
    'vaccination' => env('MODULE_VACCINATION', true),

    // Core feature flag: gates the users.allergies column + its UI/validation.
    'allergy' => env('MODULE_ALLERGY', true),
    'bloodbank' => env('MODULE_BLOODBANK', true),
    'lab' => env('MODULE_LAB', true),
    'backups' => env('MODULE_BACKUPS', true),

    // Service-only module: app/Modules/Analytics/Services/AnalyticsService.php.
    'analytics' => env('MODULE_ANALYTICS', true),
    'language' => env('MODULE_LANGUAGE', true),
    'google_calendar' => env('MODULE_GOOGLE_CALENDAR', true),
    'zoom' => env('MODULE_ZOOM', false),

    // Service-only module: app/Modules/Search/Services/DoctorSearchService.php.
    'search' => env('MODULE_SEARCH', true),

    // Core feature flag: no module folder; gates extra login throttling.
    'lockout' => env('MODULE_LOCKOUT', true),

    // Core feature flag: gates app/Services/Sms/* + users.sms_enabled.
    'sms' => env('MODULE_SMS', true),

    'publishing' => env('MODULE_PUBLISHING', true),
    'directory' => env('MODULE_DIRECTORY', true),
    'billing' => env('MODULE_BILLING', true),
    'prescriptions' => env('MODULE_PRESCRIPTIONS', true),
];
