<?php

namespace App\Events;

use App\Models\Department;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DepartmentDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Department $department;

    /**
     * Create a new event instance.
     */
    public function __construct(Department $department){
        $this->department = $department; 
        logger($department->title.' is DELETED(evet)');
    }
}
