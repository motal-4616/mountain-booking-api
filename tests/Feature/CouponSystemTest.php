<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tour;
use App\Models\Schedule;
use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CouponSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $superAdmin;
    protected Tour $tour;
    protected Schedule $schedule;
    protected Coupon $coupon;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Tạo dữ liệu test
        $this->user = User::factory()->create(['role' => 'user']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        
        $this->tour = Tour::create([
            'name' => 'Test Tour',
            'description' => 'Test Description',
            'location' => 'Test Location',
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
        
        $this->coupon = Coupon::create([
            'code' => 'TEST10',
            'name' => 'Test Coupon',
            'description' => 'Test Description',
            'type' => 'percent',
            'value' => 10,
            'min_order_amount' => 1000000,
            'max_discount' => null,
            'usage_limit' => 100,
            'used_count' => 0,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(1)->toDateString(),
            'is_active' => true,
            'created_by' => $this->superAdmin->id,
        ]);
    }

    /** @test */
    public function test_coupon_can_be_applied_to_valid_order()
    {
        $response = $this->actingAs($this->user)->postJson('/coupon/apply', [
            'code' => 'TEST10',
            'total_amount' => 2000000,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ])
                 ->assertJsonStructure([
                     'data' => ['coupon_code', 'discount_amount', 'final_amount']
                 ]);
    }

    /** @test */
    public function test_invalid_coupon_returns_error()
    {
        $response = $this->actingAs($this->user)->postJson('/coupon/apply', [
            'code' => 'INVALID',
            'total_amount' => 2000000,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_coupon_below_minimum_order_fails()
    {
        $response = $this->actingAs($this->user)->postJson('/coupon/apply', [
            'code' => 'TEST10',
            'total_amount' => 500000,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_super_admin_can_access_coupon_management()
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin/coupons');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_regular_user_cannot_access_coupon_management()
    {
        $response = $this->actingAs($this->user)->get('/admin/coupons');
        $response->assertStatus(403);
    }

    /** @test */
    public function test_coupon_service_calculates_discount_correctly()
    {
        $service = new CouponService();
        
        $result = $service->validateAndGetCoupon('TEST10', 2000000);
        
        $this->assertTrue($result['valid']);
        $this->assertEquals(200000, $result['discount_amount']);
        $this->assertEquals(1800000, $result['final_amount']);
    }
}
