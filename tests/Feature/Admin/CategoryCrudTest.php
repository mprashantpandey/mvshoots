<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_a_category(): void
    {
        Storage::fake('public');

        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@vmshoot.test',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        $city = City::create([
            'name' => 'Mumbai',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $createResponse = $this->post(route('admin.categories.store'), [
            'name' => 'Wedding',
            'description' => 'Wedding and engagement shoots',
            'status' => 'active',
            'image' => UploadedFile::fake()->image('wedding.jpg'),
            'city_ids' => [$city->id],
        ]);

        $createResponse->assertRedirect(route('admin.categories.index'));

        $category = Category::firstOrFail();

        $this->assertSame('Wedding', $category->name);
        $this->assertSame('active', $category->status);
        $this->assertNotNull($category->image);
        $this->assertSame([$city->id], $category->cities()->pluck('cities.id')->all());
        Storage::disk('public')->assertExists($category->image);

        $updateResponse = $this->put(route('admin.categories.update', $category), [
            'name' => 'Wedding Premium',
            'description' => 'Luxury wedding shoots',
            'status' => 'inactive',
            'city_ids' => [],
        ]);

        $updateResponse->assertRedirect(route('admin.categories.index'));

        $category->refresh();

        $this->assertSame('Wedding Premium', $category->name);
        $this->assertSame('inactive', $category->status);

        $deleteResponse = $this->delete(route('admin.categories.destroy', $category));

        $deleteResponse->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_guest_cannot_access_category_management(): void
    {
        $this->get(route('admin.categories.index'))
            ->assertRedirect(route('admin.login'));
    }
}
