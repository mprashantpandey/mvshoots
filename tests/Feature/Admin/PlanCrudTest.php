<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use App\Models\City;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_a_plan(): void
    {
        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@vmshoot.test',
            'password' => bcrypt('password'),
        ]);

        $category = Category::create([
            'name' => 'Wedding',
            'description' => 'Wedding shoots',
            'status' => 'active',
        ]);
        $city = City::create([
            'name' => 'Mumbai',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $this->actingAs($admin, 'admin');

        $createResponse = $this->post(route('admin.plans.store'), [
            'category_id' => $category->id,
            'title' => 'Classic Wedding',
            'description' => 'Essential coverage package',
            'price' => 14999,
            'duration' => '4 hours',
            'inclusions' => "80 edited photos\n1 teaser reel",
            'status' => 'active',
            'city_ids' => [$city->id],
        ]);

        $createResponse->assertRedirect(route('admin.plans.index'));

        $plan = Plan::firstOrFail();

        $this->assertSame('Classic Wedding', $plan->title);
        $this->assertSame('active', $plan->status);
        $this->assertSame(['80 edited photos', '1 teaser reel'], $plan->inclusions);
        $this->assertSame([$city->id], $plan->cities()->pluck('cities.id')->all());

        $updateResponse = $this->put(route('admin.plans.update', $plan), [
            'category_id' => $category->id,
            'title' => 'Classic Wedding Plus',
            'description' => 'Expanded wedding coverage package',
            'price' => 18999,
            'duration' => '6 hours',
            'inclusions' => "120 edited photos\n2 teaser reels",
            'status' => 'inactive',
            'city_ids' => [],
        ]);

        $updateResponse->assertRedirect(route('admin.plans.index'));

        $plan->refresh();

        $this->assertSame('Classic Wedding Plus', $plan->title);
        $this->assertSame('inactive', $plan->status);
        $this->assertSame(['120 edited photos', '2 teaser reels'], $plan->inclusions);

        $deleteResponse = $this->delete(route('admin.plans.destroy', $plan));

        $deleteResponse->assertRedirect(route('admin.plans.index'));
        $this->assertDatabaseMissing('plans', [
            'id' => $plan->id,
        ]);
    }

    public function test_guest_cannot_access_plan_management(): void
    {
        $this->get(route('admin.plans.index'))
            ->assertRedirect(route('admin.login'));
    }
}
