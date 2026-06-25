<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Task;
use App\Services\TaskAuthorizationService;

class TaskPolicy
{
    public function __construct(protected TaskAuthorizationService $auth) {
        //
    }
    //-------------------
    // public function before(Account $user){
    //     if ($user->hastask ('super-admin')) {
    //         return true;
    //     }
    // }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Account $user): bool{
        $membership = current_membership();
        logger()->info('you are in task Policy(viewAny) and you are: ',[$membership]);
        if (!$membership) {
            return false;
        }
        return $this->auth->canSeeAll();
    }
    public function viewOnes(Account $user):bool{
        $membership = current_membership();        
        if (!$membership) {
            return false;
        }
        $result = $this->auth->canSeeOnes();
        logger()->info('you are in task Policy(viewOnes) and you are: ',[$membership->account->name,$result]);
        return $result;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Account $user, Task $task): bool{
        $membership = current_membership();
        logger()->info('you are in task Policy(VIEW) and you are: ',[$membership]);
        if (!$membership) {
            return false;
        }
        return $this->auth->canSee();
    }
    public function attachments(Account $user, Task $task): bool{
        $membership = current_membership();
        //logger()->info('you are in task Policy and you are: ',[$membership]);
        if (!$membership) {
            return false;
        }
        return $this->auth->canSee();
    }

    /**
     * Determine whether the user can create models.
     */
     public function create(Account $user,Task $task): bool{
        $membership = current_membership();
        //logger()->info('you are in task Policy(create) and you are: ',[$membership->account->name]);
        if (!$membership) {
            return false;
        }
        return $this->auth->canCreate();
    } 

    public function update(Account $user, Task $task ): bool{
        $membership = current_membership();
        //logger()->info('you are in task Policy(update) and you are: ',[$membership->account->name]);
        if (!$membership) {
            return false;
        }
        return $this->auth->canUpdate();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Account $user, task $task ): bool{
        $membership = current_membership();
        logger()->info('you are in taskPolicy(delete) and you are: ',[$membership->account->name]);
        if (!$membership) {
            return false;
        }
        return $this->auth->canDelete();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Account $user, Task $task): bool{
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Account $user, Task $task): bool{
        return false;
    }
}
