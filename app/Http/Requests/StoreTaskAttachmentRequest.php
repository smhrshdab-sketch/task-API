<?php

namespace App\Http\Requests;

use App\Contracts\Attachable;
use App\Services\AttachmentAuthorizationService;

class StoreTaskAttachmentRequest extends BaseAttachmentRequest
{
    protected function getAttachableModel(): ?Attachable{
        // Get the task from the route and return it
        $task = $this->route('task');
        logger('route tassk [getAttachableModel]: ',[$task]);
        // Make sure it implements Attachable
        if ($task instanceof Attachable) {
            return $task;
        }
        
        return null;
    }
    
    protected function authorizeAttachment(Attachable $attachable): bool
    {
        $task = $attachable;
        $membership = current_membership();
        logger('route tassk [authorizeAttachment]: (task,membership) ',[$task,$membership]);
        if (!$membership) {
            return false;
        }
        // Check if user is in the same department as the task
        $auth = app(AttachmentAuthorizationService::class);
        logger('route tassk [authorizeAttachment]: (task,membership,auth) ',[$task,$membership,$auth]);
        return $auth->canAttach($task->department);
    }
}