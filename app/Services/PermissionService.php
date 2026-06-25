<?php

use App\Models\Membership;
use App\Models\Role;

class PermissionService
{
    public function resolvePermissions(Membership $membership): array{
        $role = $membership->role;

        return $this->resolveRolePermissions($role);
    }

    protected function resolveRolePermissions(Role $role, array $visited = []): array{
        // جلوگیری از حلقه (اگر اشتباه والد دایره‌ای بود)
        if (in_array($role->id, $visited, true)) {
            return [];
        }

        $visited[] = $role->id;

        // دسترسی های خود نقش 
        $permissions = $role->permissions
            ->pluck('name')
            ->toArray();

        // اگر والد داشت، دسترسی اون رو هم اضافه کن
        if ($role->parent) {
            $permissions = array_merge(
                $permissions,
                $this->resolveRolePermissions($role->parent, $visited)
            );
        }

        return array_values(array_unique($permissions));
    }

    public function hasPermission(Membership $membership, string $permission): bool{
        return in_array(
            $permission,
            $this->resolvePermissions($membership),
            true
        );
    }
}