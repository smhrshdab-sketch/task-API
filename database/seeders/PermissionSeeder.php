<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'slug' => 'department_create',
                'target' => 'department',
                'subject' => 'create',
                'scope' => 'department',
                'is_delegable' => true,
                'description' => 'Create a child department.',
            ],
            [
                'slug' => 'department_update',
                'target' => 'department',
                'subject' => 'update',
                'scope' => 'department',
                'is_delegable' => true,
                'description' => 'Update a department.',
            ],
            [
                'slug' => 'department_delete',
                'target' => 'department',
                'subject' => 'delete',
                'scope' => 'department',
                'is_delegable' => true,
                'description' => 'Delete a department.',
            ],
            [
                'slug' => 'department_view',
                'target' => 'department',
                'subject' => 'view',
                'scope' => 'department',
                'is_delegable' => true,                
                'description' => 'View departments within the allowed scope.',
            ],
            [
                'slug' => 'department_view_all',
                'target' => 'department',
                'subject' => 'view_all',
                'scope' => 'organization',
                'is_delegable' => false,
                'description' => 'View all departments within the organization.',
            ],
            [
                'slug' => 'root_department_create',
                'target' => 'root_department',
                'subject' => 'create',
                'scope' => 'organization',
                'is_delegable' => false,
                'description' => 'Create a root department.',
            ],
            [
                'slug' => 'root_department_update',
                'target' => 'root_department',
                'subject' => 'update',
                'scope' => 'organization',
                'is_delegable' => false,
                'description' => 'Update a root department.',
            ],
            [
                'slug' => 'root_department_delete',
                'target' => 'root_department',
                'subject' => 'delete',
                'scope' => 'organization',
                'is_delegable' => false,
                'description' => 'Delete a root department.',
            ],
            [
                'slug' => 'membership_manage_permissions',
                'target' => 'membership',
                'subject' => 'manage_permissions',
                'scope' => 'department',
                'is_delegable' => false,
                'description' => 'Manage direct permissions assigned to memberships.',
            ]
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
