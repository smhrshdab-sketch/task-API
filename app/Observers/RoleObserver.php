<?php

namespace App\Observers;

use App\Models\Role;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RoleObserver{
    protected AuditLogService $auditLogService;
    public function __construct(AuditLogService $auditLogService){
        $this->auditLogService = $auditLogService;
    }
    /**
     * Handle the Role "created" event.
     */
    public function created(Role $role): void{
        $this->auditLogService->record(
            $role,
            'created', [
            'description' => 'Role created',
            'metadata' => ['table' => $role->getTable(),],
        ]);
        Cache::forget('roles_map');
        Log::info('All role caches cleared');
    }

    /**
     * Handle the Role "updated" event.
     */
    public function updated(Role $role): void{
        $this->auditLogService->record(
            $role,
            'updated', [
            'description' => 'Role updated',
            'metadata' => ['table' => $role->getTable(),],
            'changes' => $role->getChanges(),
            'original' => $role->getOriginal()
        ]);
        Cache::forget('roles_map');
        Log::info('All role caches cleared');
    }

    /**
     * Handle the Role "deleted" event.
     */
    public function deleted(Role $role): void{
        $this->auditLogService->record($role, 'deleted', [
            'description' => 'Role deleted',
            'metadata' => [
                'table' => $role->getTable(),
                'deleted_data' => $role->toArray(),
            ],
        ]);
        Cache::forget('roles_map');
        Log::info('All role caches cleared');
    }

    /**
     * Handle the Role "restored" event.
     */
    public function restored(Role $role): void
    {
        //
    }

    /**
     * Handle the Role "force deleted" event.
     */
    public function forceDeleted(Role $role): void
    {
        //
    }
}
