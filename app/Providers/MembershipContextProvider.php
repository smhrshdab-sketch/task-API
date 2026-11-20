<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MembershipContext;

class MembershipContextProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind as a singleton - same instance throughout the request
        logger()->info('MembershipContextProvider registered');

        $this->app->singleton(MembershipContext::class, function ($app) {
            logger()->info('MembershipContext singleton created');

            return new MembershipContext();
        });
    }

    public function boot(): void
    {
        //
    }
}