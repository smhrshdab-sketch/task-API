<?php

namespace App\Listeners;

use App\Models\Engage;

class AttachMembersToTask
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void{
         // دسترسی به آرایه از طریق شیء event
        $currentMembershipId = current_membership()->id ?? null;
        logger('Listener: current_membership()->id: ', [$currentMembershipId]);
        logger('Listener: event->membershipIds: ', $event->membershipIds);
        logger('Listener: event->task->id: ', [$event->task->id]);
         foreach ($event->membershipIds as $id) {
            $createData = [
                'contributed_by' => current_membership()->id,
                'contributor' => $id,
                'task' => $event->task->id              
            ];
            logger('Attempting to create Engage with: ', $createData);
            
            Engage::create($createData);
        }
    }
}
