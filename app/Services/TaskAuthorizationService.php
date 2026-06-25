<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class TaskAuthorizationService
{
    protected function hasPermission(string $permission): bool{
            $permissions = current_membership()->getCachedPermissions();
            if (array_key_exists('admin', $permissions) && $permissions['admin'] === true) {
                return true;
            }
            
            // Check for wildcard permission
            if (array_key_exists('*', $permissions) && $permissions['*'] === true) {
                return true;
            }
            if (array_key_exists($permission, $permissions)) {
                //logger('array_key_exists: ',[$permissions,$permission]);
                return $permissions[$permission] === true;
            }
            if(in_array($permission, $permissions, true)){
                //logger('in_array: ',[$permissions,$permission]);
                return true;
            }

            Log::warning('TaskAuthorizationService: No '.$permission.' permission', [
                'membership_id' => current_membership()->id,
                'permissions' => $permissions
            ]);
        
            return false;
        }
        //---------------------------------------------
        public function canCreate(): bool{
            //logger('TaskAuthorizationService (createTask) []: ',[]);
            return $this->hasPermission('task_create');
        }
        public function canUpdate(): bool{
            return $this->hasPermission('task_update');
        }
        public function canDelete(): bool{
            return $this->hasPermission('task_delete');
        }
        public function canSee(): bool{            
            return $this->hasPermission('task_see');
        }
        public function canSeeOnes(): bool{            
            return $this->hasPermission('task_see_ones');
        }
        public function canSeeAll(): bool{            
            return $this->hasPermission('task_view_all');
        }
}
