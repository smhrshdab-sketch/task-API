<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Attachment;
use App\Models\Department;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Task;
use App\Observers\AccountObserver;
use App\Observers\AttachmentObserver;
use App\Observers\DepartmentObserver;
use App\Observers\MembershipObserver;
use App\Observers\OrganizationObserver;
use App\Observers\RoleObserver;
use App\Observers\TaskObserver;
use App\Services\AttachmentAuthorizationService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void{
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));

        Route::middleware('web')
            ->group(base_path('routes/web.php'));

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void{
        Membership::observe(MembershipObserver::class);
        Role::observe(RoleObserver::class);
        Task::observe(TaskObserver::class);
        Account::observe(AccountObserver::class);
        Department::observe(DepartmentObserver::class);
        Organization::observe(OrganizationObserver::class);
        Attachment::observe(AttachmentObserver::class);
        //-------
        Gate::define('create-attachment', function ($account, Department $department) {
            // Get current membership from context
            $membership = current_membership();
            
            if (!$membership) {
                return false;
            }
            
            $auth = app(AttachmentAuthorizationService::class);
            return $auth->canAttach($department);
        });
    }    
}
