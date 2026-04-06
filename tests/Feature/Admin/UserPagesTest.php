<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_user_index_and_detail_pages(): void
    {
        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@vmshoot.test',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'Riya',
            'phone' => '9999991111',
            'email' => 'riya@example.com',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Wedding',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Gold',
            'price' => 25000,
            'duration' => '5 hours',
            'inclusions' => ['60 edited photos'],
            'status' => 'active',
        ]);

        Booking::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'plan_id' => $plan->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '11:00',
            'address' => 'Delhi',
            'status' => 'pending',
            'total_amount' => 25000,
            'advance_amount' => 5000,
            'final_amount' => 20000,
            'advance_paid' => false,
            'final_paid' => false,
        ]);

        $this->actingAs($admin, 'admin');

        $this->get(route('admin.users.index'))
            ->assertOk();

        $this->get(route('admin.users.show', $user))
            ->assertOk();
    }
}
