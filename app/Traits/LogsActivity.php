<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait LogsActivity{
    /**
     * Log an activity
     */
    public function logActivity(string $action, array $data = [])
    {
        Log::info($action, array_merge($data, [
            'membership_id' => current_membership()->id,
            'ip' => request()->ip(),
            'timestamp' => now()
        ]));
    }
    
    /**
     * Log an error
     */
    public function logError(\Exception $e, string $context = '')
    {
        Log::error($context, [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
}