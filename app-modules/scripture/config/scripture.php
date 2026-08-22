<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API.Bible (American Bible Society)
    |--------------------------------------------------------------------------
    | Licensed translations: NIV, ESV, NASB, NLT, NKJV, BSB …
    | Keep server-side only — never expose to the frontend.
    */
    'api_bible' => [
        'key' => env('API_BIBLE_KEY', ''),
        'base_url' => env('API_BIBLE_BASE_URL', 'https://api.scripture.api.bible/v1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Bible Brain (Faith Comes By Hearing)
    |--------------------------------------------------------------------------
    | Audio and video delivery.
    | Keep server-side only.
    */
    'bible_brain_key' => env('BIBLE_BRAIN_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | YouVersion Platform
    |--------------------------------------------------------------------------
    | Verse of the Day + fallback text.
    | Keep server-side only.
    */
    'youversion_app_key' => env('YOUVERSION_APP_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Free Use Bible API
    |--------------------------------------------------------------------------
    | No API key required. No rate limits.
    | Base URL: https://bible-api.com
    | Covers 1,000+ public-domain translations across 1,000+ languages.
    */

    /*
    |--------------------------------------------------------------------------
    | Default Translation
    |--------------------------------------------------------------------------
    */
    'default_translation' => env('DEFAULT_TRANSLATION', 'KJV'),

    /*
    |--------------------------------------------------------------------------
    | Public-Domain Versions
    |--------------------------------------------------------------------------
    | These are routed exclusively to the Free Use Bible API.
    | They must NEVER consume API.Bible quota.
    */
    'public_domain_versions' => ['KJV', 'ASV', 'WEB', 'YLT', 'DARBY', 'BBE', 'WNT'],

    /*
    |--------------------------------------------------------------------------
    | Cache TTLs (seconds)
    |--------------------------------------------------------------------------
    */
    'cache_ttl_text'       => env('BIBLE_CACHE_TTL_TEXT', 2_592_000),  // 30 days
    'cache_ttl_votd'       => env('BIBLE_CACHE_TTL_VOTD', 86_400),     // 24 hours
    'cache_ttl_audio'      => env('BIBLE_CACHE_TTL_AUDIO', 21_600),    // 6 hours
    'cache_ttl_search'     => env('BIBLE_CACHE_TTL_SEARCH', 3_600),    // 1 hour
    'cache_ttl_dictionary' => env('BIBLE_CACHE_TTL_DICTIONARY', 2_592_000), // 30 days
    'cache_ttl_versions'   => env('BIBLE_CACHE_TTL_VERSIONS', 2_592_000),  // 30 days

    /*
    |--------------------------------------------------------------------------
    | Legacy: passage cache TTL used by ScriptureService
    |--------------------------------------------------------------------------
    */
    'cache_ttl' => env('SCRIPTURE_CACHE_TTL', 0),
];
