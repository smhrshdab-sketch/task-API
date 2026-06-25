<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\DepartmentContext;

class DepartmentContextProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind as a singleton - same instance throughout the request
        $this->app->singleton(DepartmentContext::class, function ($app) {
            return new DepartmentContext();
        });
    }

    public function boot(): void
    {
        //
    }
}