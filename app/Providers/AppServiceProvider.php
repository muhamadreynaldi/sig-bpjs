<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // Tambahkan ini

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
        Paginator::useBootstrapFive(); // Tambahkan baris ini
        // Jika Anda juga ingin menggunakan Bootstrap 4 di beberapa tempat (jarang terjadi)
        // Paginator::useBootstrapFour();
        // Atau secara umum
        // Paginator::defaultView('view-name');
        // Paginator::defaultSimpleView('view-name');
    }
}