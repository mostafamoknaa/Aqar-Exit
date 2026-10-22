<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Listing;
use App\Models\PhoneOtp;
use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContinuationPhasesTest extends TestCase
{
    use RefreshDatabase;

    public function test_otp_is_hashed_single_use_and_logs_user_in(): void
    {
        $user = User::factory()->create(['phone' => '01099998888']);
        PhoneOtp::create(['phone' => $user->phone, 'purpose' => 'login', 'code_hash' => Hash::make('123456'), 'expires_at' => now()->addMinutes(10)]);

        $this->postJson('/api/v1/auth/otp/login', ['phone' => $user->phone, 'code' => '000000'])->assertUnprocessable();
        $this->postJson('/api/v1/auth/otp/login', ['phone' => $user->phone, 'code' => '123456'])->assertOk()->assertJsonStructure(['data' => ['token']]);
        // Single use: replay fails
        $this->postJson('/api/v1/auth/otp/login', ['phone' => $user->phone, 'code' => '123456'])->assertUnprocessable();
        $this->assertNotNull($user->fresh()->phone_verified_at);
        $this->assertDatabaseMissing('phone_otps', ['code_hash' => '123456']); // hashed, never plaintext
    }

    public function test_otp_send_never_returns_code_and_respects_cooldown(): void
    {
        $this->postJson('/api/v1/auth/otp/send', ['phone' => '01011112222'])->assertOk()->assertJsonMissingPath('data.code');
        $this->postJson('/api/v1/auth/otp/send', ['phone' => '01011112222'])->assertUnprocessable();
        $this->assertTrue(Hash::check(
            // code unknown to client; just prove stored value is a hash
            '000000', PhoneOtp::first()->code_hash) === false && str_starts_with(PhoneOtp::first()->code_hash, '$2y$'));
    }

    public function test_map_endpoint_returns_only_geocoded_published_pins(): void
    {
        Listing::factory()->create(['latitude' => 30.01, 'longitude' => 31.02]);
        Listing::factory()->create(['latitude' => null, 'longitude' => null]);
        $this->getJson('/api/v1/listings/map')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.latitude', 30.01);
    }

    public function test_document_replace_resets_review_and_keeps_history(): void
    {
        Storage::fake('local');
        $seller = User::factory()->create();
        $listing = Listing::factory()->create(['seller_id' => $seller->id]);
        Storage::disk('local')->put('docs/old.pdf', 'old');
        $doc = $listing->documents()->create(['uploaded_by' => $seller->id, 'type' => 'contract', 'path' => 'docs/old.pdf', 'status' => 'rejected']);

        $this->actingAs($seller, 'sanctum')->postJson("/api/v1/my-listings/{$listing->id}/documents/{$doc->id}/replace", [
            'file' => UploadedFile::fake()->create('new.pdf', 10, 'application/pdf'),
        ])->assertOk()->assertJsonPath('data.status', 'pending');
        $this->assertDatabaseHas('document_reviews', ['listing_document_id' => $doc->id, 'action' => 'replaced']);
    }

    public function test_matching_command_notifies_saved_search_owners(): void
    {
        $buyer = User::factory()->create();
        SavedSearch::create(['user_id' => $buyer->id, 'name' => 'Cairo', 'criteria' => ['city' => 'New Cairo'], 'notify_on_match' => true]);
        Listing::factory()->create(['city' => 'New Cairo', 'published_at' => now()]);

        $this->artisan('aqar:notify-matching')->assertSuccessful();
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $buyer->id]);
    }

    public function test_security_headers_are_present(): void
    {
        $this->getJson('/api/v1/branding')->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_arabic_admin_uses_rtl_and_translated_nav(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'locale' => 'ar']);
        $this->actingAs($admin)->get('/admin')
            ->assertOk()->assertSee('dir="rtl"', false)->assertSee('لوحة التحكم')->assertSee('الإعدادات');
    }

    public function test_notification_preferences_toggle(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum')->putJson('/api/v1/notifications/preferences', ['notify_push' => false, 'locale' => 'ar'])
            ->assertOk()->assertJsonPath('data.notify_push', false)->assertJsonPath('data.locale', 'ar');
    }
}
