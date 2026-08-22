<?php

/**
 * Laravel CORS configuration (PRD §3 Authentication).
 *
 * During browser dev the Vite proxy makes requests same-origin so this
 * config is not exercised.  It matters for:
 *  - Capacitor native builds pointing directly at the API host
 *  - Any other client that talks to the API cross-origin with credentials
 *
 * Add any additional allowed origins to the array below.
 */
return [

    'paths' => ['api/*', 'auth/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    | Explicit origins required when credentials (cookies / Bearer tokens) are
    | sent — browsers reject wildcard '*' with withCredentials:true.
    |
    | 'null' covers Capacitor native WebViews (file:// / capacitor:// origins).
    */
    'allowed_origins' => [
        'http://localhost:5173',           // Vite dev server
        'http://localhost:58128',          // Laravel dev server (same-origin requests)
        'capacitor://localhost',           // Capacitor iOS
        'http://localhost',                // Capacitor Android
        'null',                            // file:// origin (older Capacitor / Electron)
        'https://sn-app.churchpanel.org',  // Production frontend
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    | 7 200 seconds = 2 hours. Browsers cache the pre-flight result for this
    | duration, reducing OPTIONS round-trips on mobile networks.
    */
    'max_age' => 7200,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    | Must be true for Sanctum session cookies and Bearer token flows.
    | Must NOT be used alongside a wildcard allowed_origin.
    */
    'supports_credentials' => true,

];
