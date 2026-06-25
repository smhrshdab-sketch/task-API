<?php
    namespace App\Services;//خیلی ضروری
    use App\Models\Account;
    use App\Models\Membership;

    class AccountAuthorizationService{
        protected function hasViewAllPermission(string $permission): bool{
            $permissions = current_membership()->getCachedPermissions();

            if (array_key_exists($permission, $permissions)) {
                return $permissions[$permission] === true;
            }

            return in_array($permission, $permissions, true);
        }
        public function canCreate(Account $user): bool{
            return true;
        }
        public function canUpdate(Membership $member): bool{
            return true;
        }
        public function canDelete(Account $user): bool{
            if(current_membership()->department_id != 10){
                logger()->error("current membership does not belong to speific departmant",[
                    'currrent department id : ' => current_membership()->id,
                    'target department id: 14' 
                ]);
                return false;
            }
            $permissions = current_membership()->getCachedPermissions();

            // حالت override (کلیدی)
            if (array_key_exists('delete', $permissions)) {
                return $permissions['delete'] === true;
            }
            logger()->error("current membership does not have override permission to delete");

            // حالت معمولی (لیستی)
            return in_array('delete', $permissions, true);
        }
        public function canSee(Account $user, Account $task): bool{
            return true;
        }
        public function canSeeAll(): bool{            
            return $this->hasViewAllPermission('account_view_all');
        }
    }