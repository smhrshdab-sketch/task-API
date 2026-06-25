<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membership>
 */
use App\Models\Account;
use App\Models\Organization;
use App\Models\Department;
use App\Models\Role;

class MembershipFactory extends Factory
{
    protected $model = \App\Models\Membership::class;

    public function definition(): array
    {
        return [
            'account_id'       => Account::factory(),
            'organization_id'  => Organization::factory(),
            'department_id'    => Department::factory(),
            'role_id'          => Role::factory(),
            'status'           => 'active',
            'permissions_override' => null,
        ];
    }
}

