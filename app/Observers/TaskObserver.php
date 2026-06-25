<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\Task;
use App\Services\AuditLogService;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    protected AuditLogService $auditLogService;
    public function __construct(AuditLogService $auditLogService){
        $this->auditLogService = $auditLogService;
    }
    public function created(Task $task): void{
        $this->auditLogService->record(
            $task,
            'created', [
            'description' => 'Task created',
            'metadata' => ['table' => $task->getTable(),],
        ]);
    }

    /**
     * Handle the Task "updated" event.
     */
    public function update(Account $user, Task $task): bool{
        // 1. پیدا کردن عضویت کاربر در واحد مربوط به task
        // $membership = $user->memberships()
        //     ->where('department_id', $task->department_id)
        //     ->first();
        // if (!$membership) {
        //     return false;
        // }

        // // 2. گرفتن مجوز نهایی
        // return in_array(
        //     'task.update',
        //     $membership->effective_permissions
        // );
        $this->auditLogService->record(
            $task,
            'updated', [
            'description' => 'Task updated',
            'metadata' => ['table' => $task->getTable(),],
            'changes' => $task->getChanges(),
            'original' => $task->getOriginal()
        ]);
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        $this->auditLogService->record($task, 'deleted', [
            'description' => 'Task deleted',
            'metadata' => [
                'table' => $task->getTable(),
                'deleted_data' => $task->toArray(),
            ],
        ]);
    }

    /**
     * Handle the Task "restored" event.
     */
    public function restored(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "force deleted" event.
     */
    public function forceDeleted(Task $task): void
    {
        //
    }
}
