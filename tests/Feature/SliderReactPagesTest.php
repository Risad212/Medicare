<?php

namespace Tests\Feature;

use App\Models\Slider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SliderReactPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_slider_list_and_forms_use_react_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $slider = Slider::create([
            'title' => 'Homepage hero',
            'description' => 'A welcoming message.',
            'button_text' => 'Book today',
        ]);

        $this->actingAs($admin)
            ->get('/admin/sliders')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Sliders/Index')
                ->where('sliders.0.title', 'Homepage hero')
                ->where('sliders.0.buttonText', 'Book today')
            );

        $this->actingAs($admin)
            ->get('/admin/sliders/create')
            ->assertInertia(fn ($page) => $page->component('Admin/Sliders/Create'));

        $this->actingAs($admin)
            ->get("/admin/sliders/{$slider->id}/edit")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Sliders/Edit')
                ->where('slider.title', 'Homepage hero')
            );
    }

    public function test_slider_create_update_and_delete_preserve_copy(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->from('/admin/sliders/create')->post('/admin/sliders', [
            'title' => 'New homepage banner',
            'description' => 'Care with clarity.',
            'button_text' => 'Learn more',
        ])->assertRedirect('/admin/sliders/create');

        $slider = Slider::where('title', 'New homepage banner')->firstOrFail();
        $this->assertSame('Care with clarity.', $slider->description);

        $this->actingAs($admin)->from("/admin/sliders/{$slider->id}/edit")->post("/admin/sliders/{$slider->id}", [
            '_method' => 'PUT',
            'title' => 'Updated homepage banner',
            'description' => 'Updated message.',
            'button_text' => 'Contact us',
        ])->assertRedirect('/admin/sliders');

        $this->assertSame('Updated homepage banner', $slider->fresh()->title);
        $this->assertSame('Contact us', $slider->fresh()->button_text);

        $this->actingAs($admin)->delete("/admin/sliders/{$slider->id}")
            ->assertRedirect('/admin/sliders');
        $this->assertDatabaseMissing('sliders', ['id' => $slider->id]);
    }
}
