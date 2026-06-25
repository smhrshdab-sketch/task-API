<?php

namespace App\Observers;

use App\Models\Department;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DepartmentObserver{    
    protected AuditLogService $auditLogService;
    public function __construct(AuditLogService $auditLogService){
        $this->auditLogService = $auditLogService;
    }
    /**
     * Handle the Department "created" event.
     */
    public function viewed(Department $department): void{
        $this->auditLogService->record(
            $department,
            'viewed', [
            'description' => 'Department viewed',
            'metadata' => ['table' => $department->getTable(),],
        ]);
    }

    public function created(Department $department): void{
        $this->auditLogService->record(
            $department,
            'created', [
            'description' => 'Department created',
            'metadata' => ['table' => $department->getTable(),],
        ]);
        Cache::forget('departments_list');
        Cache::forget('departments_map');
        Log::info('All department caches cleared');
    }

    /**
     * Handle the Department "updated" event.
     */
    public function updated(Department $department): void{
        $this->auditLogService->record(
            $department,
            'updated', [
            'description' => 'Department updated',
            'metadata' => ['table' => $department->getTable(),],
            'changes' => $department->getChanges(),
            'original' => $department->getOriginal()
        ]);
        Cache::forget('departments_list');
        Cache::forget('departments_map');
        Log::info('All department caches cleared');
    }

    /**
     * Handle the Department "deleted" event.
     */
    public function deleted(Department $department): void{
        $this->auditLogService->record($department, 'deleted', [
            'description' => 'Department deleted',
            'metadata' => [
                'table' => $department->getTable(),
                'deleted_data' => $department->toArray(),
            ],
        ]);
        Cache::forget('departments_list');
        Cache::forget('departments_map');
        Log::info('All department caches cleared');
    }


    /**
     * Handle the Department "restored" event.
     */
    public function restored(Department $department): void
    {
        //
    }

    /**
     * Handle the Department "force deleted" event.
     */
    public function forceDeleted(Department $department): void
    {
        //
    }
}
