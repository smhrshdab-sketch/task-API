<?php
    namespace App\Services;//خیلی ضروری

    use App\Models\Role;
    use Illuminate\Database\Eloquent\Collection;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;

    class RoleService{

        public function getAllRolesWithoutPagination(): Collection{
            logger()->info('you are in (roleService) and you are: ',[current_membership()->account->name]);
            return Role::orderBy('created_at', 'desc')->get();
        }
        //========================
        public function createRole(array $data): Role{
            Log::info('🔵🔵🔵 CREATE Role SERVICE REACHED 🔵🔵🔵');
            logger('data and role: ',[$data]);
            return DB::transaction(function () use ($data) {
                if (isset($data['permissions']) && is_array($data['permissions'])) {
                    $data['permissions'] = json_encode($data['permissions']);
                }
                
                // Create the role
                $role = Role::create([...$data]);
                $this->getRoleMap();
                Log::info('Role created successfully', [
                    'role_id' => $role->id,
                    'title' => $role->title,
                    'slug' => $role->slug,
                    'parent_id' => $role->parent_id,
                    'permissions' => $role->permissions
                ]);
                
                return $role->fresh();
            });
        }
        //========================
        public function updateRole(array $data, Role $role): Role{
            Log::info('🔵🔵🔵 UPDATE Role SERVICE REACHED 🔵🔵🔵');
            logger('data and role: ',[$data,$role]);
            return DB::transaction(function () use ($data, $role) {
                // Update the role with the provided data
                $role->update($data);
                $this->getRoleMap();
                Log::info('Role updated successfully', [
                    'role_id' => $role->id,
                    'title' => $role->title,
                    'slug' => $role->slug,
                    'parent_id' => $role->parent_id,
                    'permissions' => $role->permissions
                ]);
                
                return $role->fresh(); // Return fresh instance with updated data
            });
        }
        //=======================
        public function deleteRole(Role $role): void{

            logger()->info('Before delete', [
                'role_id' => $role->id,
                'deleted_at' => $role->deleted_at,
                'exists' => $role->exists
            ]);
            
            $result = $role->delete();
            $this->getRoleMap();
            Log::info('Account is deleted successfully');
        }
        public function getRoleMap(): array{
            $key = 'roles_map';
            //Log::info('🔵 🔵 getRoleMap Role SERVICE REACHED 🔵 🔵');
            return Cache::remember($key, now()->addMinutes(30), function () {
                $result = Role::orderBy('slug')
                    ->get(['id', 'slug as description'])
                    ->toArray();                    
                    //logger('getRoleMap: ',[$result]);
                return $result;
            });
        }
    }