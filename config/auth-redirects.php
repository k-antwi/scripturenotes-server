<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Post-Login Default Redirect
    |--------------------------------------------------------------------------
    |
    | Where to send authenticated users that don't match any role below.
    |
    */

    'default' => '/onboarding',

    /*
    |--------------------------------------------------------------------------
    | Role-Based Redirects
    |--------------------------------------------------------------------------
    |
    | Checked in declaration order — the first matching role wins.
    | A user with multiple roles is always sent to the first match here,
    | making the redirect deterministic regardless of Spatie's internal order.
    |
    | Add or remove roles to match the roles you seed in RoleSeeder.
    |
    */

    'roles' => [
        'admin'    => '/admin',
        'provider' => '/provider',
    ],

];
