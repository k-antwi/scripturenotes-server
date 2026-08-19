<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default KYC Provider
    |--------------------------------------------------------------------------
    |
    | The provider used when initiating new KYC verification sessions.
    | Supported: "manual", "stripe", "onfido", "jumio", "veriff"
    |
    */

    'default_provider' => env('KYC_PROVIDER', 'manual'),

    /*
    |--------------------------------------------------------------------------
    | Admin Notification Email
    |--------------------------------------------------------------------------
    |
    | Email address that receives a notification whenever a user submits their
    | KYC documents for manual review.
    |
    */

    'admin_notification_email' => env('KYC_ADMIN_EMAIL', 'compliance@example.com'),

    /*
    |--------------------------------------------------------------------------
    | Reviewer Middleware
    |--------------------------------------------------------------------------
    |
    | The middleware alias that guards the private file-serving route and the
    | admin reviewer workspace.
    |
    */

    'reviewer_middleware' => 'kyc.reviewer',

    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    |
    | The filesystem disk used to store KYC documents and liveness videos.
    | Defaults to 'kyc' (a private local disk). Set KYC_STORAGE_DISK=s3 to
    | store files on S3 instead.
    |
    */

    'storage_disk' => env('KYC_STORAGE_DISK', 'kyc'),

    /*
    |--------------------------------------------------------------------------
    | Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Credentials and options for each supported KYC provider.
    |
    */

    'providers' => [

        'manual' => [
            // No external credentials required. Uses the in-app document
            // upload + liveness check flow with admin review.
        ],

        'stripe' => [
            // Stripe Identity – https://stripe.com/docs/identity
            'secret_key'     => env('STRIPE_KYC_SECRET_KEY', ''),
            'webhook_secret' => env('STRIPE_KYC_WEBHOOK_SECRET', ''),
            'return_url'     => env('STRIPE_KYC_RETURN_URL', null),
        ],

        'onfido' => [
            // Onfido – https://documentation.onfido.com/
            'api_token'     => env('ONFIDO_API_TOKEN', ''),
            'webhook_token' => env('ONFIDO_WEBHOOK_TOKEN', ''),
            'region'        => env('ONFIDO_REGION', 'eu'),   // eu | us | ca
            'workflow_id'   => env('ONFIDO_WORKFLOW_ID', null),
        ],

        'jumio' => [
            // Jumio – https://developers.jumio.com/
            'api_token'   => env('JUMIO_API_TOKEN', ''),
            'api_secret'  => env('JUMIO_API_SECRET', ''),
            'base_url'    => env('JUMIO_BASE_URL', 'https://account.amer-1.jumio.ai'),
            'success_url' => env('JUMIO_SUCCESS_URL', null),
            'error_url'   => env('JUMIO_ERROR_URL', null),
        ],

        'veriff' => [
            // Veriff – https://developers.veriff.com/
            'api_key'    => env('VERIFF_API_KEY', ''),
            'api_secret' => env('VERIFF_API_SECRET', ''),
            'base_url'   => env('VERIFF_BASE_URL', 'https://stationapi.veriff.com'),
        ],

    ],

];
