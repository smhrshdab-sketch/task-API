<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Membership;
use App\Services\MembershipAuthorizationService; 

class MembershipPolicy
{
    public function __construct(protected MembershipAuthorizationService $auth)
    {
        //
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Account $user): bool
    {
        logger("viewAny MembershipPolicy");
        return $this->auth->canSeeAll();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Account $user,Membership $membership): bool
    {
        $crnt_acnt = current_membership();
        if (!$crnt_acnt) {
            return false;
        }
        return $this->auth->canSee();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Account $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Account $user, Membership $membership): bool{
        //return ;
        return $this->auth->canUpdate();
    }
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Account $user, Membership $membership): bool{
        return $this->auth->canDelete();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Account $user, Membership $membership): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Account $user, Membership $membership): bool
    {
        return false;
    }
}
