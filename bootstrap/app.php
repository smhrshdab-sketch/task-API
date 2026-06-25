<?php

use App\Http\Middleware\SetDepartmentContext;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
    )
    ->withMiddleware(function ($middleware) {

        $middleware->alias([
            'auth' => Authenticate::class,
            'department.context' => SetDepartmentContext::class,
        ]);

    })
    ->withExceptions(function ($exceptions) {
        //
    })
    ->create();

