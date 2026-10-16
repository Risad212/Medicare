<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectoryModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_module_hides_directory_routes_but_keeps_core(): void
    {
        config(['modules.directory' => false]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/services')->assertNotFound();
        $this->actingAs($admin)->get('/admin/departments')->assertNotFound();
        $this->actingAs($admin)->get('/admin/sliders')->assertNotFound();
        $this->actingAs($admin)->get('/admin/settings/service')->assertNotFound();
        $this->actingAs($admin)->get('/admin/settings/doctor')->assertNotFound();
        $this->actingAs($admin)->get('/admin/settings/blog')->assertNotFound();
        $this->actingAs($admin)->get('/admin/settings/contact')->assertNotFound();
        $this->actingAs($admin)->get('/admin/settings/appointment')->assertNotFound();
        $this->get('/service')->assertNotFound();

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->get('/')->assertOk()->assertDontSee('service-section', false);
    }

    public function test_enabled_module_serves_service_pages(): void
    {
        Service::create([
            'title' => 'Cardiology', 'slug' => 'cardiology-'.uniqid(), 'status' => 1, 'order' => 1,
        ]);

        $this->get('/service')->assertOk()->assertSee('Cardiology');
    }
}
