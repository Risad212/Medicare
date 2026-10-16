<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientPickerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_patient_picker_lists_only_patient_role_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = User::factory()->create(['role' => 'patient', 'name' => 'Alice Patient']);
        User::factory()->create(['role' => 'doctor', 'name' => 'Bob Doctor']);

        $response = $this->actingAs($admin)->getJson(route('admin.patients.picker'));

        $response->assertOk()
            ->assertJsonFragment(['id' => $patient->id]);

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('Alice Patient (#'.$patient->id.')'));
        $this->assertFalse($names->contains(fn ($n) => str_contains($n, 'Bob Doctor')));
    }

    public function test_patient_picker_filters_by_search_term(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $match = User::factory()->create(['role' => 'patient', 'name' => 'Zubair Khan']);
        User::factory()->create(['role' => 'patient', 'name' => 'Ayesha Rahman']);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.patients.picker', ['q' => 'Zubair']));

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertSame([$match->id], $ids);
    }

    public function test_patient_picker_treats_wildcards_literally(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'patient', 'name' => 'Zubair Khan']);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.patients.picker', ['q' => '%']));

        $response->assertOk();
        $this->assertSame([], $response->json('data'));
    }

    public function test_doctor_can_use_their_patient_picker(): void
    {
        $doctor = User::factory()->create(['role' => 'doctor']);
        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($doctor)
            ->getJson(route('doctor.patients.picker'))
            ->assertOk()
            ->assertJsonFragment(['id' => $patient->id]);
    }

    public function test_patient_role_cannot_reach_the_admin_picker(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($patient)
            ->getJson(route('admin.patients.picker'))
            ->assertForbidden();
    }

    public function test_patient_records_picker_lists_register_entries(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $record = Patient::create(['name' => 'Baby Aarav']);

        $this->actingAs($admin)
            ->getJson(route('admin.patient-records.picker'))
            ->assertOk()
            ->assertJsonFragment(['id' => $record->id, 'name' => 'Baby Aarav (#'.$record->id.')']);
    }

    public function test_patient_records_picker_search_filters_register_entries(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Patient::create(['name' => 'Baby Aarav']);
        $match = Patient::create(['name' => 'Baby Zara']);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.patient-records.picker', ['q' => 'Zara']));

        $response->assertOk();
        $this->assertSame([$match->id], collect($response->json('data'))->pluck('id')->all());
    }
}
