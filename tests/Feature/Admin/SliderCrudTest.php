<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Slider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SliderCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_a_slider(): void
    {
        Storage::fake('public');

        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@vmshoot.test',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        $createResponse = $this->post(route('admin.sliders.store'), [
            'title' => 'Homepage Banner',
            'subtitle' => 'Book premium shoots fast',
            'image' => UploadedFile::fake()->image('slider.jpg'),
            'app_target' => 'user',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        $createResponse->assertRedirect(route('admin.sliders.index'));

        $slider = Slider::firstOrFail();

        $this->assertSame('Homepage Banner', $slider->title);
        $this->assertSame('user', $slider->app_target);
        $this->assertNotNull($slider->image);
        Storage::disk('public')->assertExists($slider->image);

        $updateResponse = $this->put(route('admin.sliders.update', $slider), [
            'title' => 'Updated Banner',
            'subtitle' => 'Now live for both apps',
            'app_target' => 'both',
            'sort_order' => 3,
            'status' => 'inactive',
        ]);

        $updateResponse->assertRedirect(route('admin.sliders.index'));

        $slider->refresh();

        $this->assertSame('Updated Banner', $slider->title);
        $this->assertSame('both', $slider->app_target);
        $this->assertSame(3, $slider->sort_order);
        $this->assertSame('inactive', $slider->status);

        $deleteResponse = $this->delete(route('admin.sliders.destroy', $slider));

        $deleteResponse->assertRedirect(route('admin.sliders.index'));
        $this->assertDatabaseMissing('sliders', [
            'id' => $slider->id,
        ]);
    }

    public function test_guest_cannot_access_slider_management(): void
    {
        $this->get(route('admin.sliders.index'))
            ->assertRedirect(route('admin.login'));
    }
}
