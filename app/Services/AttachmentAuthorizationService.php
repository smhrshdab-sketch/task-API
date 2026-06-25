<?php
    namespace App\Services;
    use App\Models\Account;
    use App\Models\Department;

    class AttachmentAuthorizationService{
        protected function hasCreatePermission(): bool{
            $permissions = current_membership()->getCachedPermissions();
            //$msg = current_membership()->account->name;
            //logger()->warning('in (hasCreatePermission) ',[$msg]);
            // حالت override (کلیدی)
            if (array_key_exists('create', $permissions)) {
                //$approve1 = $permissions['create'] === true;
                //logger()->warning('He/She has CREATE permission (1)',[$approve1]);
                return $permissions['create'] === true;
            }

            // حالت معمولی (لیستی)
            //$approve2 = in_array('create', $permissions, true);
            //logger()->warning('He/She has CREATE permission (2)',[$approve2]);
            return in_array('create', $permissions, true);
        }
        public function canAttach(Department $dep): bool{
            $currentMembership = current_membership();
            logger()->info('Authorization check', [
                'current_department' => $currentMembership?->department_id,
                'target_department' => $dep->id,
                'is_same_department' => $currentMembership?->department_id === $dep->id,
                'permissions' => $currentMembership?->getCachedPermissions()
            ]);
            
            if (!$currentMembership) {
                logger()->warning('No current membership');
                return false;
            }
            if($currentMembership->department_id !== $dep->id){
                logger()->error('task and membership are not in same department!');
                return false;
            }
            return $this->hasCreatePermission();
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
    }