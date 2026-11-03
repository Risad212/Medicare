<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageServingTest extends TestCase
{
    use RefreshDatabase;

    public function test_storage_route_serves_public_disk_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('sliders/slider-1.jpg', 'fake-image-content');

        $this->get('/storage/sliders/slider-1.jpg')
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_storage_route_returns_404_for_missing_and_traversal_paths(): void
    {
        Storage::fake('public');

        $this->get('/storage/missing.jpg')->assertNotFound();
        $this->get('/storage/sliders/../../.env')->assertNotFound();
        $this->get('/storage/sliders/..%2F..%2F.env')->assertNotFound();
    }

    public function test_setup_route_requires_the_configured_token(): void
    {
        config()->set('app.setup_token', null);

        $this->get('/setup/anything')->assertNotFound();

        config()->set('app.setup_token', 'test-setup-token');

        $this->get('/setup/wrong-token')->assertNotFound();
    }

    public function test_setup_route_runs_migration_and_seeder_with_valid_token(): void
    {
        config()->set('app.setup_token', 'test-setup-token');

        $this->get('/setup/test-setup-token')
            ->assertOk()
            ->assertSee('migrate', false)
            ->assertSee('Demo data ready', false);

        $this->assertDatabaseHas('sliders', [
            'title' => 'Your Health, Our Mission',
            'bg_image' => 'sliders/slider-2.jpg',
        ]);
    }
}
