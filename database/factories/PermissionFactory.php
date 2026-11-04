<?php

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permission>
 */
class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Permission::class;

    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug(),
            'target' => 'department',
            'subject' => 'view',
            'description' => $this->faker->sentence(),
            'is_active' => true,
            'is_delegable' => false,
        ];
    }
}
