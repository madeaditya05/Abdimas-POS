<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Observers\UserObserver;
use App\Models\PenjualanDetail;
use App\Observers\PenjualanDetailObserver;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
            // User::observe(UserObserver::class);
                PenjualanDetail::observe(PenjualanDetailObserver::class);
    }

    
}
