<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Verifier Binding
    |--------------------------------------------------------------------------
    |
    | The fully-qualified class name of the CredentialVerifier implementation
    | the VerifyProviderCredential job will resolve. Forks should bind their
    | own verifier (e.g. FCA, NPI, Bar) here.
    |
    | The default NullCredentialVerifier returns 'manual_review' so the
    | boilerplate boots without external API credentials.
    |
    */

    'verifier' => \Nucleus\Providers\Verifiers\NullCredentialVerifier::class,

    /*
    |--------------------------------------------------------------------------
    | Auto-Verification
    |--------------------------------------------------------------------------
    |
    | When true, creating a ProviderProfile dispatches VerifyProviderCredential
    | automatically. Disable to control verification timing manually.
    |
    */

    'auto_verify_on_create' => true,

];
