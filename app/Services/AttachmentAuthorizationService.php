<?php
    namespace App\Services;
    use Illuminate\Support\Facades\Log;

    class AttachmentAuthorizationService{
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

            Log::warning('AttachmentAuthorizationService: No '.$permission.' permission', [
                'membership_id' => current_membership()->id,
                'permissions' => $permissions
            ]);
        
            return false;
        }  
        public function canAttach(): bool{
            return $this->hasPermission('attachment_add');
        }
        public function canDelete(): bool{
            return $this->hasPermission('attachment_delete');
        }
        public function canSee(): bool{            
            return $this->hasPermission('attachment_see');
        }
        public function canSeeAll(): bool{            
            return $this->hasPermission('attachment_view_all');
        } 
    }