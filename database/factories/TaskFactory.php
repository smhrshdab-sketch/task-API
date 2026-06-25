<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
use App\Models\Task;
use App\Models\Project;
use App\Models\Account;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition()
    {
        $project = Project::factory()->create();
        $department = $project->department;

        $assignee = $department->accounts()->inRandomOrder()->first()
            ?? Account::factory()->create();

        return [
            'project_id' => $project->id,
            'department_id' => $department->id,
            'assignee_id' => $assignee->id,
            'title' => $this->faker->sentence,
            'status' => 'pending',
            'priority' => 'normal',
        ];
    }
}

