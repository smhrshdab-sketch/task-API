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
         foreach ($event->membershipIds as $id) {
            Engage::create([
                'task' => $event->task->id,
                'contributor' => $id,
                'contributed_by' => current_membership()->id
            ]);
        }
    }
}
