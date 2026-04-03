<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Forzar configuración de sesión independientemente del caché de config
        config([
            'session.lifetime'        => 480,  // 8 horas
            'session.expire_on_close' => false,
        ]);
    }
}
