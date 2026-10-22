<?php

namespace Database\Factories;

use App\Enums\DealStage;
use App\Models\Deal;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Deal> */
class DealFactory extends Factory
{
    public function definition(): array
    {
        return [
            'listing_id' => Listing::factory(),
            'seller_id' => User::factory(),
            'buyer_id' => User::factory(),
            'stage' => DealStage::NewLead,
            'cash_to_seller' => 1_000_000,
            'total_price' => 5_000_000,
            'commission_rate' => config('aqar.buyer_commission_rate'),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Deal $deal) {
            $deal->seller_id = $deal->listing->seller_id;
            $deal->cash_to_seller = $deal->listing->paid_amount;
            $deal->total_price = $deal->listing->total_price;
        });
    }
}
