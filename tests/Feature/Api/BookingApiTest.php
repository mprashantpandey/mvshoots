<?php

namespace Tests\Feature\Api;

use App\Models\Admin;
use App\Models\AppNotification;
use App\Models\Category;
use App\Models\Owner;
use App\Models\Partner;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_booking_without_passing_user_id(): void
    {
        $user = User::create([
            'name' => 'Booking User',
            'phone' => '+911111111111',
            'email' => 'booking.user@example.com',
            'firebase_uid' => 'firebase-booking-user',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Wedding',
            'description' => 'Wedding shoots',
            'image' => 'categories/wedding.jpg',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Premium Wedding',
            'description' => 'Premium package',
            'price' => 10000,
            'duration' => '6 hours',
            'inclusions' => ['Edited photos', 'Highlights reel'],
            'status' => 'active',
        ]);

        $token = $user->createToken('test-user')->plainTextToken;

        $response = $this
            ->withToken($token)
            ->postJson('/api/v1/bookings', [
                'category_id' => $category->id,
                'plan_id' => $plan->id,
                'booking_date' => now()->addWeek()->toDateString(),
                'booking_time' => '10:00',
                'address' => '123 Test Street',
                'notes' => 'Please arrive early',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user_id', $user->id);
    }

    public function test_partner_cannot_create_a_booking(): void
    {
        $partner = Partner::create([
            'name' => 'Partner User',
            'phone' => '+922222222222',
            'email' => 'partner.user@example.com',
            'firebase_uid' => 'firebase-partner-user',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Portrait',
            'description' => 'Portrait shoots',
            'image' => 'categories/portrait.jpg',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Portrait Lite',
            'description' => 'Portrait package',
            'price' => 2500,
            'duration' => '1 hour',
            'inclusions' => ['10 edits'],
            'status' => 'active',
        ]);

        $token = $partner->createToken('test-partner')->plainTextToken;

        $response = $this
            ->withToken($token)
            ->postJson('/api/v1/bookings', [
                'category_id' => $category->id,
                'plan_id' => $plan->id,
                'booking_date' => now()->addWeek()->toDateString(),
                'booking_time' => '14:00',
                'address' => '456 Test Avenue',
            ]);

        $response->assertForbidden();
    }

    public function test_booking_can_move_through_assignment_results_and_final_payment_flow(): void
    {
        Storage::fake('public');

        $user = User::create([
            'name' => 'Lifecycle User',
            'phone' => '+913333333333',
            'email' => 'lifecycle.user@example.com',
            'firebase_uid' => 'firebase-lifecycle-user',
            'status' => 'active',
        ]);

        $owner = Owner::create([
            'name' => 'Lifecycle Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'name' => 'Lifecycle Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $partner = Partner::create([
            'name' => 'Lifecycle Partner',
            'phone' => '+914444444444',
            'email' => 'lifecycle.partner@example.com',
            'firebase_uid' => 'firebase-lifecycle-partner',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Maternity',
            'description' => 'Maternity shoots',
            'image' => 'categories/maternity.jpg',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Maternity Gold',
            'description' => 'Premium maternity package',
            'price' => 8000,
            'duration' => '3 hours',
            'inclusions' => ['25 edited photos', '1 teaser reel'],
            'status' => 'active',
        ]);

        $userToken = $user->createToken('lifecycle-user')->plainTextToken;
        $ownerToken = $owner->createToken('lifecycle-owner')->plainTextToken;
        $partnerToken = $partner->createToken('lifecycle-partner')->plainTextToken;

        $this->app['auth']->forgetGuards();
        $bookingResponse = $this
            ->withToken($userToken)
            ->postJson('/api/v1/bookings', [
                'category_id' => $category->id,
                'plan_id' => $plan->id,
                'booking_date' => now()->addWeek()->toDateString(),
                'booking_time' => '09:30',
                'address' => '789 Lifecycle Road',
                'notes' => 'Ring the bell on arrival',
            ])
            ->assertCreated();

        $bookingId = $bookingResponse->json('data.id');

        $this->app['auth']->forgetGuards();
        $this->withToken($userToken)
            ->postJson('/api/v1/payments/advance', [
                'booking_id' => $bookingId,
                'payment_reference' => 'adv_12345',
            ])
            ->assertOk()
            ->assertJsonPath('data.payment_type', 'advance');

        $this->app['auth']->forgetGuards();
        $this->withToken($ownerToken)
            ->postJson("/api/v1/bookings/{$bookingId}/assign-partner", [
                'partner_id' => $partner->id,
                'remarks' => 'Assigning best nearby partner',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'assigned');

        $this->app['auth']->forgetGuards();
        $this->withToken($partnerToken)
            ->postJson("/api/v1/bookings/{$bookingId}/status", [
                'status' => 'accepted',
                'remarks' => 'Accepted and preparing gear',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'accepted');

        $this->app['auth']->forgetGuards();
        $this->withToken($partnerToken)
            ->postJson("/api/v1/bookings/{$bookingId}/status", [
                'status' => 'in_progress',
                'remarks' => 'Shoot started',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress');

        $this->app['auth']->forgetGuards();
        $uploadResponse = $this->withToken($partnerToken)
            ->post("/api/v1/bookings/{$bookingId}/upload-results", [
                'file' => UploadedFile::fake()->image('photo1.jpg'),
                'file_type' => 'photo',
                'notes' => 'Hero frame',
            ]);

        $uploadResponse
            ->assertOk()
            ->assertJsonPath('data.0.file_type', 'photo');

        $resultPath = str_replace('/storage/', '', parse_url($uploadResponse->json('data.0.file_url'), PHP_URL_PATH) ?: '');
        Storage::disk('public')->assertExists($resultPath);

        $this->app['auth']->forgetGuards();
        $this->withToken($userToken)
            ->postJson('/api/v1/payments/final', [
                'booking_id' => $bookingId,
                'payment_reference' => 'fin_67890',
            ])
            ->assertOk()
            ->assertJsonPath('data.payment_type', 'final');

        $this->app['auth']->forgetGuards();
        $this->withToken($userToken)
            ->getJson("/api/v1/bookings/{$bookingId}")
            ->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.advance_paid', true)
            ->assertJsonPath('data.final_paid', true);

        $this->assertDatabaseHas('notifications', [
            'user_type' => 'admin',
            'user_id' => $admin->id,
            'type' => 'booking_created',
            'reference_id' => $bookingId,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_type' => 'admin',
            'user_id' => $admin->id,
            'type' => 'advance_paid',
            'reference_id' => $bookingId,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_type' => 'admin',
            'user_id' => $admin->id,
            'type' => 'booking_assigned',
            'reference_id' => $bookingId,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_type' => 'admin',
            'user_id' => $admin->id,
            'type' => 'results_uploaded',
            'reference_id' => $bookingId,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_type' => 'admin',
            'user_id' => $admin->id,
            'type' => 'booking_completed',
            'reference_id' => $bookingId,
        ]);

        $this->assertGreaterThanOrEqual(
            5,
            AppNotification::query()
                ->where('user_type', 'admin')
                ->where('user_id', $admin->id)
                ->count()
        );
    }

    public function test_final_payment_requires_uploaded_results(): void
    {
        $user = User::create([
            'name' => 'Payment Gate User',
            'phone' => '+915555555555',
            'email' => 'payment.user@example.com',
            'firebase_uid' => 'firebase-payment-user',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Birthday',
            'description' => 'Birthday shoots',
            'image' => 'categories/birthday.jpg',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Birthday Basic',
            'description' => 'Birthday package',
            'price' => 4000,
            'duration' => '2 hours',
            'inclusions' => ['15 edited photos'],
            'status' => 'active',
        ]);

        $token = $user->createToken('payment-user')->plainTextToken;

        $this->app['auth']->forgetGuards();
        $bookingId = $this
            ->withToken($token)
            ->postJson('/api/v1/bookings', [
                'category_id' => $category->id,
                'plan_id' => $plan->id,
                'booking_date' => now()->addWeek()->toDateString(),
                'booking_time' => '16:00',
                'address' => '101 Payment Street',
            ])
            ->json('data.id');

        $this->app['auth']->forgetGuards();
        $this->withToken($token)
            ->postJson('/api/v1/payments/advance', [
                'booking_id' => $bookingId,
            ])
            ->assertOk();

        $this->app['auth']->forgetGuards();
        $this->withToken($token)
            ->postJson('/api/v1/payments/final', [
                'booking_id' => $bookingId,
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Final payment requires uploaded results.');
    }

    public function test_owner_assignment_and_partner_result_upload_are_visible_to_all_actors(): void
    {
        Storage::fake('public');

        $user = User::create([
            'name' => 'Visibility User',
            'phone' => '+916666666666',
            'email' => 'visibility.user@example.com',
            'firebase_uid' => 'firebase-visibility-user',
            'status' => 'active',
        ]);

        $owner = Owner::create([
            'name' => 'Visibility Owner',
            'email' => 'visibility.owner@example.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $partner = Partner::create([
            'name' => 'Visibility Partner',
            'phone' => '+917777777777',
            'email' => 'visibility.partner@example.com',
            'firebase_uid' => 'firebase-visibility-partner',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Corporate',
            'description' => 'Corporate shoots',
            'image' => 'categories/corporate.jpg',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Corporate Prime',
            'description' => 'Corporate package',
            'price' => 12000,
            'duration' => '4 hours',
            'inclusions' => ['40 edited photos'],
            'status' => 'active',
        ]);

        $userToken = $user->createToken('visibility-user')->plainTextToken;
        $ownerToken = $owner->createToken('visibility-owner')->plainTextToken;
        $partnerToken = $partner->createToken('visibility-partner')->plainTextToken;

        $this->app['auth']->forgetGuards();
        $bookingId = $this
            ->withToken($userToken)
            ->postJson('/api/v1/bookings', [
                'category_id' => $category->id,
                'plan_id' => $plan->id,
                'booking_date' => now()->addDays(10)->toDateString(),
                'booking_time' => '13:00',
                'address' => 'Visibility Street',
                'notes' => 'Reception desk entry',
            ])
            ->assertCreated()
            ->json('data.id');

        $this->app['auth']->forgetGuards();
        $this->withToken($userToken)
            ->postJson('/api/v1/payments/advance', [
                'booking_id' => $bookingId,
                'payment_reference' => 'adv_visibility_001',
            ])
            ->assertOk();

        $this->app['auth']->forgetGuards();
        $this->withToken($ownerToken)
            ->postJson("/api/v1/bookings/{$bookingId}/assign-partner", [
                'partner_id' => $partner->id,
                'remarks' => 'Assigning for visibility check',
            ])
            ->assertOk()
            ->assertJsonPath('data.assigned_partner.id', $partner->id)
            ->assertJsonPath('data.status', 'assigned');

        $this->assertDatabaseHas('notifications', [
            'user_type' => 'partner',
            'user_id' => $partner->id,
            'type' => 'booking_assigned',
            'reference_id' => $bookingId,
        ]);

        $this->app['auth']->forgetGuards();
        $this->withToken($partnerToken)
            ->getJson('/api/v1/bookings')
            ->assertOk()
            ->assertJsonPath('data.data.0.id', $bookingId);

        $this->app['auth']->forgetGuards();
        $this->withToken($partnerToken)
            ->postJson("/api/v1/bookings/{$bookingId}/status", [
                'status' => 'accepted',
                'remarks' => 'Accepted',
            ])
            ->assertOk();

        $this->app['auth']->forgetGuards();
        $this->withToken($partnerToken)
            ->postJson("/api/v1/bookings/{$bookingId}/status", [
                'status' => 'in_progress',
                'remarks' => 'Started shooting',
            ])
            ->assertOk();

        $this->app['auth']->forgetGuards();
        $uploadResponse = $this->withToken($partnerToken)
            ->post("/api/v1/bookings/{$bookingId}/upload-results", [
                'file' => UploadedFile::fake()->image('visibility-proof.jpg'),
                'file_type' => 'photo',
                'notes' => 'Uploaded by partner workflow',
            ]);

        $uploadResponse
            ->assertOk()
            ->assertJsonPath('data.0.file_type', 'photo')
            ->assertJsonPath('data.0.notes', 'Uploaded by partner workflow');

        $uploadedFileUrl = $uploadResponse->json('data.0.file_url');
        $resultPath = str_replace('/storage/', '', parse_url($uploadedFileUrl, PHP_URL_PATH) ?: '');
        Storage::disk('public')->assertExists($resultPath);

        $this->app['auth']->forgetGuards();
        $this->withToken($ownerToken)
            ->getJson("/api/v1/bookings/{$bookingId}")
            ->assertOk()
            ->assertJsonPath('data.assigned_partner.id', $partner->id)
            ->assertJsonPath('data.results.0.file_url', $uploadedFileUrl);

        $this->app['auth']->forgetGuards();
        $this->withToken($userToken)
            ->getJson("/api/v1/bookings/{$bookingId}")
            ->assertOk()
            ->assertJsonPath('data.assigned_partner.id', $partner->id)
            ->assertJsonPath('data.results_locked', true)
            ->assertJsonCount(0, 'data.results');
    }

    public function test_partner_can_collect_final_payment_in_cash_after_uploading_results(): void
    {
        Storage::fake('public');

        $user = User::create([
            'name' => 'Cash User',
            'phone' => '+918888888888',
            'email' => 'cash.user@example.com',
            'firebase_uid' => 'firebase-cash-user',
            'status' => 'active',
        ]);

        $owner = Owner::create([
            'name' => 'Cash Owner',
            'email' => 'cash.owner@example.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $partner = Partner::create([
            'name' => 'Cash Partner',
            'phone' => '+919999999999',
            'email' => 'cash.partner@example.com',
            'firebase_uid' => 'firebase-cash-partner',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Studio',
            'description' => 'Studio shoots',
            'image' => 'categories/studio.jpg',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Studio Pro',
            'description' => 'Studio package',
            'price' => 9000,
            'duration' => '3 hours',
            'inclusions' => ['30 edited photos'],
            'status' => 'active',
        ]);

        $userToken = $user->createToken('cash-user')->plainTextToken;
        $ownerToken = $owner->createToken('cash-owner')->plainTextToken;
        $partnerToken = $partner->createToken('cash-partner')->plainTextToken;

        $this->app['auth']->forgetGuards();
        $bookingId = $this
            ->withToken($userToken)
            ->postJson('/api/v1/bookings', [
                'category_id' => $category->id,
                'plan_id' => $plan->id,
                'booking_date' => now()->addDays(7)->toDateString(),
                'booking_time' => '11:00',
                'address' => 'Cash Street',
            ])
            ->assertCreated()
            ->json('data.id');

        $this->app['auth']->forgetGuards();
        $this->withToken($userToken)
            ->postJson('/api/v1/payments/advance', [
                'booking_id' => $bookingId,
                'payment_reference' => 'adv_cash_001',
            ])
            ->assertOk();

        $this->app['auth']->forgetGuards();
        $this->withToken($ownerToken)
            ->postJson("/api/v1/bookings/{$bookingId}/assign-partner", [
                'partner_id' => $partner->id,
            ])
            ->assertOk();

        $this->app['auth']->forgetGuards();
        $this->withToken($partnerToken)
            ->postJson("/api/v1/bookings/{$bookingId}/status", [
                'status' => 'accepted',
            ])
            ->assertOk();

        $this->app['auth']->forgetGuards();
        $this->withToken($partnerToken)
            ->postJson("/api/v1/bookings/{$bookingId}/status", [
                'status' => 'in_progress',
            ])
            ->assertOk();

        $this->app['auth']->forgetGuards();
        $this->withToken($partnerToken)
            ->post("/api/v1/bookings/{$bookingId}/upload-results", [
                'file' => UploadedFile::fake()->image('cash-proof.jpg'),
                'file_type' => 'photo',
            ])
            ->assertOk();

        $this->app['auth']->forgetGuards();
        $this->withToken($partnerToken)
            ->postJson('/api/v1/payments/final/cash-collect', [
                'booking_id' => $bookingId,
                'payment_reference' => 'cash_collected_on_delivery',
            ])
            ->assertOk()
            ->assertJsonPath('data.payment_type', 'final')
            ->assertJsonPath('data.payment_status', 'paid');

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'final_paid' => true,
            'status' => 'completed',
        ]);

        $this->app['auth']->forgetGuards();
        $this->withToken($userToken)
            ->getJson("/api/v1/bookings/{$bookingId}")
            ->assertOk()
            ->assertJsonPath('data.results_locked', false)
            ->assertJsonCount(1, 'data.results');
    }
}
