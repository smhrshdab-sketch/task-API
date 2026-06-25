<?php

namespace App\Observers;

use App\Models\Attachment;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AttachmentObserver{
    protected AuditLogService $auditLogService;
    public function __construct(AuditLogService $auditLogService){
        $this->auditLogService = $auditLogService;
    }
    private function forgetCache(Attachment $attachment){
        $parent = $attachment->attachable;
        
        if (!$parent) {
            Log::warning('Attachment observer: Parent not found', [
                'attachment_id' => $attachment->id
            ]);
            return;
        }
        
        // Generate cache key using the same method as in TaskService
        $cacheKey = "attachments_{$parent->getMorphClass()}_{$parent->id}";
        
        Cache::forget($cacheKey);
        
        Log::info('Cache cleared for attachments', [
            'parent_type' => $parent->getMorphClass(),
            'parent_id' => $parent->id,
            'cache_key' => $cacheKey
        ]);
    }

    // public function saved(Attachment $attachment)
    // {
    //     $this->forgetCache($attachment);
    // }
    
    public function created(Attachment $attachment){
        $this->forgetCache($attachment);
        $this->auditLogService->record(
            $attachment,
            'created', [
            'description' => 'Attachment created',
            'metadata' => ['table' => $attachment->getTable(),],
        ]);
    }
    // public function update(Attachment $attachment){
    //     $this->forgetCache($attachment);
    //     $this->auditLogService->record(
    //         $attachment,
    //         'updated', [
    //         'description' => 'Attachment updated',
    //         'metadata' => ['table' => $attachment->getTable(),],
    //         'changes' => $attachment->getChanges(),
    //         'original' => $attachment->getOriginal()
    //     ]);
    // }
    public function deleted(Attachment $attachment)
    {
        $this->forgetCache($attachment);
        $this->auditLogService->record($attachment, 'deleted', [
            'description' => 'Attachment deleted',
            'metadata' => [
                'table' => $attachment->getTable(),
                'deleted_data' => $attachment->toArray(),
            ],
        ]);
    }

    public function restored(Attachment $attachment)
    {
        $this->forgetCache($attachment);
    }
}
