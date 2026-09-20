<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class AdminPatientTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    public function test_index_lists_patient_records(): void
    {
        $this->makePatient(['name' => 'Visible Patient']);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.patients.index'))
            ->assertOk()
            ->assertSee('Visible Patient');
    }

    public function test_index_filters_by_search(): void
    {
        $this->makePatient(['name' => 'Searched Person', 'email' => 'searched@example.com']);
        $this->makePatient(['name' => 'Other Person', 'email' => 'other@example.com']);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.patients.index', ['search' => 'Searched']))
            ->assertOk()
            ->assertSee('Searched Person')
            ->assertDontSee('Other Person');
    }

    public function test_create_page_loads(): void
    {
        $this->actingAs($this->makeAdmin())->get(route('admin.patients.create'))->assertOk();
    }

    public function test_admin_creates_patient_record_without_account(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.patients.store'), [
                'name' => 'New Patient',
                'email' => 'patient@example.com',
                'phone' => '01711-333333',
            ])
            ->assertRedirect(route('admin.patients.index'))
            ->assertSessionHas('success', 'Patient created successfully.');

        $this->assertDatabaseHas('patients', [
            'email' => 'patient@example.com',
            'phone' => '01711-333333',
        ]);
        $this->assertDatabaseMissing('users', [
            'email' => 'patient@example.com',
        ]);
    }

    public function test_admin_patient_store_requires_name(): void
    {
        $this->actingAs($this->makeAdmin())
            ->from(route('admin.patients.create'))
            ->post(route('admin.patients.store'), [
                'email' => 'patient@example.com',
            ])->assertSessionHasErrors('name');
    }

    public function test_admin_shows_patient(): void
    {
        $patient = $this->makePatient();

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.patients.show', $patient->id))
            ->assertOk();
    }

    public function test_admin_cannot_show_missing_patient(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(route('admin.patients.show', 9999))
            ->assertNotFound();
    }

    public function test_admin_updates_patient(): void
    {
        $patient = $this->makePatient();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.patients.update', $patient->id), [
                'name' => 'Renamed Patient',
                'email' => 'renamed@example.com',
                'phone' => '01711-444444',
            ])
            ->assertRedirect(route('admin.patients.index'))
            ->assertSessionHas('success', 'Patient updated successfully.');

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'name' => 'Renamed Patient',
            'email' => 'renamed@example.com',
            'phone' => '01711-444444',
        ]);
    }

    public function test_admin_deletes_patient_record_but_keeps_user_account(): void
    {
        $patient = $this->makePatient(['email' => 'shared@example.com']);
        $user = $this->makeUser(['email' => 'shared@example.com']);

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.patients.destroy', $patient->id))
            ->assertRedirect(route('admin.patients.index'))
            ->assertSessionHas('success', 'Patient deleted successfully.');

        $this->assertDatabaseMissing('patients', ['id' => $patient->id]);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
