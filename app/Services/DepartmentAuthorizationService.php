<?php
    namespace App\Services;//خیلی ضروری

use Illuminate\Support\Facades\Log;

    class DepartmentAuthorizationService{
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
                return $permissions[$permission] === true;
            }
            if(in_array($permission, $permissions, true)){
                return true;
            }

            Log::warning('Department AuthorizationService: No '.$permission.' permission', [
                'membership_id' => current_membership()->id,
                'permissions' => $permissions
            ]);
        
            return false;
        }
        //---------------------------------------------
        public function canCreate(): bool{
            return $this->hasPermission('department_create');
        }
        public function canUpdate(): bool{
            return $this->hasPermission('department_update');
        }
        public function canDelete(): bool{
            return $this->hasPermission('department_delete');
        }
        public function canSee(): bool{            
            return $this->hasPermission('department_see');
        }
        public function canSeeAll(): bool{            
            return $this->hasPermission('department_view_all');
        }
    }