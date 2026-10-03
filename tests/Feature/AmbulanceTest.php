<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Ambulance\Models\AmbulanceRequest;
use App\Modules\Ambulance\Notifications\AmbulanceRequested;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AmbulanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_minimal_request(): void
    {
        Notification::fake();

        $response = $this->post('/ambulance', [
            'requester_name' => 'Karim Uddin',
            'requester_phone' => '+880 1711 223344',
            'pickup_address' => 'House 12, Road 5, Dhanmondi',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('ambulance_requests', [
            'requester_name' => 'Karim Uddin',
            'status' => 0,
        ]);
    }

    public function test_request_notifies_admins(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->post('/ambulance', [
            'requester_name' => 'Karim Uddin',
            'requester_phone' => '01711223344',
            'pickup_address' => 'House 12, Road 5',
        ]);

        Notification::assertSentTo($admin, AmbulanceRequested::class);
    }

    public function test_request_links_logged_in_user(): void
    {
        Notification::fake();
        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($patient)->post('/ambulance', [
            'requester_name' => $patient->name,
            'requester_phone' => '01711223344',
            'pickup_address' => 'House 12',
        ]);

        $this->assertSame($patient->id, AmbulanceRequest::first()->user_id);
    }

    public function test_phone_with_letters_is_rejected(): void
    {
        $response = $this->post('/ambulance', [
            'requester_name' => 'Karim',
            'requester_phone' => 'call-me-now',
            'pickup_address' => 'House 12',
        ]);

        $response->assertSessionHasErrors('requester_phone');
        $this->assertSame(0, AmbulanceRequest::count());
    }

    public function test_admin_can_advance_status_along_allowed_path(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $request = AmbulanceRequest::create([
            'requester_name' => 'Karim',
            'requester_phone' => '01711223344',
            'pickup_address' => 'House 12',
            'status' => 0,
        ]);

        $this->actingAs($admin)->put("/admin/ambulance-requests/{$request->id}", ['status' => 1])
            ->assertRedirect();

        $this->assertSame(1, $request->fresh()->status);
    }

    public function test_admin_cannot_skip_or_resurrect_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $request = AmbulanceRequest::create([
            'requester_name' => 'Karim',
            'requester_phone' => '01711223344',
            'pickup_address' => 'House 12',
            'status' => 0,
        ]);

        // Requested -> Completed is not allowed (must dispatch first).
        $this->actingAs($admin)->put("/admin/ambulance-requests/{$request->id}", ['status' => 2])
            ->assertSessionHasErrors('status');
        $this->assertSame(0, $request->fresh()->status);

        $request->update(['status' => 2]);

        // Completed is terminal.
        $this->actingAs($admin)->put("/admin/ambulance-requests/{$request->id}", ['status' => 1])
            ->assertSessionHasErrors('status');
        $this->assertSame(2, $request->fresh()->status);
    }

    public function test_admin_index_lists_newest_first_with_call_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        AmbulanceRequest::create([
            'requester_name' => 'Old',
            'requester_phone' => '01710000000',
            'pickup_address' => 'Old address',
            'created_at' => now()->subDay(),
        ]);
        AmbulanceRequest::create([
            'requester_name' => 'New',
            'requester_phone' => '01719999999',
            'pickup_address' => 'New address',
        ]);

        $response = $this->actingAs($admin)->get('/admin/ambulance-requests');

        $response->assertOk();
        $response->assertSeeInOrder(['New', 'Old']);
        $response->assertSee('tel:01719999999', false);
    }

    public function test_guest_and_patient_cannot_access_admin_panel(): void
    {
        $this->get('/admin/ambulance-requests')->assertRedirect('/login');

        $patient = User::factory()->create(['role' => 'patient']);
        $this->actingAs($patient)->get('/admin/ambulance-requests')->assertRedirect('/login');
    }

    public function test_disabled_module_hides_ambulance_routes(): void
    {
        config(['modules.ambulance' => false]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/ambulance-requests')->assertNotFound();
        $this->get('/ambulance')->assertNotFound();
    }
}
