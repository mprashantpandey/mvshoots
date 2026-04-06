<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_send_notifications(): void
    {
        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@vmshoot.test',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'Tara',
            'phone' => '9000000003',
            'status' => 'active',
        ]);

        AppNotification::create([
            'user_type' => 'user',
            'user_id' => $user->id,
            'title' => 'Existing Notification',
            'body' => 'Already sent',
            'type' => 'manual_notification',
            'is_read' => false,
        ]);

        $this->actingAs($admin, 'admin');

        $this->get(route('admin.notifications.index', ['is_read' => '0']))
            ->assertOk();

        $response = $this->post(route('admin.notifications.store'), [
            'user_type' => 'user',
            'user_id' => $user->id,
            'title' => 'Payment Reminder',
            'body' => 'Please complete your final payment.',
            'type' => 'manual_notification',
            'reference_id' => null,
        ]);

        $response->assertRedirect(route('admin.notifications.index'));

        $this->assertDatabaseHas('notifications', [
            'title' => 'Payment Reminder',
            'user_id' => $user->id,
        ]);

        $notification = AppNotification::where('title', 'Existing Notification')->firstOrFail();

        $this->post(route('admin.notifications.update-read-state', $notification), [
            'is_read' => true,
        ])->assertRedirect();

        $notification->refresh();

        $this->assertTrue($notification->is_read);
    }
}
