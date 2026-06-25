<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\Membership;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Cache;

class MembershipObserver{
    protected AuditLogService $auditLogService;
    public function __construct(AuditLogService $auditLogService){
        $this->auditLogService = $auditLogService;
    }
    /**
     * Handle the Membership "created" event.
     */
    // public function created(Membership $membership): void{
    //     $this->auditLogService->record(
    //         $membership,
    //         'created', [
    //         'description' => 'Membership created',
    //         'metadata' => ['table' => $membership->getTable(),],
    //     ]);
    // }

    /**
     * Handle the Membership "updated" event.
     */
    public function updated(Membership $membership): void
    {
        if($membership->isDirty('status')){
            Cache::forget("permissions:membership:{$membership->id}");
        }
        $this->auditLogService->record(
            $membership,
            'updated', [
            'description' => 'Membership updated',
            'metadata' => ['table' => $membership->getTable(),],
            'changes' => $membership->getChanges(),
            'original' => $membership->getOriginal()
        ]);
    }
    //---------
    // public function saved(Membership $membership)
    // {
    //     //Cache::forget("membership_permissions_{$membership->account_id}");
    //     Cache::forget("permissions:membership:{$membership->id}");
    // }
    /**
     * Handle the Membership "deleted" event.
     */
    public function deleted(Membership $membership)
    {
        Cache::forget("permissions:membership:{$membership->id}");
        //Cache::forget("membership_permissions_{$membership->account_id}");
        $this->auditLogService->record($membership, 'deleted', [
            'description' => 'Membership deleted',
            'metadata' => [
                'table' => $membership->getTable(),
                'deleted_data' => $membership->toArray(),
            ],
        ]);
    }

    /**
     * Handle the Membership "restored" event.
     */
    public function restored(Membership $membership): void
    {
        //
    }

    /**
     * Handle the Membership "force deleted" event.
     */
    public function forceDeleted(Membership $membership): void
    {
        //
    }
    //superuser
    public function before(Account $user, string $ability){
        if ($user->hasRole('admin')) {
            return true;
        }
    }
}
