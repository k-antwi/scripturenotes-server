<?php

return [

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'address_lookup' => [
        'default' => env('ADDRESS_LOOKUP_DEFAULT_PROVIDER', 'getaddress'),

        'getaddress' => [
            'api_key' => env('GETADDRESS_API_KEY'),
        ],

        'idealpostcodes' => [
            'api_key' => env('IDEALPOSTCODES_API_KEY'),
        ],
    ],

    'companies_house' => [
        'api_key'  => env('COMPANIES_HOUSE_API_KEY'),
        'base_url' => env('COMPANIES_HOUSE_BASE_URL', 'https://api.company-information.service.gov.uk'),
    ],

];
