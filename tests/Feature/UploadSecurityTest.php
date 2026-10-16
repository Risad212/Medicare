<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UploadSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_svg_logo_upload_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $svg = UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml');

        $this->actingAs($admin)->post(route('settings.general.update'), [
            'logo' => $svg,
        ])->assertSessionHasErrors('logo');
    }

    public function test_svg_service_icon_upload_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $svg = UploadedFile::fake()->create('icon.svg', 10, 'image/svg+xml');

        $this->actingAs($admin)->post(route('admin.services.store'), [
            'title' => 'XSS Service',
            'icon' => $svg,
        ])->assertSessionHasErrors('icon');
    }

    public function test_raster_logo_upload_still_accepted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $png = UploadedFile::fake()->image('logo.png');

        $this->actingAs($admin)->post(route('settings.general.update'), [
            'logo' => $png,
        ])->assertSessionHasNoErrors();
    }

    public function test_javascript_map_embed_url_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('settings.general.update'), [
            'map_embed_url' => 'javascript:alert(document.cookie)',
        ])->assertSessionHasErrors('map_embed_url');

        $this->actingAs($admin)->post(route('settings.general.update'), [
            'map_embed_url' => 'https://evil.example.com/embed',
        ])->assertSessionHasErrors('map_embed_url');
    }

    public function test_google_maps_embed_url_is_accepted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('settings.general.update'), [
            'map_embed_url' => 'https://www.google.com/maps/embed?pb=test',
        ])->assertSessionHasNoErrors();
    }
}
