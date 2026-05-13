<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Receipt Printer
    |--------------------------------------------------------------------------
    |
    | The Windows share name in the printer properties screenshot is POS-Printer.
    | With escpos-php, passing "POS-Printer" prints to \\COMPUTERNAME\POS-Printer.
    | You may also use an explicit SMB target, for example:
    | smb://COMPUTERNAME/POS-Printer
    |
    */

    'enabled' => env('POS_PRINTER_ENABLED', true),
    'printer_name' => env('POS_PRINTER_NAME', 'POS-Printer'),
    'destination' => env('POS_PRINTER_DESTINATION', env('POS_PRINTER_NAME', 'POS-Printer')),
    'profile' => env('POS_PRINTER_PROFILE', 'simple'),
    'columns' => (int) env('POS_PRINTER_COLUMNS', 32),
    'cut' => env('POS_PRINTER_CUT', false),
    'cash_drawer' => env('POS_PRINTER_CASH_DRAWER', false),
];
