<?php

namespace Database\Factories;

use App\Models\Project;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Projec>
 */
use App\Models\Department;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

use function Symfony\Component\Clock\now;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition()
    {
        return [
            'department_id' => Department::factory(),
            'organization_id' => Organization::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'status' => 'active',
            'start_date' => $this->faker->date(),  // Random past date
        ];
    }
}

