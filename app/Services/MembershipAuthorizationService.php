<?php
    namespace App\Services;//خیلی ضروری

use App\Models\Membership;
use App\Models\Permission;
use Illuminate\Support\Facades\Log;

    class MembershipAuthorizationService{
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

            Log::warning('MembershipAuthorizationService: No '.$permission.' permission', [
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
            return $this->hasPermission('membership_update');
        }
        public function canDelete(): bool{
            return $this->hasPermission('membership_delete');
        }
        public function canSee(): bool{            
            return $this->hasPermission('membership_see');
        }
        public function canSeeAll(): bool{            
            return $this->hasPermission('membership_view_all');
        }
        //==========================================
        public function isWithinScope(Membership $target): bool{
            $currentDepartment = current_department();

            if (!$currentDepartment) {
                return false;
            }
            $departmentTarget = $target->department;
            if ($currentDepartment->id === $departmentTarget) {
                return true;
            }

            $parent = $departmentTarget->parent;

            while ($parent) {
                if ($parent->id === $currentDepartment->id) {
                    return true;
                }

                $parent = $parent->parent;
            }

            return false;
        }
        public function canDelegate(Permission $permission, Membership $membership): bool{
            if (! $permission->is_delegable) {
            return false;
            }

            if (! $this->hasPermission('membership_manage_permissions')) {
            return false;
            }

            if (! $this->hasPermission($permission->slug)) {
            return false;
            }

            return $this->isWithinScope($membership);
        }
    }