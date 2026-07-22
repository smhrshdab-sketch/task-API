<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Engage;
use App\Services\EngageAuthorizationService;

class EngagePolicy{
    public function __construct(protected EngageAuthorizationService $auth){
        //
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Account $user): bool{
        $membership = current_membership();
        logger()->info('you are in engagePolicy(viewAny) and you are: ',[$membership]);
        if (!$membership) {
            return false;
        }
        return $this->auth->canSeeAll();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Account $user, Engage $engage): bool{
        $membership = current_membership();
        //logger()->info('you are in engagePolicy and you are: ',[$membership]);
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
        logger()->info('you are in engagePolicy(create) and you are: ',[$membership->account->name]);
        if (!$membership) {
            return false;
        }
        return $this->auth->canCreate();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Account $user, Engage $engage): bool
    {
        $membership = current_membership();
        //logger()->info('you are in engagePolicy(update) and you are: ',[$membership->account->name]);
        if (!$membership) {
            return false;
        }
        return $this->auth->canUpdate();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Account $user, Engage $engage): bool
    {
        $membership = current_membership();
        //logger()->info('you are in engagePolicy(update) and you are: ',[$membership->account->name]);
        if (!$membership) {
            return false;
        }
        return $this->auth->canDelete();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Account $user, Engage $engage): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Account $user, Engage $engage): bool
    {
        return false;
    }
}
