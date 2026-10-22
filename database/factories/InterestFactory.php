<?php

namespace Database\Factories;

use App\Models\Interest;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Interest> */
class InterestFactory extends Factory
{
    public function definition(): array
    {
        return ['listing_id' => Listing::factory(), 'buyer_id' => User::factory(), 'status' => 'new', 'cash_available' => fake()->numberBetween(500_000, 3_000_000)];
    }
}
