<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Department;
use App\Models\Role;
use App\Models\Task;
use App\Models\Project;
use App\Models\Account;
use Database\Seeders\RoleSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(){
        $org = Organization::factory()->create();

        $departments = Department::factory(3)
            ->for($org)
            ->create();

        $this->call([
                RoleSeeder::class,
            ]);

        foreach ($departments as $department) {
            $accounts = Account::factory(5)->create();

            foreach ($accounts as $account) {
                $department->accounts()->attach(
                    $account->id,
                    ['role_id' => Role::inRandomOrder()->first()->id]
                );
            }

            Project::factory(2)
                ->for($department)
                ->create()
                ->each(function ($project) {
                    Task::factory(5)->for($project)->create();
                });
        }
    }

}
