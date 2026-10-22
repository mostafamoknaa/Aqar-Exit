<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Enums\UserRole;
use App\Models\Interest;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_browse_and_detail_show_only_published(): void
    {
        $published = Listing::factory()->create(['title' => 'Sunny Apartment']);
        Listing::factory()->pending()->create(['title' => 'Hidden Draft Unit']);

        $this->get('/')->assertOk()->assertSee('Sunny Apartment')->assertDontSee('Hidden Draft Unit');
        $this->get('/listings')->assertOk()->assertSee('Sunny Apartment');
        $this->get("/listings/{$published->id}")->assertOk()->assertSee('Cash to seller');
        $hidden = Listing::where('title', 'Hidden Draft Unit')->first();
        $this->get("/listings/{$hidden->id}")->assertNotFound();
    }

    public function test_public_calculator_and_lead_submission(): void
    {
        $this->post('/evaluate/calculate', ['total_price' => 10_000_000, 'paid_amount' => 2_000_000])
            ->assertOk()->assertSee('1,500,000');
        $this->post('/evaluate', ['name' => 'Lead', 'phone' => '01000000000', 'total_price' => 5000000, 'paid_amount' => 1000000])
            ->assertRedirect('/evaluate');
        $this->assertDatabaseHas('evaluations', ['phone' => '01000000000']);
    }

    public function test_admin_can_create_listing_for_seller_and_export_csv(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $seller = User::factory()->create();

        $this->actingAs($admin)->post('/admin/listings', [
            'seller_id' => $seller->id, 'title' => 'Admin Created Unit', 'unit_type' => 'apartment', 'city' => 'New Cairo',
            'area_sqm' => 120, 'total_price' => 6000000, 'paid_amount' => 1200000, 'status' => 'draft',
        ])->assertRedirect();
        $this->assertDatabaseHas('listings', ['title' => 'Admin Created Unit', 'seller_id' => $seller->id]);

        $this->actingAs($admin)->get('/admin/listings/export')->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $this->actingAs($manager)->get('/admin/listings/export')->assertForbidden();
    }

    public function test_inquiry_thread_is_private_and_staff_can_reply(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $stranger = User::factory()->create();
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $listing = Listing::factory()->create(['seller_id' => $seller->id]);
        $interest = Interest::factory()->create(['listing_id' => $listing->id, 'buyer_id' => $buyer->id]);

        $this->actingAs($buyer, 'sanctum')->postJson("/api/v1/interests/{$interest->id}/messages", ['body' => 'Is the unit available?'])->assertCreated();
        $this->actingAs($stranger, 'sanctum')->getJson("/api/v1/interests/{$interest->id}/messages")->assertForbidden();
        $this->actingAs($seller, 'sanctum')->getJson("/api/v1/interests/{$interest->id}/messages")->assertOk()->assertJsonCount(1, 'data');
        $this->actingAs($manager)->post("/admin/interests/{$interest->id}/reply", ['body' => 'Yes, scheduling a call.'])->assertRedirect();
        $this->assertDatabaseHas('interest_messages', ['interest_id' => $interest->id, 'sender_role' => 'staff']);
        $this->assertSame(ListingStatus::Published, $listing->fresh()->status);
    }
}
