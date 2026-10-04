<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_access_doctor_dashboard(): void
    {
        $response = $this->get('/doctor/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_patient_cannot_access_admin_dashboard(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $response = $this->actingAs($patient)->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_patient_cannot_access_doctor_dashboard(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $response = $this->actingAs($patient)->get('/doctor/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_doctor_cannot_access_admin_dashboard(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);

        $response = $this->actingAs($doctorUser)->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->where('auth.user.role', 'admin')
            ->has('metrics.admin.rangeStats')
        );
    }

    public function test_receptionist_gets_only_front_desk_dashboard_data(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);

        $this->actingAs($receptionist)
            ->get('/admin')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Dashboard')
                ->where('auth.user.role', 'receptionist')
                ->has('metrics.frontDesk')
                ->where('metrics.admin', null)
                ->where('metrics.laboratory', null)
                ->where('metrics.pharmacy', null)
            );
    }

    public function test_admin_appointments_register_uses_react_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/appointments')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Appointments/Index')
                ->where('filters.search', '')
                ->where('appointments.total', 0)
                ->has('routes.export')
            );
    }

    public function test_appointments_register_preserves_search_and_row_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Dr Register',
            'slug' => 'dr-register',
            'user_id' => $doctorUser->id,
            'status' => 1,
        ]);
        $slot = TimeSlot::create(['time' => '10:00 AM', 'status' => 1]);
        Appointment::create([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'patient_name' => 'Patient Register',
            'age' => 34,
            'gender' => 1,
            'phone' => '01700000000',
            'email' => 'patient@example.test',
            'visit_type' => 1,
            'appointment_date' => now()->toDateString(),
            'status' => 0,
        ]);

        $this->actingAs($admin)
            ->get('/admin/appointments?search=Register')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Appointments/Index')
                ->where('filters.search', 'Register')
                ->where('appointments.total', 1)
                ->where('appointments.data.0.patientName', 'Patient Register')
                ->where('appointments.data.0.doctorName', 'Dr Register')
                ->where('appointments.data.0.gender', 'Male')
                ->where('appointments.data.0.visitType', 'First Visit')
                ->where('appointments.data.0.time', '10:00 AM')
            );
    }

    public function test_appointment_create_and_edit_forms_use_react_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Dr Appointment Form',
            'slug' => 'dr-appointment-form',
            'user_id' => $doctorUser->id,
            'status' => 1,
        ]);
        $slot = TimeSlot::create(['time' => '11:00 AM', 'status' => 1]);
        $appointment = Appointment::create([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'patient_name' => 'Appointment Form Patient',
            'age' => 41,
            'gender' => 2,
            'phone' => '01700000001',
            'email' => 'form-patient@example.test',
            'visit_type' => 2,
            'appointment_date' => now()->toDateString(),
            'status' => 1,
        ]);

        $this->actingAs($admin)
            ->get('/admin/appointments/create')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Appointments/Create')
                ->has('doctors', 1)
                ->where('doctors.0.name', 'Dr Appointment Form')
                ->has('timeSlots', 1)
                ->where('timeSlots.0.time', '11:00 AM')
                ->has('defaultDate')
            );

        $this->actingAs($admin)
            ->get("/admin/appointments/{$appointment->id}/edit")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Appointments/Edit')
                ->where('appointment.name', 'Appointment Form Patient')
                ->where('appointment.doctorId', $doctor->id)
                ->where('appointment.timeSlotId', $slot->id)
                ->where('appointment.status', '1')
                ->where('appointment.visitTypeLabel', $appointment->visit_type_label)
            );
    }

    public function test_doctor_can_access_doctor_dashboard(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        Doctor::create([
            'name' => 'Dr Test',
            'slug' => 'dr-test',
            'user_id' => $doctorUser->id,
            'status' => 1,
        ]);

        $response = $this->actingAs($doctorUser)->get('/doctor/dashboard');

        $response->assertOk();
    }
}
