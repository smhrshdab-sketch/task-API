<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Role;
use App\Services\RoleAuthorizationService;

class RolePolicy
{
    public function __construct(protected RoleAuthorizationService $auth){
        //
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Account $user): bool{
        $membership = current_membership();
        logger()->info('you are in rolePolicy(viewAny) and you are: ',[$membership]);
        if (!$membership) {
            return false;
        }
        return $this->auth->canSeeAll();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Account $user, Role $role): bool{
        $membership = current_membership();
        //logger()->info('you are in rolePolicy and you are: ',[$membership]);
        if (!$membership) {
            return false;
        }
        return $this->auth->canSee();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Account $user): bool{
        $membership = current_membership();
        //logger()->info('you are in rolePolicy(create) and you are: ',[$membership->account->name]);
        if (!$membership) {
            return false;
        }
        return $this->auth->canCreate();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Account $user, Role $role): bool
    {
        $membership = current_membership();
        //logger()->info('you are in rolePolicy(update) and you are: ',[$membership->account->name]);
        if (!$membership) {
            return false;
        }
        return $this->auth->canUpdate();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Account $user, Role $role): bool
    {
        $membership = current_membership();
        //logger()->info('you are in rolePolicy(update) and you are: ',[$membership->account->name]);
        if (!$membership) {
            return false;
        }
        return $this->auth->canDelete();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Account $user, Role $role): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Account $user, Role $role): bool
    {
        return false;
    }
}
