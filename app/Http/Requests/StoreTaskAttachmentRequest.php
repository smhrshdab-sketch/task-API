<?php

namespace App\Http\Requests;

use App\Contracts\Attachable;
use App\Http\Requests\BaseAttachmentRequest;
use App\Services\AttachmentAuthorizationService;
use Illuminate\Support\Facades\Log;

class StoreTaskAttachmentRequest extends BaseAttachmentRequest
{
    protected function getAttachableModel(): ?Attachable{
        // Get the task from the route and return it
        Log::info('👿 StoreTaskAttachmentRequest getAttachableModel REACHED 👿');
        $task = $this->route('task');
        logger('Selected task is: ',[$task->title]);
        // Make sure it implements Attachable
        if ($task instanceof Attachable) {
            return $task;
        }
        
        return null;
    }
    
    protected function authorizeAttachment(Attachable $attachable): bool
    {
        Log::info('🤖 StoreTaskAttachmentRequest authorizeAttachment REACHED 🤖');
        $task = $attachable;
        $membership = current_membership();
        logger('route tassk [authorizeAttachment]: ',[$task->title]);
        if (!$membership) {
            return false;
        }
        
        // Check if user is in the same department as the task    
        if($membership->department->id !== $task->department->id){
            Log::info('Now we know user and task are NOT in same department'); 
            logger('department->id: ',[$membership->department->id]);
            Log::info('task department ID: ',[$task->department->id]);           
            return false;
        }
        Log::info('Now we know user and task are in same department');
        $result = app(AttachmentAuthorizationService::class)->canAttach($task->department);
        Log::info('AttachmentAuthorizationService result: ',[$result]);        
        return $result;
    }
}