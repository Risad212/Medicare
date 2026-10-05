<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DoctorDepartmentReactPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_register_forms_and_availability_use_react_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $department = Department::create(['name' => 'Cardiology', 'status' => 1]);
        $doctor = Doctor::create([
            'name' => 'Dr React',
            'slug' => 'dr-react',
            'department' => $department->name,
            'user_id' => $doctorUser->id,
            'status' => 1,
        ]);
        $slot = TimeSlot::create(['time' => '10:30 AM', 'status' => 1]);
        $doctor->schedules()->create(['weekday' => 1, 'time_slot_id' => $slot->id]);

        $this->actingAs($admin)
            ->get('/admin/doctors?search=React')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Doctors/Index')
                ->where('filters.search', 'React')
                ->where('doctors.total', 1)
                ->where('doctors.data.0.name', 'Dr React')
            );

        $this->actingAs($admin)
            ->get('/admin/doctors/create')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Doctors/Create')
                ->where('departments.0.name', 'Cardiology')
            );

        $this->actingAs($admin)
            ->get("/admin/doctors/{$doctor->id}/edit")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Doctors/Edit')
                ->where('doctor.name', 'Dr React')
                ->where('doctor.email', $doctorUser->email)
            );

        $this->actingAs($admin)
            ->get("/admin/doctors/{$doctor->id}/availability")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Doctors/Availability')
                ->where('doctor.name', 'Dr React')
                ->where('openByWeekday.1.0', $slot->id)
            );
    }

    public function test_doctor_create_and_update_preserve_login_account_behavior(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Department::create(['name' => 'Neurology', 'status' => 1]);

        $this->actingAs($admin)->from('/admin/doctors/create')->post('/admin/doctors', [
            'name' => 'Dr New Specialist',
            'email' => 'new-specialist@example.test',
            'password' => 'strong-password',
            'department' => 'Neurology',
            'specialist' => 'Neurology',
            'status' => '1',
        ])->assertRedirect('/admin/doctors/create');

        $doctor = Doctor::where('name', 'Dr New Specialist')->firstOrFail();
        $doctorUser = $doctor->user;
        $this->assertNotNull($doctorUser);
        $this->assertSame('doctor', $doctorUser->role);
        $this->assertTrue(Hash::check('strong-password', $doctorUser->password));

        $this->actingAs($admin)->from("/admin/doctors/{$doctor->id}/edit")->post("/admin/doctors/{$doctor->id}", [
            '_method' => 'PUT',
            'name' => 'Dr Updated Specialist',
            'email' => 'updated-specialist@example.test',
            'password' => '',
            'department' => 'Neurology',
            'status' => '0',
        ])->assertRedirect("/admin/doctors/{$doctor->id}/edit");

        $this->assertSame('doctor', $doctorUser->fresh()->role);
        $this->assertSame('updated-specialist@example.test', $doctorUser->fresh()->email);
        $this->assertTrue(Hash::check('strong-password', $doctorUser->fresh()->password));
        $this->assertSame(0, (int) $doctor->fresh()->status);
    }

    public function test_schedule_and_off_days_preserve_booking_guards(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $doctor = Doctor::create(['name' => 'Dr Calendar', 'slug' => 'dr-calendar', 'status' => 1]);
        $slot = TimeSlot::create(['time' => '09:00 AM', 'status' => 1]);
        $appointmentDate = now()->addDays(3)->toDateString();

        $this->actingAs($admin)->post("/admin/doctors/{$doctor->id}/availability", [
            'schedules' => [now()->addDays(3)->dayOfWeek => [$slot->id]],
        ])->assertRedirect();

        $this->assertDatabaseHas('doctor_schedules', [
            'doctor_id' => $doctor->id,
            'weekday' => now()->addDays(3)->dayOfWeek,
            'time_slot_id' => $slot->id,
        ]);

        $this->actingAs($admin)->post("/admin/doctors/{$doctor->id}/off-days", [
            'date' => $appointmentDate,
            'reason' => 'Conference',
        ])->assertRedirect();

        $this->assertDatabaseHas('doctor_off_days', ['doctor_id' => $doctor->id, 'date' => $appointmentDate]);

        $doctor->offDays()->delete();
        Appointment::create([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'patient_name' => 'Booked Patient',
            'gender' => 1,
            'phone' => '01700000020',
            'visit_type' => 1,
            'appointment_date' => $appointmentDate,
            'status' => 0,
        ]);

        $this->actingAs($admin)->from("/admin/doctors/{$doctor->id}/availability")->post("/admin/doctors/{$doctor->id}/off-days", [
            'date' => $appointmentDate,
        ])->assertRedirect("/admin/doctors/{$doctor->id}/availability")
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('doctor_off_days', ['doctor_id' => $doctor->id, 'date' => $appointmentDate]);
    }

    public function test_doctor_with_history_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $doctor = Doctor::create(['name' => 'Dr Has History', 'slug' => 'dr-has-history', 'status' => 1]);
        $slot = TimeSlot::create(['time' => '02:00 PM', 'status' => 1]);
        Appointment::create([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'patient_name' => 'History Patient',
            'gender' => 1,
            'phone' => '01700000021',
            'visit_type' => 1,
            'appointment_date' => now()->addDay()->toDateString(),
            'status' => 0,
        ]);

        $this->actingAs($admin)->delete("/admin/doctors/{$doctor->id}")
            ->assertRedirect('/admin/doctors')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('doctors', ['id' => $doctor->id]);
    }

    public function test_department_listing_forms_and_crud_use_react_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $department = Department::create([
            'name' => 'Emergency',
            'description' => 'Emergency care',
            'status' => 1,
        ]);

        $this->actingAs($admin)
            ->get('/admin/departments')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Departments/Index')
                ->where('departments.0.name', 'Emergency')
            );

        $this->actingAs($admin)
            ->get('/admin/departments/create')
            ->assertInertia(fn ($page) => $page->component('Admin/Departments/Create'));

        $this->actingAs($admin)
            ->get("/admin/departments/{$department->id}/edit")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Departments/Edit')
                ->where('department.name', 'Emergency')
            );

        $this->actingAs($admin)->from('/admin/departments/create')->post('/admin/departments', [
            'name' => 'Imaging',
            'description' => 'Imaging services',
            'status' => '1',
        ])->assertRedirect('/admin/departments/create');

        $created = Department::where('name', 'Imaging')->firstOrFail();
        $this->assertSame(1, (int) $created->status);

        $this->actingAs($admin)->from("/admin/departments/{$created->id}/edit")->put("/admin/departments/{$created->id}", [
            'name' => 'Radiology',
            'description' => 'Diagnostic imaging',
        ])->assertRedirect("/admin/departments/{$created->id}/edit");

        $this->assertSame(0, (int) $created->fresh()->status);
        $this->assertSame('Radiology', $created->fresh()->name);
    }
}
