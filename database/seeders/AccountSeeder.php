<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Account;
use App\Models\Department;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $department = Department::factory()->create();
        $accounts = Account::factory(5)->create();
        $roles = Role::pluck('id');

        foreach ($accounts as $account) {
            $department->accounts()->attach(
                $account->id,
                ['role_id' => $roles->random()]
            );
        }
    }
}
