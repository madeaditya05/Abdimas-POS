<?php

return [
    App\Providers\AppServiceProvider::class,
    BladeUI\Icons\BladeIconsServiceProvider::class,
    BladeUI\Heroicons\BladeHeroiconsServiceProvider::class,
    // Filament panel sudah tidak dipakai karena aplikasi memakai full MVC.
    // App\Providers\Filament\AdminPanelProvider::class,
];
