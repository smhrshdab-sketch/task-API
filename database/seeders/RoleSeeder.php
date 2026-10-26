<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'manager',
            'supervisor',
            'operator',
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role],
                [
                    'title' => ucfirst($role),
                ]
            );
        }
    }
}
