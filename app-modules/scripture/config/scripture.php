<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API.Bible API Key
    |--------------------------------------------------------------------------
    | Used to access copyrighted translations (NIV, NKJV, ESV, NLT) via the
    | American Bible Society API.Bible service (PRD §3 — Copyrighted Translations).
    | Public-domain translations (KJV, ASV) work without a key.
    */
    'api_bible_key' => env('API_BIBLE_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Translation
    |--------------------------------------------------------------------------
    */
    'default_translation' => env('DEFAULT_TRANSLATION', 'KJV'),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (seconds)
    |--------------------------------------------------------------------------
    | How long passage API responses are cached in the database before a
    | background refresh is triggered.  0 = never expire.
    */
    'cache_ttl' => env('SCRIPTURE_CACHE_TTL', 0),
];
