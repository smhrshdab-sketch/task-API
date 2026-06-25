<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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
            Role::create([
                'title' => ucfirst($role),
                'slug'  => Str::slug($role),
            ]);
        }
    }
}
