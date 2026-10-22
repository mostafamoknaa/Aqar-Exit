<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AqarExitApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_use_exit_calculator(): void
    {
        $this->postJson('/api/v1/evaluations/calculate', ['total_price' => 10_000_000, 'paid_amount' => 2_000_000])
            ->assertOk()
            ->assertJsonPath('data.cancellation_penalty', 1_500_000)
            ->assertJsonPath('data.refund_if_cancelled', 500_000)
            ->assertJsonPath('data.refund_with_aqar_exit', 2_000_000);
    }

    public function test_register_login_and_publish_flow(): void
    {
        $token = $this->postJson('/api/v1/auth/register', [
            'name' => 'Seller', 'phone' => '01012345678', 'password' => 'password', 'password_confirmation' => 'password',
        ])->assertCreated()->json('data.token');

        $listingId = $this->withToken($token)->postJson('/api/v1/my-listings', [
            'title' => 'Apartment in Mivida', 'unit_type' => 'apartment', 'city' => 'New Cairo',
            'area_sqm' => 150, 'bedrooms' => 3, 'total_price' => 8_000_000, 'paid_amount' => 1_600_000,
        ])->assertCreated()->json('data.id');

        // Draft is invisible to the public, seller can see it
        $this->getJson('/api/v1/listings')->assertOk()->assertJsonCount(0, 'data');
        $this->withToken($token)->getJson('/api/v1/my-listings')->assertOk()->assertJsonCount(1, 'data');

        Listing::find($listingId)->update(['status' => ListingStatus::Published, 'published_at' => now()]);
        $this->getJson("/api/v1/listings/{$listingId}")->assertOk()->assertJsonPath('data.cash_required', 1_600_000);
    }

    public function test_buyer_can_favorite_and_express_interest(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = Listing::factory()->create(['seller_id' => $seller->id]);

        $this->actingAs($buyer, 'sanctum')->postJson("/api/v1/listings/{$listing->id}/favorite")->assertOk()->assertJsonPath('data.is_favorite', true);
        $this->actingAs($buyer, 'sanctum')->postJson("/api/v1/listings/{$listing->id}/interest", ['cash_available' => $listing->paid_amount])->assertCreated();
        $this->assertDatabaseHas('interests', ['listing_id' => $listing->id, 'buyer_id' => $buyer->id]);
    }
}
