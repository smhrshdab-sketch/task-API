<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = Permission::pluck('id', 'slug');

        $roles = [
            'manager' => [
                'department_view',
                'department_create',
                'department_update',
                'department_delete',
                'department_view_all',
            ],

            'supervisor' => [
                'department_view',
                'department_create',
                'department_update',
            ],

            'operator' => [
                'department_view',
            ],
        ];
        /*
            roles : آرایه اصلی که عملیات روی آن صورت میگیرد
            roleSlug : اندیس آرایه اصلی. حکم همان اعداد اندیس هارا دارد
            permissionSlugs : محتوای این سلول از آرایه
            foreach ($roles as $roleSlug => $permissionSlugs) :
                روی آرایه
                roles                
                حلقه بزن.به طوری که اندیست
                roleSlug
                است. محتوای سلول آرایه در هر مرحله در 
                permissionSlugs
                ریخته می شود. چون خود 
                permissionSlugs
                یک آزایه است می توان روی آن هم دوباره حلقه زد
         */
        foreach ($roles as $roleSlug => $permissionSlugs) {
            $role = Role::where('slug', $roleSlug)->firstOrFail();

            $permissionIds = collect($permissionSlugs)
                ->map(fn (string $slug) => $permissions->get($slug))
                ->filter()
                ->values()
                ->all();

            $role->permissions()->sync($permissionIds);
            /*
            $permissionIds = [];
            foreach ($permissionSlugs as $permissionSlug) {
                $permissionIds[] = $permissions[$permissionSlug];
            }
             */
        }
    }
}