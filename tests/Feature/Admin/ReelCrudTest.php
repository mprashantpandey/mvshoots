<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Reel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReelCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_a_reel(): void
    {
        Storage::fake('public');

        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@vmshoot.test',
            'password' => bcrypt('password'),
        ]);

        $category = Category::create([
            'name' => 'Pre Wedding',
            'description' => 'Pre wedding shoots',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'admin');

        $createResponse = $this->post(route('admin.reels.store'), [
            'title' => 'Cinematic Save The Date',
            'video_file' => UploadedFile::fake()->create('reel.mp4', 1024, 'video/mp4'),
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg'),
            'category_id' => $category->id,
            'status' => 'active',
        ]);

        $createResponse->assertRedirect(route('admin.reels.index'));

        $reel = Reel::firstOrFail();

        $this->assertSame('Cinematic Save The Date', $reel->title);
        $this->assertSame('active', $reel->status);
        $this->assertNotNull($reel->thumbnail);
        $this->assertNotNull($reel->video_url);
        Storage::disk('public')->assertExists($reel->thumbnail);
        Storage::disk('public')->assertExists($reel->video_url);

        $updateResponse = $this->put(route('admin.reels.update', $reel), [
            'title' => 'Cinematic Save The Date Plus',
            'video_url' => 'https://example.com/reels/save-the-date-plus.mp4',
            'category_id' => $category->id,
            'status' => 'inactive',
        ]);

        $updateResponse->assertRedirect(route('admin.reels.index'));

        $reel->refresh();

        $this->assertSame('Cinematic Save The Date Plus', $reel->title);
        $this->assertSame('inactive', $reel->status);
        $this->assertSame('https://example.com/reels/save-the-date-plus.mp4', $reel->video_url);

        $deleteResponse = $this->delete(route('admin.reels.destroy', $reel));

        $deleteResponse->assertRedirect(route('admin.reels.index'));
        $this->assertDatabaseMissing('reels', [
            'id' => $reel->id,
        ]);
    }

    public function test_guest_cannot_access_reel_management(): void
    {
        $this->get(route('admin.reels.index'))
            ->assertRedirect(route('admin.login'));
    }
}
