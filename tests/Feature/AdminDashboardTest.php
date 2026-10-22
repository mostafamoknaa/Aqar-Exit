<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Enums\UserRole;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_normal_users_cannot_access_admin(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create(['role' => UserRole::User]))->get('/admin')->assertForbidden();
    }

    public function test_admin_can_login_and_verify_a_listing(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'email' => 'a@test.com']);
        $listing = Listing::factory()->pending()->create();

        $this->post('/admin/login', ['email' => 'a@test.com', 'password' => 'password'])->assertRedirect('/admin');
        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Verification Queue');
        $this->actingAs($admin)->patch("/admin/listings/{$listing->id}/status", ['status' => 'published'])->assertRedirect();
        $this->assertSame(ListingStatus::Published, $listing->fresh()->status);
    }
}
