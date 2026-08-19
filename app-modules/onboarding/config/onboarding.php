<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Document approval step
    |--------------------------------------------------------------------------
    |
    | When true, the wizard's final step asks the user to sign and accept a
    | document (e.g. terms-of-service, Letter of Authority, consent form)
    | before completion. Disable to skip the step entirely.
    |
    */

    'require_document_approval' => false,

    /*
    |--------------------------------------------------------------------------
    | Post-completion redirect
    |--------------------------------------------------------------------------
    |
    | Where the wizard sends the user after a successful submission. Forks
    | typically point this at their main app dashboard.
    |
    */

    'completion_redirect' => '/dashboard',

];
