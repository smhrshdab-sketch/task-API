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
                'description' => 'Create a child department.',
            ],
            [
                'slug' => 'department_update',
                'target' => 'department',
                'subject' => 'update',
                'description' => 'Update a department.',
            ],
            [
                'slug' => 'department_delete',
                'target' => 'department',
                'subject' => 'delete',
                'description' => 'Delete a department.',
            ],
            [
                'slug' => 'department_view',
                'target' => 'department',
                'subject' => 'view',
                'description' => 'View departments within the allowed scope.',
            ],
            [
                'slug' => 'department_view_all',
                'target' => 'department',
                'subject' => 'view_all',
                'description' => 'View all departments within the organization.',
            ],
            [
                'slug' => 'root_department_create',
                'target' => 'root_department',
                'subject' => 'create',
                'description' => 'Create a root department.',
            ],
            [
                'slug' => 'root_department_update',
                'target' => 'root_department',
                'subject' => 'update',
                'description' => 'Update a root department.',
            ],
            [
                'slug' => 'root_department_delete',
                'target' => 'root_department',
                'subject' => 'delete',
                'description' => 'Delete a root department.',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
