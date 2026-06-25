<?php
    namespace App\Services;

use App\Models\AuditLog;
use App\Models\AuditLogChange;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

    class AuditLogService{
        public function record(Model $model, string $event, array $context = []): AuditLog{
            $auditLog = AuditLog::create([
                'subject_type' => get_class($model),
                'subject_id'   => $model->getKey(),
                'event'        => $event,
                'description'  => $context['description'] ?? null,
                'batch_id'     => $context['batch_id'] ?? null,
                'ip_address'   => request()?->ip(),
                'user_agent'   => request()?->userAgent(),
                'url'          => request()?->fullUrl(),
                'method'       => request()?->method(),
                'metadata'     => $context['metadata'] ?? [],
                'causer_id'    => current_membership()?->id,
            ]);

            if ($event === 'updated') {
                $this->recordChanges($auditLog, $model, $context);
            }

            return $auditLog;
        }

        protected function recordChanges(AuditLog $auditLog, Model $model, array $context = []): void{
            $changes = $context['changes'] ?? $model->getChanges();
            $original = $context['original'] ?? $model->getOriginal();
            //$ignored = ['updated_at', 'created_at'];
            foreach ($changes as $field => $newValue) {
                if (in_array($field, ['created_at', 'updated_at'], true)) {
                    continue;
                }

                AuditLogChange::create([
                    'audit_log_id' => $auditLog->id,
                    'field_name'   => $field,
                    'old_value'    => $original[$field] ?? null,
                    'new_value'    => $newValue,
                    'value_type'   => is_array($newValue) ? 'array' : gettype($newValue),
                ]);
            }
        
        }
        public function getAllAuditLogsWithoutPagination(): Collection{
            logger()->info('you are in (AuditLogService) and you are: ',[current_membership()->account->name]);
            return AuditLog::orderBy('created_at', 'desc')->get();
        }
        public function getAllAuditLogsWithPagination(array $data): LengthAwarePaginator{
        //logger('(AuditLogService)parentId: ',[$data['parentId']]);
        $perPage = $data['perPage'] ?? 10;
        $search = $data['search'] ?? '';
        
        Log::info('AuditLogService: Fetching auditLogs', [
            'perPage' => $perPage,
            'search' => $search
        ]);
        
        $query = AuditLog::orderBy('created_at', 'desc');
        // Add search functionality
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('subject_type', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $departments = $query->paginate($perPage);
        
        Log::info('DepartmentService: Results', [
            'total' => $departments->total(),
            'current_page' => $departments->currentPage(),
            'last_page' => $departments->lastPage()
        ]);
        return $departments;
    }
    }