<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class AdminPatientTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    public function test_index_lists_only_patients(): void
    {
        $this->makeUser(['name' => 'Visible Patient']);
        $this->makeUser(['name' => 'Hidden Doctor', 'role' => 'doctor']);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.patients.index'))
            ->assertOk()
            ->assertSee('Visible Patient')
            ->assertDontSee('Hidden Doctor');
    }

    public function test_create_page_loads(): void
    {
        $this->actingAs($this->makeAdmin())->get(route('admin.patients.create'))->assertOk();
    }

    public function test_admin_creates_patient(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.patients.store'), [
                'name' => 'New Patient',
                'email' => 'patient@example.com',
                'phone' => '01711-333333',
                'password' => 'secretpass',
                'password_confirmation' => 'secretpass',
            ])
            ->assertRedirect(route('admin.patients.index'))
            ->assertSessionHas('success', 'Patient created successfully.');

        $this->assertDatabaseHas('users', [
            'email' => 'patient@example.com',
            'role' => 'patient',
            'phone' => '01711-333333',
        ]);
    }

    public function test_admin_patient_store_requires_confirming_password(): void
    {
        $this->actingAs($this->makeAdmin())
            ->from(route('admin.patients.create'))
            ->post(route('admin.patients.store'), [
                'name' => 'New Patient',
                'email' => 'patient@example.com',
                'password' => 'secretpass',
            ])->assertSessionHasErrors('password');
    }

    public function test_admin_shows_patient(): void
    {
        $patient = $this->makeUser();

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.patients.show', $patient->id))
            ->assertOk();
    }

    public function test_admin_cannot_show_non_patient_user(): void
    {
        $doctorUser = $this->makeUser(['role' => 'doctor']);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.patients.show', $doctorUser->id))
            ->assertNotFound();
    }

    public function test_admin_updates_patient(): void
    {
        $patient = $this->makeUser();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.patients.update', $patient->id), [
                'name' => 'Renamed Patient',
                'email' => 'renamed@example.com',
                'phone' => '01711-444444',
            ])
            ->assertRedirect(route('admin.patients.index'))
            ->assertSessionHas('success', 'Patient updated successfully.');

        $this->assertDatabaseHas('users', [
            'id' => $patient->id,
            'name' => 'Renamed Patient',
            'email' => 'renamed@example.com',
            'phone' => '01711-444444',
        ]);
    }

    public function test_admin_deletes_patient(): void
    {
        $patient = $this->makeUser();

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.patients.destroy', $patient->id))
            ->assertRedirect(route('admin.patients.index'))
            ->assertSessionHas('success', 'Patient deleted successfully.');

        $this->assertDatabaseMissing('users', ['id' => $patient->id]);
    }

    public function test_role_scoped_delete_does_not_delete_doctor_user(): void
    {
        $doctorUser = $this->makeUser(['role' => 'doctor']);

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.patients.destroy', $doctorUser->id))
            ->assertNotFound();

        $this->assertDatabaseHas('users', ['id' => $doctorUser->id]);
    }
}
