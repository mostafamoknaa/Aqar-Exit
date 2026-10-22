<?php

namespace Tests\Feature;

use App\Enums\DealStage;
use App\Enums\UserRole;
use App\Models\Deal;
use App\Models\Listing;
use App\Models\Setting;
use App\Models\User;
use App\Services\DealTransitionService;
use App\Services\FinancialsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WorkflowAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_deal_transition_is_rejected_and_valid_one_records_event_and_notifies(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $deal = Deal::factory()->create(['case_manager_id' => $manager->id, 'stage' => DealStage::NewLead]);
        $service = app(DealTransitionService::class);

        try {
            $service->transition($deal, DealStage::Signing, $manager);
            $this->fail('Expected ValidationException');
        } catch (ValidationException) {
            $this->assertDatabaseCount('deal_events', 0);
        }

        $service->transition($deal, DealStage::Contacted, $manager, 'Called buyer');
        $this->assertDatabaseHas('deal_events', ['deal_id' => $deal->id, 'from_stage' => 'new', 'to_stage' => 'contacted']);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $deal->buyer_id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'deal.stage_changed', 'auditable_id' => $deal->id]);
    }

    public function test_financial_verification_flags_discrepancy_without_overwriting_reported_figures(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $listing = Listing::factory()->create(['total_price' => 5_000_000, 'paid_amount' => 1_000_000]);

        app(FinancialsService::class)->verify($listing, '5000000.00', '900000.00', $admin, 'Receipt total differs');

        $listing->refresh();
        $this->assertSame('discrepancy', $listing->financial_status);
        $this->assertSame('1000000.00', (string) $listing->paid_amount);
    }

    public function test_admin_can_update_site_name_and_logo_setting_via_dashboard(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin)->put('/admin/settings', [
            'site_name' => 'My Real Estate', 'site_name_ar' => 'عقاري', 'buyer_commission_rate' => 0.02,
            'default_cancellation_percent' => 10, 'default_locale' => 'ar', 'registration_enabled' => 1,
        ])->assertRedirect();

        $this->assertSame('My Real Estate', Setting::siteName());
        $this->assertEquals(0.02, Setting::buyerCommissionRate());
        $this->getJson('/api/v1/branding')->assertOk()->assertJsonPath('data.site_name', 'My Real Estate')->assertJsonPath('data.buyer_commission_rate', 0.02);
    }

    public function test_saved_search_matching_and_notifications_endpoint(): void
    {
        $buyer = User::factory()->create();
        $token = $buyer->createToken('test')->plainTextToken;
        $this->withToken($token)->postJson('/api/v1/saved-searches', [
            'name' => 'New Cairo under 2M cash', 'criteria' => ['city' => 'New Cairo', 'max_paid' => 2_000_000],
        ])->assertCreated();

        $listing = Listing::factory()->make(['city' => 'New Cairo', 'paid_amount' => 1_500_000]);
        $this->assertTrue($buyer->savedSearches()->first()->matches($listing));
        $listing->paid_amount = 3_000_000;
        $this->assertFalse($buyer->savedSearches()->first()->matches($listing));

        $this->withToken($token)->getJson('/api/v1/notifications')->assertOk()->assertJsonPath('meta.unread_count', 0);
    }

    public function test_non_admin_cannot_view_audit_logs_or_settings(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $this->actingAs($manager)->get('/admin/audit-logs')->assertForbidden();
        $this->actingAs($manager)->get('/admin/settings')->assertOk(); // visible page, update is admin-only
        $this->actingAs($manager)->put('/admin/settings', ['site_name' => 'Hack', 'buyer_commission_rate' => 0.01, 'default_cancellation_percent' => 15, 'default_locale' => 'en'])->assertForbidden();
    }

    public function test_private_document_download_requires_owner(): void
    {
        $seller = User::factory()->create();
        $other = User::factory()->create();
        $listing = Listing::factory()->create(['seller_id' => $seller->id]);
        Storage::disk('local')->put('docs/test.pdf', 'pdf-content');
        $doc = $listing->documents()->create(['uploaded_by' => $seller->id, 'type' => 'contract', 'path' => 'docs/test.pdf', 'original_name' => 'test.pdf']);

        $this->actingAs($other, 'sanctum')->get("/api/v1/my-listings/{$listing->id}/documents/{$doc->id}/download")->assertForbidden();
        $this->actingAs($seller, 'sanctum')->get("/api/v1/my-listings/{$listing->id}/documents/{$doc->id}/download")->assertOk();
    }
}
