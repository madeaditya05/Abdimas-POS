<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Midtrans\Config as MidtransConfig;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\MidtransPaymentGateway;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\Payments\PaymentGateway::class, function () {
        return new \App\Services\Payments\PaymentGateway();
    });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    MidtransConfig::$serverKey = config('midtrans.server_key');
    MidtransConfig::$isProduction = (bool) config('midtrans.is_production');
    MidtransConfig::$isSanitized  = (bool) config('midtrans.sanitize');
    MidtransConfig::$is3ds        = (bool) config('midtrans.enable_3ds');
    }
}
