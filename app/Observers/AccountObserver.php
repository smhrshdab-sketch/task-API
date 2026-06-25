<?php

namespace App\Observers;

use App\Models\Account;
use App\Services\AuditLogService;

class AccountObserver{
    protected AuditLogService $auditLogService;
    public function __construct(AuditLogService $auditLogService){
        $this->auditLogService = $auditLogService;
    }
    /**
     * Handle the Account "created" event.
     */
    // public function created(Account $account): void{
    //     $this->auditLogService->record(
    //         $account,
    //         'created', [
    //         'description' => 'Account created',
    //         'metadata' => ['table' => $account->getTable(),],
    //     ]);
    // }

    /**
     * Handle the Account "updated" event.
     */
    public function updated(Account $account): void{
        $this->auditLogService->record(
            $account,
            'updated', [
            'description' => 'Account updated',
            'metadata' => ['table' => $account->getTable(),],
            'changes' => $account->getChanges(),
            'original' => $account->getOriginal()
        ]);
    }

    /**
     * Handle the Account "deleted" event.
     */
    public function deleted(Account $account): void{
        $this->auditLogService->record($account, 'deleted', [
            'description' => 'Account deleted',
            'metadata' => [
                'table' => $account->getTable(),
                'deleted_data' => $account->toArray(),
            ],
        ]);
    }

    /**
     * Handle the Account "restored" event.
     */
    public function restored(Account $account): void
    {
        //
    }

    /**
     * Handle the Account "force deleted" event.
     */
    public function forceDeleted(Account $account): void
    {
        //
    }
}
