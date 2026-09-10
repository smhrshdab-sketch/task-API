<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Department;
use App\Services\DepartmentAuthorizationService;

class DepartmentPolicy
{
    public function __construct(protected DepartmentAuthorizationService $auth) {
        //
    }
    /*
        پارامتر اول Account $account در واقع نماینده‌ی کاربر احراز هویت‌شده است.

        Laravel وقتی می‌خواهد Policy را اجرا کند، به‌طور معمول Account/Model احراز هویت‌شده را به عنوان اولین آرگومان متد Policy می‌دهد.
     */
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Account $user): bool{
        return $this->auth->canViewAny();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Account $account, Department $department): bool{
        return $this->auth->canView($department);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Account $user): bool{
        $membership = current_membership();
        //logger()->info('you are in department Policy(create) and you are: ',[$membership->account->name]);
        if (!$membership) {
            return false;
        }
        $result =  $this->auth->canCreate();
        if($result){
            logger()->info('you are in department Policy(create) and you can create department: ');
        }
        else{
            logger()->info('you are in department Policy(create) and you can NOT create department: ');
        }
        return $result;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Account $user): bool{
         $membership = current_membership();
        //logger()->info('you are in department Policy(update) and you are: ',[$membership->account->name]);
        if (!$membership) {
            return false;
        }
        return $this->auth->canUpdate();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Account $user): bool{
        $membership = current_membership();
        //logger()->info('you are in department Policy(update) and you are: ',[$membership->account->name]);
        if (!$membership) {
            return false;
        }
        return $this->auth->canDelete();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Account $user, Department $department): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Account $user, Department $department): bool
    {
        return false;
    }
}
