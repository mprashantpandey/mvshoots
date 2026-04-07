<?php

namespace Tests\Feature\Api;

use App\Enums\PartnerKycStatus;
use App\Models\Admin;
use App\Models\Category;
use App\Models\City;
use App\Models\Owner;
use App\Models\Partner;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PartnerKycApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_profile_includes_kyc_and_account_flags(): void
    {
        $partner = Partner::create([
            'name' => 'KYC Partner',
            'phone' => '+911100000001',
            'email' => 'kyc.partner@example.com',
            'status' => 'active',
        ]);

        $token = $partner->createToken('kyc-profile')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/auth/partner/me')
            ->assertOk()
            ->assertJsonPath('data.kyc.status', 'not_submitted')
            ->assertJsonPath('data.account.can_accept_bookings', false);
    }

    public function test_partner_can_submit_kyc_and_owner_cannot_assign_until_verified(): void
    {
        Storage::fake('local');

        $mumbai = City::create(['name' => 'Mumbai', 'status' => 'active', 'sort_order' => 1]);

        $user = User::create([
            'name' => 'Booking User',
            'phone' => '+911100000002',
            'city_id' => $mumbai->id,
            'status' => 'active',
        ]);

        $owner = Owner::create([
            'name' => 'Owner',
            'email' => 'owner.kyc@example.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $partner = Partner::create([
            'name' => 'New Partner',
            'phone' => '+911100000003',
            'city_id' => $mumbai->id,
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Wedding',
            'description' => 'Wedding',
            'image' => 'categories/w.jpg',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Plan',
            'description' => 'Plan',
            'price' => 10000,
            'duration' => '4h',
            'inclusions' => ['Photos'],
            'status' => 'active',
        ]);
        $plan->cities()->sync([$mumbai->id]);

        $partnerToken = $partner->createToken('partner-kyc')->plainTextToken;
        $userToken = $user->createToken('user-kyc')->plainTextToken;
        $ownerToken = $owner->createToken('owner-kyc')->plainTextToken;

        $this->withToken($partnerToken)
            ->post('/api/v1/auth/partner/kyc', [
                'aadhar_number' => '123456789012',
                'pan_number' => 'ABCDE1234F',
                'aadhar_front' => UploadedFile::fake()->image('af.jpg', 400, 400),
                'aadhar_back' => UploadedFile::fake()->image('ab.jpg', 400, 400),
                'pan_image' => UploadedFile::fake()->image('pan.jpg', 400, 400),
                'selfie' => UploadedFile::fake()->image('s.jpg', 400, 400),
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', PartnerKycStatus::Pending->value);

        $this->app['auth']->forgetGuards();
        $bookingId = $this->withToken($userToken)
            ->postJson('/api/v1/bookings', [
                'category_id' => $category->id,
                'plan_id' => $plan->id,
                'booking_date' => now()->addWeek()->toDateString(),
                'booking_time' => '10:00',
                'address' => 'Addr',
            ])
            ->assertCreated()
            ->json('data.id');

        $this->withToken($userToken)
            ->postJson('/api/v1/payments/advance', [
                'booking_id' => $bookingId,
                'payment_reference' => 'adv_kyc',
            ])
            ->assertOk();

        $this->app['auth']->forgetGuards();
        $this->withToken($ownerToken)
            ->postJson("/api/v1/bookings/{$bookingId}/assign-partner", [
                'partner_id' => $partner->id,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['partner_id']);

        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin.kyc@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');
        $this->post(route('admin.partners.kyc.verify', $partner))
            ->assertRedirect();

        $this->app['auth']->forgetGuards();
        $this->withToken($ownerToken)
            ->postJson("/api/v1/bookings/{$bookingId}/assign-partner", [
                'partner_id' => $partner->id,
            ])
            ->assertOk();
    }
}
