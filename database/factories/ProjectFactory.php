<?php

namespace Database\Factories;

use App\Models\Developer;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Project> */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'developer_id' => Developer::factory(),
            'name' => ucwords($name), 'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'city' => fake()->randomElement(['New Cairo', '6th of October', 'New Capital', 'North Coast']),
            'district' => fake()->word(), 'is_active' => true,
        ];
    }
}
