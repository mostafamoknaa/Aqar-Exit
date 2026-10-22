<?php

namespace Database\Factories;

use App\Enums\ListingStatus;
use App\Enums\UnitType;
use App\Models\Listing;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Listing> */
class ListingFactory extends Factory
{
    public function definition(): array
    {
        $total = fake()->numberBetween(3, 20) * 500_000;
        $paid = (int) ($total * fake()->randomFloat(2, 0.1, 0.4));

        return [
            'seller_id' => User::factory(),
            'project_id' => Project::factory(),
            'title' => fake()->sentence(4),
            'unit_type' => fake()->randomElement(UnitType::cases()),
            'city' => fake()->randomElement(['New Cairo', '6th of October', 'New Capital']),
            'area_sqm' => fake()->numberBetween(70, 350),
            'bedrooms' => fake()->numberBetween(1, 5),
            'bathrooms' => fake()->numberBetween(1, 3),
            'total_price' => $total,
            'paid_amount' => $paid,
            'market_price' => (int) ($total * 1.35),
            'installment_amount' => fake()->numberBetween(20_000, 90_000),
            'status' => ListingStatus::Published,
            'published_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(fn (Listing $l) => $l->developer_id = $l->project?->developer_id);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => ListingStatus::PendingReview, 'published_at' => null]);
    }
}
