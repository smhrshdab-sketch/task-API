<?php

namespace App\Listeners;

use App\Events\DepartmentDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateDepartmentPaths
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
    public function handle(DepartmentDeleted $event): void
    {
        //
    }
}
