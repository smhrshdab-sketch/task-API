<?php

namespace App\Observers;

use App\Models\Organization;
use App\Services\AuditLogService;

class OrganizationObserver
{
    protected AuditLogService $auditLogService;
    public function __construct(AuditLogService $auditLogService){
        $this->auditLogService = $auditLogService;
    }
    /**
     * Handle the Organization "created" event.
     */
    public function created(Organization $organization): void{
        $this->auditLogService->record(
            $organization,
            'created', [
            'description' => 'Organization created',
            'metadata' => ['table' => $organization->getTable(),],
        ]);
    }

    /**
     * Handle the Organization "updated" event.
     */
    public function updated(Organization $organization): void{
        $this->auditLogService->record(
            $organization,
            'updated', [
            'description' => 'Organization updated',
            'metadata' => ['table' => $organization->getTable(),],
            'changes' => $organization->getChanges(),
            'original' => $organization->getOriginal()
        ]);
    }

    /**
     * Handle the Organization "deleted" event.
     */
    public function deleted(Organization $organization): void{
        $this->auditLogService->record($organization, 'deleted', [
            'description' => 'Organization deleted',
            'metadata' => [
                'table' => $organization->getTable(),
                'deleted_data' => $organization->toArray(),
            ],
        ]);
    }

    /**
     * Handle the Organization "restored" event.
     */
    public function restored(Organization $organization): void
    {
        //
    }

    /**
     * Handle the Organization "force deleted" event.
     */
    public function forceDeleted(Organization $organization): void
    {
        //
    }
}
