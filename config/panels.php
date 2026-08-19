<?php

/*
|--------------------------------------------------------------------------
| Filament Panel Soft Configuration
|--------------------------------------------------------------------------
|
| Soft (re-brandable) parameters consumed by AdminPanelProvider and
| ProviderPanelProvider. Hard structural config (resource discovery,
| middleware) lives in the panel provider classes themselves.
|
| Forks rebrand by editing this file; they don't need to edit panel
| provider PHP unless they're adding/removing resources.
|
*/

return [

    'admin' => [
        'path'         => 'admin',
        'brand_name'   => env('APP_NAME', 'Admin'),
        'primary_color'=> 'Blue',
    ],

    'provider' => [
        'path'         => 'provider',
        'brand_name'   => env('APP_NAME', 'Provider'),
        'primary_color'=> 'Emerald',
    ],

];
