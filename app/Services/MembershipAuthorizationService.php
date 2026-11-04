<?php
    namespace App\Services;//خیلی ضروری

use App\Models\Membership;
use App\Models\Permission;

    class MembershipAuthorizationService{
        protected function hasPermission(string $permission): bool{
            $membership = current_membership();

            if (!$membership) {
                return false;
            }

            return $membership->hasPermission($permission);
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
            logger(" -- isWithinScope -- ");
            $currentDepartment = current_department();

            if (!$currentDepartment) {
                logger("currentDepartment is not set");
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
            logger("membership and target are not in same scope");
            return false;
        }
        public function canDelegate(Permission $permission, Membership $membership): bool{
            logger(" -- canDelegate -- ");
            if (!$permission->is_delegable) {
                logger("permision is not able to delegable");
                return false;
            }

            if (!$this->hasPermission('membership_manage_permissions')) {
                logger("membership has not the membership_manage_permissions permission");
                return false;
            }

            if (!$this->hasPermission($permission->slug)) {
                logger("membership has not the permission permission");
                return false;
            }

            return $this->isWithinScope($membership);
        }
    }