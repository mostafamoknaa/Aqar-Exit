<?php

namespace Database\Factories;

use App\Models\Evaluation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Evaluation> */
class EvaluationFactory extends Factory
{
    public function definition(): array
    {
        $total = fake()->numberBetween(3, 15) * 500_000;

        return ['name' => fake()->name(), 'phone' => fake()->numerify('01#########'), 'total_price' => $total, 'paid_amount' => (int) ($total * 0.25), 'status' => 'new'];
    }
}
