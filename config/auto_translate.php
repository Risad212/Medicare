<?php

return [

    /*
    |----------------------------------------------------------------------
    | Auto-translation of frontend HTML responses
    |----------------------------------------------------------------------
    |
    | When the active locale differs from the fallback locale, the
    | TranslateResponse middleware translates visible text nodes of
    | frontend HTML pages on the fly and caches each unique string
    | forever (per locale), so the provider is hit only once per string.
    |
    | Drivers: 'mymemory' (keyless, free tier, default), 'google'
    | (Cloud Translation v2, needs GOOGLE_TRANSLATE_KEY), 'null' (off).
    |
    */

    'enabled' => env('AUTO_TRANSLATE_ENABLED', true),

    'driver' => env('AUTO_TRANSLATE_DRIVER', 'mymemory'),

    'key' => env('GOOGLE_TRANSLATE_KEY', ''),

    /*
    | Source locale of the site's authored content. Strings are only
    | translated when the active locale differs from this value.
    */
    'source' => env('AUTO_TRANSLATE_SOURCE', 'en'),

    /*
    | URL path prefixes that are never translated (staff areas stay in
    | the authored language).
    */
    'skip_prefixes' => ['admin', 'doctor'],

    /*
    | Cache TTL in seconds for translated strings. Null = forever.
    */
    'cache_ttl' => env('AUTO_TRANSLATE_CACHE_TTL', null),
];
