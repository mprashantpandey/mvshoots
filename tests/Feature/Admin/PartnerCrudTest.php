<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\BookingResult;
use App\Models\Category;
use App\Models\City;
use App\Models\Partner;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_a_partner(): void
    {
        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@vmshoot.test',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        $city = City::create([
            'name' => 'Partner City',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $createResponse = $this->post(route('admin.partners.store'), [
            'name' => 'Lens Crew',
            'phone' => '8888882222',
            'email' => 'partner@example.com',
            'status' => 'active',
            'city_id' => $city->id,
            'service_city_ids' => [$city->id],
        ]);

        $createResponse->assertRedirect(route('admin.partners.index'));

        $partner = Partner::firstOrFail();

        $this->assertSame('Lens Crew', $partner->name);
        $this->assertDatabaseHas('city_partner', [
            'partner_id' => $partner->id,
            'city_id' => $city->id,
        ]);

        $updateResponse = $this->put(route('admin.partners.update', $partner), [
            'name' => 'Lens Crew Plus',
            'phone' => '8888882222',
            'email' => 'partner-plus@example.com',
            'status' => 'inactive',
            'city_id' => $city->id,
            'service_city_ids' => [$city->id],
        ]);

        $updateResponse->assertRedirect(route('admin.partners.index'));

        $partner->refresh();

        $this->assertSame('Lens Crew Plus', $partner->name);
        $this->assertSame('inactive', $partner->status);

        $deleteResponse = $this->delete(route('admin.partners.destroy', $partner));

        $deleteResponse->assertRedirect(route('admin.partners.index'));
        $this->assertDatabaseMissing('partners', [
            'id' => $partner->id,
        ]);
    }

    public function test_admin_can_open_partner_index_create_show_and_edit_pages(): void
    {
        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@vmshoot.test',
            'password' => bcrypt('password'),
        ]);

        $partner = Partner::create([
            'name' => 'Lens Crew',
            'phone' => '8888882222',
            'email' => 'partner@example.com',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Aarav',
            'phone' => '9999992222',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Wedding',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Signature',
            'price' => 35000,
            'duration' => '6 hours',
            'inclusions' => ['80 edited photos'],
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'plan_id' => $plan->id,
            'assigned_partner_id' => $partner->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00',
            'address' => 'Mumbai',
            'status' => 'assigned',
            'total_amount' => 35000,
            'advance_amount' => 7000,
            'final_amount' => 28000,
            'advance_paid' => true,
            'final_paid' => false,
        ]);

        BookingResult::create([
            'booking_id' => $booking->id,
            'uploaded_by_partner_id' => $partner->id,
            'file_type' => 'photo',
            'file_url' => 'https://example.com/result.jpg',
            'notes' => 'Pending final upload',
        ]);

        $this->actingAs($admin, 'admin');

        $this->get(route('admin.partners.index'))->assertOk();
        $this->get(route('admin.partners.create'))->assertOk();
        $this->get(route('admin.partners.show', $partner))->assertOk();
        $this->get(route('admin.partners.edit', $partner))->assertOk();
    }
}
