<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\AuditLog;
use App\Services\AuditLogAuthorizationService;

class AuditLogPolicy{
    public function __construct(protected AuditLogAuthorizationService $auth){
        //
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Account $user): bool{
        $membership = current_membership();
        if (!$membership) {
            return false;
        }
        return $this->auth->canSeeAll();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Account $user, AuditLog $auditLog): bool
    {
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
    public function update(Account $user, AuditLog $auditLog): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Account $user, AuditLog $auditLog): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Account $user, AuditLog $auditLog): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Account $user, AuditLog $auditLog): bool
    {
        return false;
    }
}
