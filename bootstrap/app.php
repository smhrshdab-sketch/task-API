<?php

use App\Exceptions\MembershipContextMissingException;
use App\Exceptions\ModelCannotHaveAttachmentsException;
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
        // مدیریت برای وقتی که مدل نمی‌تواند فایل داشته باشد
        $exceptions->render(function (ModelCannotHaveAttachmentsException $e, $request) {
            return response()->json([
                'error' => 'Unauthorize action',
                'message' => $e->getMessage(),
            ], 403);
        });

        // مدیریت برای خطای Membership
        $exceptions->render(function (MembershipContextMissingException $e, $request) {
            return response()->json([
                'error' => ' Access Eror',
                'message' => 'Please select department',
            ], 401);
        });
    })
    ->create();

