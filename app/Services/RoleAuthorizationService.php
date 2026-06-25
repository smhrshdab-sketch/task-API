<?php
    namespace App\Services;//خیلی ضروری

use Illuminate\Support\Facades\Log;

    class RoleAuthorizationService{
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

            Log::warning('RoleAuthorizationService: No '.$permission.' permission', [
                'membership_id' => current_membership()->id,
                'permissions' => $permissions
            ]);
        
            return false;
        }
        //---------------------------------------------
        public function canCreate(): bool{
            return $this->hasPermission('role_create');
        }
        public function canUpdate(): bool{
            return $this->hasPermission('role_update');
        }
        public function canDelete(): bool{
            return $this->hasPermission('role_delete');
        }
        public function canSee(): bool{            
            return $this->hasPermission('role_see');
        }
        public function canSeeAll(): bool{            
            return $this->hasPermission('role_view_all');
        }
    }