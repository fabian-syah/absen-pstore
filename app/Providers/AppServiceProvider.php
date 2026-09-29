<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Otomatis bersihkan stale route & config cache jika ada penambahan rute baru
        $routeCache = base_path('bootstrap/cache/routes-v7.php');
        if (file_exists($routeCache)) {
            @unlink($routeCache);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
  public function boot()
    {
        Paginator::useBootstrapFive();
        // Paksa semua aset menggunakan HTTPS
        if($this->app->environment('production') || $this->app->environment('local')) {
            URL::forceScheme('https');
        }
    }
}
