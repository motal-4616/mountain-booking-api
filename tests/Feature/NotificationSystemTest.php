<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tour;
use App\Models\Schedule;
use App\Models\Booking;
use App\Models\Review;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

class NotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $bookingManager;
    protected User $contentManager;
    protected User $user;
    protected Tour $tour;
    protected Schedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->bookingManager = User::factory()->create(['role' => 'booking_manager']);
        $this->contentManager = User::factory()->create(['role' => 'content_manager']);
        $this->user = User::factory()->create(['role' => 'user']);
        
        $this->tour = Tour::create([
            'name' => 'Test Tour',
            'description' => 'Test',
            'location' => 'Test',
            'difficulty' => 'medium',
            'price' => 2000000,
            'image' => 'test.jpg',
            'is_active' => true,
        ]);
        
        $this->schedule = Schedule::create([
            'tour_id' => $this->tour->id,
            'departure_date' => now()->addDays(10),
            'max_people' => 20,
            'available_slots' => 20,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_notification_service_can_get_super_admins()
    {
        $service = new NotificationService();
        
        // Use reflection to access protected method
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getSuperAdmins');
        $method->setAccessible(true);
        
        $superAdmins = $method->invoke($service);
        
        $this->assertCount(1, $superAdmins);
        $this->assertEquals('super_admin', $superAdmins->first()->role);
    }

    /** @test */
    public function test_notification_service_can_get_booking_managers()
    {
        $service = new NotificationService();
        
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getBookingManagers');
        $method->setAccessible(true);
        
        $managers = $method->invoke($service);
        
        $this->assertCount(1, $managers);
        $this->assertEquals('booking_manager', $managers->first()->role);
    }

    /** @test */
    public function test_notification_service_can_get_content_managers()
    {
        $service = new NotificationService();
        
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getContentManagers');
        $method->setAccessible(true);
        
        $managers = $method->invoke($service);
        
        $this->assertCount(1, $managers);
        $this->assertEquals('content_manager', $managers->first()->role);
    }

    /** @test */
    public function test_user_can_access_notifications_page()
    {
        $response = $this->actingAs($this->user)->get('/notifications');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_guest_cannot_access_notifications()
    {
        $response = $this->get('/notifications');
        $response->assertRedirect('/login');
    }
}
