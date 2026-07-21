<?php
    namespace App\Exceptions;

use App\Models\AuditLog;
use Exception;
use Illuminate\Http\JsonResponse;
    
    class MembershipContextMissingException extends Exception{
        public function report(): void{
            AuditLog::create([
                'subject_type' => 'Attachment',
                'causer_id'    => current_membership()?->id,
                'event'        => 'error',
                'description'  => $this->getMessage(),
                'url'          => request()->fullUrl(),
                'method'       => request()->method(),
                'metadata'     => json_encode(['exception' => get_class($this)]),
            ]);
        }
        public function render($request): JsonResponse{
            return response()->json([
                'success' => false,
                'message' => 'No membership context found. Please select a department.'
            ], 401);
        }
    }
    