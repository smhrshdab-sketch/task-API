<?php

namespace App\Policies;

use App\Models\Account;
use App\Services\AccountAuthorizationService; 

class AccountPolicy{
    public function __construct(protected AccountAuthorizationService $auth)
    {
        //
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Account $user): bool
    {
        logger("(Account)viewAny AccountPolicy");
        return $this->auth->canSeeAll();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Account $user,Account $account): bool{//اکانت اولی مال لاراوله نمی تونی ازش به عنوان پرامتر استفاده کنی!!
        $crnt_acnt = current_membership()->account;
        if($crnt_acnt->id == $account->id){
            return true;
        }
        return false;
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
    public function update(Account $user): bool{
        $crnt_acnt = current_membership()->account;
        if($crnt_acnt->id == $user->id){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Account $user, Account $account): bool
    {
        return $this->auth->canDelete($account);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Account $user, Account $account): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Account $user, Account $account): bool
    {
        return false;
    }
}
