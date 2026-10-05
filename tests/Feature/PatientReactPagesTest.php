<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientReactPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_register_and_forms_use_react_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = Patient::create([
            'name' => 'React Patient',
            'email' => 'react-patient@example.test',
            'phone' => '01700000010',
            'gender' => 'female',
            'date_of_birth' => '1990-02-03',
        ]);

        $this->actingAs($admin)
            ->get('/admin/patients?search=React')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Patients/Index')
                ->where('filters.search', 'React')
                ->where('patients.total', 1)
                ->where('patients.data.0.name', 'React Patient')
                ->where('patients.data.0.dateOfBirth', '1990-02-03')
                ->has('routes.export')
            );

        $this->actingAs($admin)
            ->get('/admin/patients/create')
            ->assertInertia(fn ($page) => $page->component('Admin/Patients/Create'));

        $this->actingAs($admin)
            ->get("/admin/patients/{$patient->id}/edit")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Patients/Edit')
                ->where('patient.name', 'React Patient')
            );
    }

    public function test_patient_details_include_matched_visit_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = Patient::create([
            'name' => 'Visit Patient',
            'email' => 'visit-patient@example.test',
            'phone' => '01700000011',
        ]);
        $doctor = Doctor::create(['name' => 'Dr Visit', 'slug' => 'dr-visit', 'status' => 1]);
        $slot = TimeSlot::create(['time' => '10:00 AM', 'status' => 1]);
        Appointment::create([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'patient_name' => 'Visit Patient',
            'gender' => 1,
            'phone' => '01700000011',
            'email' => 'visit-patient@example.test',
            'visit_type' => 2,
            'appointment_date' => now()->addDay()->toDateString(),
            'status' => 1,
        ]);

        $this->actingAs($admin)
            ->get("/admin/patients/{$patient->id}")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Patients/Show')
                ->where('patient.name', 'Visit Patient')
                ->has('visits', 1)
                ->where('visits.0.doctor', 'Dr Visit')
                ->where('visits.0.visitType', 'Second Visit')
                ->where('visits.0.status', 1)
            );
    }

    public function test_patient_record_crud_does_not_create_or_change_a_login_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $userCount = User::count();

        $this->actingAs($admin)->post('/admin/patients', [
            'name' => 'Clinic Record',
            'email' => 'clinic-record@example.test',
            'gender' => 'other',
            'blood_group' => 'O+',
        ])->assertRedirect('/admin/patients')
            ->assertSessionHas('success', 'Patient created successfully.');

        $patient = Patient::where('email', 'clinic-record@example.test')->firstOrFail();
        $this->assertSame($userCount, User::count());

        $this->actingAs($admin)->put("/admin/patients/{$patient->id}", [
            'name' => 'Updated Clinic Record',
            'email' => 'clinic-record@example.test',
            'gender' => 'other',
            'blood_group' => 'O+',
        ])->assertRedirect('/admin/patients');

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'name' => 'Updated Clinic Record',
        ]);

        $this->actingAs($admin)->delete("/admin/patients/{$patient->id}")
            ->assertRedirect('/admin/patients');

        $this->assertDatabaseMissing('patients', ['id' => $patient->id]);
        $this->assertSame($userCount, User::count());
    }
}
