<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_super_admin_has_all_permissions()
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->canManageCoupons());
        $this->assertTrue($user->canManageBookings());
        $this->assertTrue($user->canManageContent());
        $this->assertTrue($user->canManageTours());
        $this->assertTrue($user->canManageReviews());
    }

    /** @test */
    public function test_booking_manager_has_correct_permissions()
    {
        $user = User::factory()->create(['role' => 'booking_manager']);

        $this->assertTrue($user->isBookingManager());
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->canManageCoupons());
        $this->assertTrue($user->canManageBookings());
        $this->assertFalse($user->canManageContent());
        $this->assertFalse($user->canManageTours());
        $this->assertFalse($user->canManageReviews());
    }

    /** @test */
    public function test_content_manager_has_correct_permissions()
    {
        $user = User::factory()->create(['role' => 'content_manager']);

        $this->assertTrue($user->isContentManager());
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->canManageCoupons());
        $this->assertFalse($user->canManageBookings());
        $this->assertTrue($user->canManageContent());
        $this->assertTrue($user->canManageTours());
        $this->assertTrue($user->canManageReviews());
    }

    /** @test */
    public function test_regular_user_has_no_admin_permissions()
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->canManageCoupons());
        $this->assertFalse($user->canManageBookings());
        $this->assertFalse($user->canManageContent());
    }

    /** @test */
    public function test_super_admin_can_access_admin_panel()
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'super_admin']);
        
        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_regular_user_cannot_access_admin_panel()
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'user']);
        
        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }
}
