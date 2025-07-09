<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ZohoInventoryService;
use App\Services\ZohoTokenService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register ZohoTokenService
        $this->app->singleton(ZohoTokenService::class, function ($app) {
            return new ZohoTokenService();
        });

        // Register ZohoInventoryService
        $this->app->singleton(ZohoInventoryService::class, function ($app) {
            return new ZohoInventoryService($app->make(ZohoTokenService::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
