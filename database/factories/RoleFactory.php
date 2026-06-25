<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
   public function definition(): array{
        $title = $this->faker->unique()->word();

        return [
            'title'       => $title,
            'slug'        => Str::slug($title),
            'permissions' => json_encode([]),
        ];
    }
}
