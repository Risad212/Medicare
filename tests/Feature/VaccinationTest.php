<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Modules\Vaccination\Models\Vaccination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VaccinationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_vaccination_for_user_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = User::factory()->create(['role' => 'patient']);

        $response = $this->actingAs($admin)->post('/admin/vaccinations', [
            'user_id' => $patient->id,
            'vaccine_name' => 'BCG',
            'dose_number' => 1,
            'status' => 1,
            'date_given' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vaccinations', [
            'user_id' => $patient->id,
            'vaccine_name' => 'BCG',
            'status' => 1,
        ]);
    }

    public function test_admin_vaccination_pages_render_react_components_and_preserve_search(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = User::factory()->create(['role' => 'patient']);
        $vaccination = Vaccination::create([
            'user_id' => $patient->id,
            'child_name' => 'Baby Aarav',
            'vaccine_name' => 'Pentavalent',
            'dose_number' => 2,
            'status' => 0,
            'next_due_date' => now()->subWeek()->toDateString(),
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->get('/admin/vaccinations?search=Pentavalent')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Vaccinations/Index')
                ->where('filters.search', 'Pentavalent')
                ->where('vaccinations.data.0.subjectName', 'Baby Aarav')
                ->where('vaccinations.data.0.isOverdue', true)
            );

        $this->actingAs($admin)->get('/admin/vaccinations/create')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Vaccinations/Form')
                ->where('mode', 'create')
                ->has('users')
                ->has('patients')
            );

        $this->actingAs($admin)->get("/admin/vaccinations/{$vaccination->id}/edit")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Vaccinations/Form')
                ->where('mode', 'edit')
                ->where('vaccination.vaccineName', 'Pentavalent')
            );

        $this->actingAs($admin)->get("/admin/vaccinations/{$vaccination->id}")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Vaccinations/Show')
                ->where('vaccination.creatorName', $admin->name)
                ->where('vaccination.isOverdue', true)
            );
    }

    public function test_doctor_can_create_vaccination_for_register_child(): void
    {
        [$doctorUser] = $this->makeDoctor();
        $register = Patient::create(['name' => 'Baby Aarav']);

        $response = $this->actingAs($doctorUser)->post('/doctor/vaccinations', [
            'patient_id' => $register->id,
            'child_name' => 'Baby Aarav',
            'vaccine_name' => 'Pentavalent',
            'dose_number' => 2,
            'status' => 0,
            'next_due_date' => now()->addMonth()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vaccinations', [
            'patient_id' => $register->id,
            'child_name' => 'Baby Aarav',
            'dose_number' => 2,
        ]);
    }

    public function test_vaccination_requires_subject_and_valid_dose(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/vaccinations', [
            'vaccine_name' => 'BCG',
            'dose_number' => 0,
            'status' => 0,
        ]);

        $response->assertSessionHasErrors(['user_id', 'patient_id', 'dose_number']);
    }

    public function test_completed_vaccination_requires_date_given(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = User::factory()->create(['role' => 'patient']);

        $response = $this->actingAs($admin)->post('/admin/vaccinations', [
            'user_id' => $patient->id,
            'vaccine_name' => 'BCG',
            'dose_number' => 1,
            'status' => 1,
        ]);

        $response->assertSessionHasErrors('date_given');
    }

    public function test_patient_sees_own_timeline_with_overdue_flag(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $other = User::factory()->create(['role' => 'patient']);

        Vaccination::create([
            'user_id' => $patient->id,
            'child_name' => 'Baby Aarav',
            'vaccine_name' => 'Pentavalent',
            'dose_number' => 2,
            'status' => 0,
            'next_due_date' => now()->subWeek()->toDateString(),
        ]);
        Vaccination::create([
            'user_id' => $other->id,
            'vaccine_name' => 'BCG',
            'dose_number' => 1,
            'status' => 1,
            'date_given' => now()->toDateString(),
        ]);

        $response = $this->actingAs($patient)->get('/my-vaccinations');

        $response->assertOk();
        $response->assertSee('Pentavalent');
        $response->assertSee('Overdue');
        $response->assertDontSee('BCG');
    }

    public function test_duplicate_dose_for_same_subject_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = User::factory()->create(['role' => 'patient']);

        $payload = [
            'user_id' => $patient->id,
            'vaccine_name' => 'BCG',
            'dose_number' => 1,
            'status' => 1,
            'date_given' => now()->toDateString(),
        ];

        $this->actingAs($admin)->post('/admin/vaccinations', $payload)->assertRedirect();
        $this->actingAs($admin)->post('/admin/vaccinations', $payload)->assertSessionHasErrors('vaccine_name');

        $this->assertSame(1, Vaccination::count());
    }

    public function test_same_vaccine_different_dose_is_allowed_and_update_self_is_allowed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = User::factory()->create(['role' => 'patient']);

        $first = Vaccination::create([
            'user_id' => $patient->id,
            'vaccine_name' => 'BCG',
            'dose_number' => 1,
            'status' => 1,
            'date_given' => now()->toDateString(),
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->post('/admin/vaccinations', [
            'user_id' => $patient->id,
            'vaccine_name' => 'BCG',
            'dose_number' => 2,
            'status' => 0,
        ])->assertRedirect();

        $this->actingAs($admin)->put("/admin/vaccinations/{$first->id}", [
            'user_id' => $patient->id,
            'vaccine_name' => 'BCG',
            'dose_number' => 1,
            'status' => 1,
            'date_given' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertSame(2, Vaccination::count());
    }

    public function test_created_by_records_the_staff_member(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($admin)->post('/admin/vaccinations', [
            'user_id' => $patient->id,
            'vaccine_name' => 'BCG',
            'dose_number' => 1,
            'status' => 1,
            'date_given' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertSame($admin->id, Vaccination::first()->created_by);
    }

    public function test_guest_and_patient_cannot_access_staff_indexes(): void
    {
        $this->get('/admin/vaccinations')->assertRedirect('/login');
        $this->get('/doctor/vaccinations')->assertRedirect('/login');
        $this->get('/my-vaccinations')->assertRedirect('/login');

        $patient = User::factory()->create(['role' => 'patient']);
        $this->actingAs($patient)->get('/admin/vaccinations')->assertRedirect('/login');
        $this->actingAs($patient)->get('/doctor/vaccinations')->assertRedirect('/login');
    }

    /**
     * @return array{0: User, 1: Doctor}
     */
    private function makeDoctor(): array
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Dr Vax',
            'slug' => 'dr-vax-'.uniqid(),
            'user_id' => $doctorUser->id,
            'status' => 1,
        ]);

        return [$doctorUser, $doctor];
    }

    public function test_disabled_module_hides_vaccination_routes(): void
    {
        config(['modules.vaccination' => false]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/vaccinations')->assertNotFound();
        $this->get('/my-vaccinations')->assertRedirect('/login');
    }
}
