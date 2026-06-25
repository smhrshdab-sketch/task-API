<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
use App\Models\Department;
use App\Models\Organization;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition()
    {
        return [
            'organization_id' => Organization::factory(),
            'title' => $this->faker->word,
            'description' => $this->faker->sentence,
            'path'            => '1', // 👈 حداقل مقدار معتبر
        ];
    }// state مخصوص root
    public function root(): static{
        return $this->state(fn () => [
            'path' => '1',
        ]);
    }
}

