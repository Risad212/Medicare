<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Service;
use App\Models\Slider;
use App\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class AdminContentTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    public function test_admin_creates_updates_and_deletes_department(): void
    {
        $this->actingAs($this->makeAdmin())
            ->from('/admin/departments')
            ->post(route('admin.departments.store'), ['name' => 'Cardiology', 'status' => 1])
            ->assertRedirect()
            ->assertSessionHas('success', 'Department added successfully!');

        $this->assertDatabaseHas('departments', ['name' => 'Cardiology', 'status' => 1]);

        $department = Department::where('name', 'Cardiology')->first();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.departments.update', $department->id), ['name' => 'Heart Center'])
            ->assertSessionHas('success', 'Department updated successfully!');

        $this->assertDatabaseHas('departments', ['id' => $department->id, 'name' => 'Heart Center']);

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.departments.destroy', $department->id))
            ->assertSessionHas('success', 'Department deleted successfully!');

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }

    public function test_department_name_must_be_unique(): void
    {
        Department::create(['name' => 'Cardiology', 'description' => 'H', 'status' => 1]);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.departments.store'), ['name' => 'Cardiology'])
            ->assertSessionHasErrors('name');
    }

    public function test_admin_creates_updates_and_deletes_time_slot(): void
    {
        $this->actingAs($this->makeAdmin())
            ->from('/admin/time-slots')
            ->post(route('admin.time-slots.store'), ['time' => '10:00 AM', 'status' => 1])
            ->assertRedirect()
            ->assertSessionHas('success', 'Time slot added successfully!');

        $this->assertDatabaseHas('time_slots', ['time' => '10:00 AM', 'status' => 1]);

        $slot = TimeSlot::where('time', '10:00 AM')->first();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.time-slots.update', $slot->id), ['time' => '10:30 AM'])
            ->assertSessionHas('success', 'Time slot updated successfully!');

        $this->assertDatabaseHas('time_slots', ['id' => $slot->id, 'time' => '10:30 AM']);

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.time-slots.destroy', $slot->id))
            ->assertSessionHas('success', 'Time slot deleted successfully!');

        $this->assertDatabaseMissing('time_slots', ['id' => $slot->id]);
    }

    public function test_time_slot_must_be_unique(): void
    {
        TimeSlot::create(['time' => '10:00 AM', 'status' => 1]);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.time-slots.store'), ['time' => '10:00 AM'])
            ->assertSessionHasErrors('time');
    }

    public function test_admin_creates_updates_and_deletes_service(): void
    {
        Storage::fake('public');

        $this->actingAs($this->makeAdmin())
            ->from('/admin/services')
            ->post(route('admin.services.store'), [
                'title' => 'Heart transplants',
                'description' => 'Card body.',
                'button_text' => 'Read more',
                'order' => 2,
                'status' => 1,
                'icon' => UploadedFile::fake()->image('icon.jpg', 128, 128),
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Service added successfully!');

        $service = Service::where('title', 'Heart transplants')->first();
        $this->assertNotNull($service);
        Storage::disk('public')->assertExists($service->icon);

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.services.update', $service->id), ['title' => 'Heart care'])
            ->assertSessionHas('success', 'Service updated successfully!');

        $this->assertDatabaseHas('services', ['id' => $service->id, 'title' => 'Heart care']);

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.services.destroy', $service->id))
            ->assertSessionHas('success', 'Service deleted successfully!');

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_service_title_is_required(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.services.store'), [])
            ->assertSessionHasErrors('title');
    }

    public function test_service_admin_pages_load(): void
    {
        $service = Service::create(['title' => 'Cardiology Card', 'status' => 1]);

        $client = $this->actingAs($this->makeAdmin());

        $client->get(route('admin.services.index'))->assertOk();
        $client->get(route('admin.services.create'))->assertOk();
        $client->get(route('admin.services.edit', $service->id))->assertOk();
    }

    public function test_admin_creates_updates_and_deletes_slider(): void
    {
        Storage::fake('public');

        $this->actingAs($this->makeAdmin())
            ->from('/admin/sliders')
            ->post(route('admin.sliders.store'), [
                'title' => 'Welcome Banner',
                'description' => 'Best care',
                'button_text' => 'Learn more',
                'bg_image' => UploadedFile::fake()->image('banner.jpg', 1200, 600),
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Slider added successfully!');

        $slider = Slider::where('title', 'Welcome Banner')->first();
        $this->assertNotNull($slider);
        Storage::disk('public')->assertExists($slider->bg_image);

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.sliders.update', $slider->id), ['title' => 'New Banner'])
            ->assertRedirect(route('admin.sliders.index'))
            ->assertSessionHas('success', 'Slider updated successfully!');

        $this->assertDatabaseHas('sliders', ['id' => $slider->id, 'title' => 'New Banner']);

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.sliders.destroy', $slider->id))
            ->assertRedirect(route('admin.sliders.index'))
            ->assertSessionHas('success', 'Slider deleted successfully!');

        $this->assertDatabaseMissing('sliders', ['id' => $slider->id]);
    }

    public function test_slider_title_is_required(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.sliders.store'), [])
            ->assertSessionHasErrors('title');
    }
}
