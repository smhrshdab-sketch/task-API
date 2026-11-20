<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MembershipContext;

class MembershipContextProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind as a singleton - same instance throughout the request
        $this->app->singleton(MembershipContext::class, function ($app) {
            return new MembershipContext();
        });
    }

    public function boot(): void
    {
        //
    }
}