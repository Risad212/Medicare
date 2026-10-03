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
|   2. Optionally delete app/Modules/<Name>/, its views in
|      resources/views/<name>/, and its tests. Core code never imports
|      module classes at runtime when the flag is off, so nothing breaks.
|   3. Module migrations already run stay in the database (data kept).
|      Roll them back first only if you want the tables gone too.
|
*/

return [
    'pharmacy' => env('MODULE_PHARMACY', true),
    'beds' => env('MODULE_BEDS', true),
    'ambulance' => env('MODULE_AMBULANCE', true),
    'vaccination' => env('MODULE_VACCINATION', true),
    'allergy' => env('MODULE_ALLERGY', true),
    'bloodbank' => env('MODULE_BLOODBANK', true),
    'lab' => env('MODULE_LAB', true),
    'backups' => env('MODULE_BACKUPS', true),
    'analytics' => env('MODULE_ANALYTICS', true),
    'language' => env('MODULE_LANGUAGE', true),
    'search' => env('MODULE_SEARCH', true),
    'lockout' => env('MODULE_LOCKOUT', true),
];
