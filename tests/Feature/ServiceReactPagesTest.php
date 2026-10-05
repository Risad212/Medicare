<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceReactPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_list_and_forms_use_react_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Service::create([
            'title' => 'React Service',
            'description' => 'A clinic service',
            'order' => 2,
            'status' => 1,
        ]);

        $this->actingAs($admin)
            ->get('/admin/services')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Services/Index')
                ->where('services.0.title', 'React Service')
                ->where('services.0.order', 2)
            );

        $this->actingAs($admin)
            ->get('/admin/services/create')
            ->assertInertia(fn ($page) => $page->component('Admin/Services/Create'));

        $service = Service::firstOrFail();
        $this->actingAs($admin)
            ->get("/admin/services/{$service->id}/edit")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Services/Edit')
                ->where('service.title', 'React Service')
                ->where('service.buttonText', 'Read more')
            );
    }

    public function test_service_create_and_update_preserve_status_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->from('/admin/services/create')->post('/admin/services', [
            'title' => 'Inactive Service',
            'description' => 'Hidden by default when unchecked.',
            'order' => '3',
        ])->assertRedirect('/admin/services/create');

        $service = Service::where('title', 'Inactive Service')->firstOrFail();
        $this->assertSame(0, (int) $service->status);

        $this->actingAs($admin)->from("/admin/services/{$service->id}/edit")->post("/admin/services/{$service->id}", [
            '_method' => 'PUT',
            'title' => 'Active Service',
            'description' => 'Visible in the public service catalog.',
            'order' => '1',
            'status' => '1',
        ])->assertRedirect("/admin/services/{$service->id}/edit");

        $this->assertSame('Active Service', $service->fresh()->title);
        $this->assertSame(1, (int) $service->fresh()->status);
    }
}
