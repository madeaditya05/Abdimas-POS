<?php

return [
    'brand_name'    => 'Nafisa Pasta',
    'brand_tagline' => env('INVOICE_BRAND_TAGLINE', ''),

    'phone'     => env('INVOICE_PHONE', ''),
    'email'     => env('INVOICE_EMAIL', ''),
    'instagram' => env('INVOICE_INSTAGRAM', ''),

    'bank_name'         => env('INVOICE_BANK_NAME', ''),
    'bank_account_name' => env('INVOICE_ACCOUNT_NAME', ''),
    'bank_account_no'   => env('INVOICE_ACCOUNT_NO', ''),

    // Example values: "assets/logo.png" or "storage/logo.png" or full URL.
    'logo_path' => env('INVOICE_LOGO_PATH', null),
];

