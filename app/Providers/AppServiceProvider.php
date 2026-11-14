<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
        // Usar plantilla personalizada para la paginación
        Paginator::defaultView('vendor.pagination.custom');
        
        // También configurar la vista simple por si acaso
        Paginator::defaultSimpleView('vendor.pagination.custom');
    }
}
