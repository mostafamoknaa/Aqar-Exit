<?php

namespace Tests\Feature;

use App\Enums\DealStage;
use App\Enums\ListingStatus;
use App\Enums\UserRole;
use App\Models\Deal;
use App\Models\Listing;
use App\Models\Permission;
use App\Models\Setting;
use App\Models\User;
use App\Services\CaseWorkflow;
use App\Services\DealTransitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class HardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_twelve_step_workflow_mapping_and_listing_transitions(): void
    {
        $this->assertCount(12, CaseWorkflow::STEPS);
        $this->assertSame(3, CaseWorkflow::currentStep('under_review', null));
        $this->assertSame(8, CaseWorkflow::currentStep('published', 'negotiation'));
        $this->assertSame(10, CaseWorkflow::currentStep('published', 'completed'));

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $listing = Listing::factory()->pending()->create(); // submitted (step 2)

        // Invalid jump: submitted -> completed is rejected with validation errors
        $this->actingAs($admin)->patch("/admin/listings/{$listing->id}/status", ['status' => 'completed'])->assertSessionHasErrors('status');
        // Valid: submitted -> under_review -> published
        $this->actingAs($admin)->patch("/admin/listings/{$listing->id}/status", ['status' => 'under_review'])->assertRedirect();
        $this->actingAs($admin)->patch("/admin/listings/{$listing->id}/status", ['status' => 'published'])->assertRedirect();
        $this->assertSame(ListingStatus::Published, $listing->fresh()->status);
    }

    public function test_seller_resubmits_as_documents_resubmitted_after_changes_requested(): void
    {
        $seller = User::factory()->create();
        $listing = Listing::factory()->create(['seller_id' => $seller->id, 'status' => ListingStatus::ChangesRequested]);
        $listing->documents()->create(['uploaded_by' => $seller->id, 'type' => 'contract', 'path' => 'x.pdf']);

        $this->actingAs($seller, 'sanctum')->postJson("/api/v1/my-listings/{$listing->id}/submit")->assertOk();
        $this->assertSame(ListingStatus::DocumentsResubmitted, $listing->fresh()->status);
    }

    public function test_deal_negotiation_stage_is_in_pipeline(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $deal = Deal::factory()->create(['case_manager_id' => $manager->id, 'stage' => DealStage::NewLead]);
        app(DealTransitionService::class)->transition($deal, DealStage::Negotiation, $manager, 'Discussing cash schedule');
        $this->assertSame(DealStage::Negotiation, $deal->fresh()->stage);
        $this->assertSame(8, CaseWorkflow::currentStep('published', $deal->stage->value));
    }

    public function test_permission_matrix_is_editable_and_enforced(): void
    {
        Permission::syncFromConfig();
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $this->assertTrue($manager->can('listings.moderate'));

        // Admin removes listings.moderate from managers (keeps it for admin)
        $grants = [];
        foreach (Permission::all() as $p) {
            $grants[$p->name] = Permission::rolesFor($p->name);
        }
        $grants['listings.moderate'] = ['admin'];
        $this->actingAs($admin)->put('/admin/permissions', ['permissions' => $grants])->assertRedirect();

        $this->assertFalse($manager->fresh()->can('listings.moderate'));
        $this->assertTrue($admin->fresh()->can('listings.moderate'));
        // Admin can never lock itself out of recovery permissions
        $this->assertTrue($admin->fresh()->can('permissions.manage'));

        // Enforcement: manager can no longer moderate
        $listing = Listing::factory()->pending()->create();
        $this->actingAs($manager)->patch("/admin/listings/{$listing->id}/status", ['status' => 'under_review'])->assertForbidden();
        // And managers cannot open the matrix itself
        $this->actingAs($manager)->get('/admin/permissions')->assertForbidden();
    }

    public function test_signed_document_link_works_and_tampering_fails(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('docs/secret.pdf', 'secret');
        $seller = User::factory()->create();
        $listing = Listing::factory()->create(['seller_id' => $seller->id]);
        $doc = $listing->documents()->create(['uploaded_by' => $seller->id, 'type' => 'contract', 'path' => 'docs/secret.pdf', 'original_name' => 'secret.pdf']);

        $url = URL::temporarySignedRoute('documents.signed-download', now()->addMinutes(5), ['document' => $doc->id]);
        $this->get($url)->assertOk(); // no login needed — signature is the credential
        $tampered = $this->get(str_replace("/documents/{$doc->id}/", '/documents/99999/', $url));
        $this->assertContains($tampered->getStatusCode(), [403, 404]); // tampering never grants access

        // API generation is owner-only
        $other = User::factory()->create();
        $this->actingAs($other, 'sanctum')->postJson("/api/v1/my-listings/{$listing->id}/documents/{$doc->id}/signed-url")->assertForbidden();
        $this->actingAs($seller, 'sanctum')->postJson("/api/v1/my-listings/{$listing->id}/documents/{$doc->id}/signed-url")
            ->assertOk()->assertJsonStructure(['data' => ['url', 'expires_at']]);
    }

    public function test_setting_cache_stores_plain_data_not_a_model(): void
    {
        Setting::put('site_name', 'Cached Name');
        // First read populates the cache, second read comes from it — neither may
        // try to unserialize an Eloquent model ("incomplete object" regression).
        $this->assertSame('Cached Name', Setting::get('site_name'));
        $this->assertSame('Cached Name', Setting::get('site_name'));
        $cached = Cache::get('setting.v2.site_name');
        $this->assertIsArray($cached);
        $this->assertSame('Cached Name', $cached['value']);
    }

    public function test_arabic_admin_translations_render(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'locale' => 'ar']);
        $this->actingAs($admin)->get('/admin/listings')->assertOk()
            ->assertSee('بحث')->assertSee('الصلاحيات')->assertSee('dir="rtl"', false);
    }
}
