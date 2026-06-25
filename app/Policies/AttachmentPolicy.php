<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Attachment;
use App\Models\Department;
use App\Models\Membership;
use App\Services\AttachmentAuthorizationService;

class AttachmentPolicy{
    public function __construct(protected AttachmentAuthorizationService $auth){
        //
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Account $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Account $user, Attachment $attachment): bool{
        if ($attachment->is_public) {
            return true;
        }

        return $attachment->uploaded_by === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Membership $membership, Department $department): bool{
        // $membership is automatically passed as first parameter
        // $department is the second parameter from can('create', $task->department)
        
        if (!$membership) {
            logger()->warning('No membership provided');
            return false;
        }
        
        // Check permission using authorization service
        return $this->auth->canAttach($department, $membership);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Account $user, Attachment $attachment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Account $user, Attachment $attachment): bool
    {
        return $attachment->uploaded_by === $user->id || $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Account $user, Attachment $attachment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Account $user, Attachment $attachment): bool
    {
        return false;
    }
}
