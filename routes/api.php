<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Webhook\MidtransWebhookController;
use App\Http\Controllers\CustomerPembayaranController;

Route::post('/webhooks/midtrans', MidtransWebhookController::class)
    ->name('webhooks.midtrans'); // URL: /api/webhooks/midtrans

Route::get('/public/display/{code?}', [CustomerPembayaranController::class, 'dataDisplay']);
