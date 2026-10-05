<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Beds\Models\Bed;
use App\Modules\Beds\Models\Room;
use App\Modules\Beds\Models\Ward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BedTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_ward_room_and_bed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/wards', ['name' => 'ICU'])->assertRedirect();
        $ward = Ward::first();

        $this->actingAs($admin)->post('/admin/rooms', [
            'ward_id' => $ward->id,
            'room_number' => '101',
            'room_type' => 'Private',
        ])->assertRedirect();
        $room = Room::first();

        $this->actingAs($admin)->post('/admin/beds', [
            'room_id' => $room->id,
            'bed_number' => 'B-01',
            'status' => 0,
        ])->assertRedirect();

        $this->assertDatabaseHas('beds', ['bed_number' => 'B-01', 'status' => 0]);
    }

    public function test_assign_and_discharge_flow(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = User::factory()->create(['role' => 'patient']);
        $bed = $this->makeBed();

        $this->actingAs($admin)->post("/admin/beds/{$bed->id}/assign", [
            'patient_user_id' => $patient->id,
        ])->assertRedirect()->assertSessionHas('success');

        $fresh = $bed->fresh();
        $this->assertSame(1, $fresh->status);
        $this->assertSame($patient->id, $fresh->current_patient_id);
        $this->assertNotNull($fresh->admitted_at);

        $this->actingAs($admin)->post("/admin/beds/{$bed->id}/discharge")
            ->assertRedirect()->assertSessionHas('success');

        $fresh = $bed->fresh();
        $this->assertSame(0, $fresh->status);
        $this->assertNull($fresh->current_patient_id);
        $this->assertNull($fresh->admitted_at);
    }

    public function test_double_assign_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $first = User::factory()->create(['role' => 'patient']);
        $second = User::factory()->create(['role' => 'patient']);
        $bed = $this->makeBed();

        $this->actingAs($admin)->post("/admin/beds/{$bed->id}/assign", [
            'patient_user_id' => $first->id,
        ])->assertRedirect();

        $this->actingAs($admin)->post("/admin/beds/{$bed->id}/assign", [
            'patient_user_id' => $second->id,
        ])->assertSessionHasErrors('bed');

        $this->assertSame($first->id, $bed->fresh()->current_patient_id);
    }

    public function test_patient_cannot_occupy_two_beds(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = User::factory()->create(['role' => 'patient']);
        $first = $this->makeBed('B-01');
        $second = $this->makeBed('B-02');

        $this->actingAs($admin)->post("/admin/beds/{$first->id}/assign", [
            'patient_user_id' => $patient->id,
        ])->assertRedirect();

        $this->actingAs($admin)->post("/admin/beds/{$second->id}/assign", [
            'patient_user_id' => $patient->id,
        ])->assertSessionHasErrors('patient_user_id');

        $this->assertSame(0, $second->fresh()->status);
    }

    public function test_discharge_of_free_bed_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $bed = $this->makeBed();

        $this->actingAs($admin)->post("/admin/beds/{$bed->id}/discharge")
            ->assertSessionHasErrors('bed');
    }

    public function test_occupied_bed_cannot_be_edited_or_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = User::factory()->create(['role' => 'patient']);
        $bed = $this->makeBed();

        $this->actingAs($admin)->post("/admin/beds/{$bed->id}/assign", [
            'patient_user_id' => $patient->id,
        ])->assertRedirect();

        $this->actingAs($admin)->put("/admin/beds/{$bed->id}", [
            'room_id' => $bed->room_id,
            'bed_number' => 'B-99',
            'status' => 0,
        ])->assertSessionHasErrors('bed');

        $this->actingAs($admin)->delete("/admin/beds/{$bed->id}")
            ->assertSessionHasErrors('bed');

        $this->assertTrue(Bed::whereKey($bed->id)->exists());
    }

    public function test_ward_with_rooms_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ward = Ward::create(['name' => 'ICU']);
        $ward->rooms()->create(['room_number' => '101', 'room_type' => 'General']);

        $this->actingAs($admin)->delete("/admin/wards/{$ward->id}")
            ->assertSessionHasErrors('ward');

        $this->assertTrue(Ward::whereKey($ward->id)->exists());
    }

    public function test_dashboard_shows_availability_counts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = User::factory()->create(['role' => 'patient']);
        $bed = $this->makeBed();

        $this->actingAs($admin)->post("/admin/beds/{$bed->id}/assign", [
            'patient_user_id' => $patient->id,
        ])->assertRedirect();

        $response = $this->actingAs($admin)->get('/admin/beds');

        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Beds/Index')
            ->where('wards.0.availableBedsCount', 0)
            ->where('wards.0.bedsCount', 1)
            ->where('wards.0.rooms.0.beds.0.patientName', $patient->name)
        );
    }

    public function test_ward_and_room_pages_render_react_components(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $bed = $this->makeBed();

        $this->actingAs($admin)->get('/admin/wards')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Wards/Index')
                ->where('wards.data.0.name', 'General Ward')
            );

        $this->actingAs($admin)->get("/admin/wards/{$bed->room->ward_id}")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Wards/Show')
                ->where('ward.rooms.0.bedsCount', 1)
            );

        $this->actingAs($admin)->get('/admin/rooms')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Rooms/Index')
                ->where('rooms.data.0.roomNumber', '101')
            );

        $this->actingAs($admin)->get("/admin/rooms/{$bed->room_id}")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Rooms/Show')
                ->where('room.beds.0.bedNumber', 'B-01')
            );
    }

    public function test_bed_forms_render_react_component(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $bed = $this->makeBed();

        $this->actingAs($admin)->get('/admin/beds/create')
            ->assertInertia(fn ($page) => $page->component('Admin/Beds/Form')->where('mode', 'create'));

        $this->actingAs($admin)->get("/admin/beds/{$bed->id}/edit")
            ->assertInertia(fn ($page) => $page->component('Admin/Beds/Form')->where('mode', 'edit'));
    }

    public function test_guest_and_patient_cannot_access_bed_admin(): void
    {
        $this->get('/admin/beds')->assertRedirect('/login');
        $this->get('/admin/wards')->assertRedirect('/login');

        $patient = User::factory()->create(['role' => 'patient']);
        $this->actingAs($patient)->get('/admin/beds')->assertRedirect('/login');
        $this->actingAs($patient)->get('/admin/rooms')->assertRedirect('/login');
    }

    public function test_disabled_module_hides_bed_routes(): void
    {
        config(['modules.beds' => false]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/beds')->assertNotFound();
        $this->actingAs($admin)->get('/admin/wards')->assertNotFound();
        $this->actingAs($admin)->get('/admin/rooms')->assertNotFound();
    }

    private function makeBed(string $number = 'B-01'): Bed
    {
        $ward = Ward::create(['name' => 'General Ward']);
        $room = $ward->rooms()->create(['room_number' => '101', 'room_type' => 'General']);

        return $room->beds()->create(['bed_number' => $number, 'status' => 0]);
    }
}
