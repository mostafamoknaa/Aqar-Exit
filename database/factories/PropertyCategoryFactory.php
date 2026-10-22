<?php

namespace Database\Factories;

use App\Models\PropertyCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<PropertyCategory> */
class PropertyCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return ['name' => ucfirst($name), 'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999), 'icon' => 'building', 'is_active' => true];
    }
}
