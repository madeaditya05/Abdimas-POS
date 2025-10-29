<?php

return [
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'server_key'    => env('MIDTRANS_SERVER_KEY'),
    'client_key'    => env('MIDTRANS_CLIENT_KEY'),
    'sanitize'      => env('MIDTRANS_SANITIZE', true),
    'enable_3ds'    => env('MIDTRANS_3DS', true),
];
