<?php

// config for Adequaat/LaravelPostnlApi
return [

    'api'   => [
        'key'   => env('POSTNL_API_KEY'),
        'url'   => env('POSTNL_API_URL'),
    ],

    'customer'  => [
        'collection_location'   => env('POSTNL_CUSTOMER_COLLECTION_LOCATION'),
        'code'  => env('POSTNL_CUSTOMER_CODE'),
        'number'    => env('POSTNL_CUSTOMER_NUMBER'),
        'address'   => [
            'address_type'  => env('POSTNL_CUSTOMER_ADDRESS_TYPE'),
            'city'  => env('POSTNL_CUSTOMER_CITY'),
            'company_name'  => env('POSTNL_CUSTOMER_COMPANY_NAME'),
            'country_code'  => env('POSTNL_CUSTOMER_COUNTRY_CODE'),
            'house_number'  => env('POSTNL_CUSTOMER_HOUSE_NUMBER'),
            'postal_code'   => env('POSTNL_CUSTOMER_POSTAL_CODE'),
            'street'    => env('POSTNL_CUSTOMER_STREET'),
        ],
        'email' => env('POSTNL_CUSTOMER_EMAIL'),
    ]

];
