<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class AdminDoctorTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    public function test_index_and_create_pages_load(): void
    {
        $this->actingAs($this->makeAdmin())->get(route('admin.doctors.index'))->assertOk();
        $this->actingAs($this->makeAdmin())->get(route('admin.doctors.create'))->assertOk();
    }

    public function test_admin_creates_doctor_user_and_profile(): void
    {
        Storage::fake('public');

        $this->actingAs($this->makeAdmin())
            ->from(route('admin.doctors.create'))
            ->post(route('admin.doctors.store'), [
                'name' => 'Dr. New Specialist',
                'email' => 'newdoctor@example.com',
                'password' => 'secretpass',
                'degree' => 'MBBS, FCPS',
                'department' => 'Cardiology',
                'specialist' => 'Cardiologist',
                'phone' => '01711-999999',
                'image' => UploadedFile::fake()->image('doc.jpg', 200, 200),
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Doctor added successfully!');

        $this->assertDatabaseHas('users', [
            'email' => 'newdoctor@example.com',
            'role' => 'doctor',
        ]);

        $user = User::where('email', 'newdoctor@example.com')->first();

        $this->assertDatabaseHas('doctors', [
            'name' => 'Dr. New Specialist',
            'department' => 'Cardiology',
            'specialist' => 'Cardiologist',
            'status' => 1,
            'user_id' => $user->id,
        ]);

        $doctor = Doctor::where('user_id', $user->id)->first();
        $this->assertNotNull($doctor->image);
        Storage::disk('public')->assertExists($doctor->image);
    }

    public function test_admin_doctor_store_validation(): void
    {
        $this->actingAs($this->makeAdmin())
            ->from(route('admin.doctors.create'))
            ->post(route('admin.doctors.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_admin_doctor_store_rejects_duplicate_email_once(): void
    {
        User::factory()->create(['email' => 'taken@example.com', 'role' => 'doctor']);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.doctors.store'), [
                'name' => 'Dr. Dup',
                'email' => 'taken@example.com',
                'password' => 'secretpass',
            ])->assertSessionHasErrors('email');
    }

    public function test_admin_updates_doctor_and_user(): void
    {
        $doctor = $this->makeDoctor();
        $user = $doctor->user;

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.doctors.update', $doctor->id), [
                'name' => 'Dr. Renamed',
                'email' => 'renamed@example.com',
                'degree' => 'MD',
                'department' => 'Neurology',
                'specialist' => 'Neurologist',
                'phone' => '01711-777777',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Doctor updated successfully!');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Dr. Renamed', 'email' => 'renamed@example.com']);
        $this->assertDatabaseHas('doctors', ['id' => $doctor->id, 'department' => 'Neurology']);
    }

    public function test_admin_doctor_update_keeps_unchanged_email(): void
    {
        $doctor = $this->makeDoctor(['name' => 'Dr. Keep']);
        $user = $doctor->user;

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.doctors.update', $doctor->id), [
                'name' => 'Dr. Keep',
                'email' => $user->email,
            ])->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => $user->email]);
    }

    public function test_admin_doctor_update_rejects_another_users_email(): void
    {
        $doctor = $this->makeDoctor();
        User::factory()->create(['email' => 'other@example.com', 'role' => 'doctor']);

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.doctors.update', $doctor->id), [
                'name' => 'Dr. Renamed',
                'email' => 'other@example.com',
            ])->assertSessionHasErrors('email');
    }

    public function test_admin_deletes_doctor_profile_but_keeps_user(): void
    {
        $doctor = $this->makeDoctor();
        $user = $doctor->user;

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.doctors.destroy', $doctor->id))
            ->assertRedirect(route('admin.doctors.index'))
            ->assertSessionHas('success', 'Doctor deleted successfully!');

        $this->assertDatabaseMissing('doctors', ['id' => $doctor->id]);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_admin_doctor_delete_returns_404_for_missing_doctor(): void
    {
        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.doctors.destroy', 999999))
            ->assertNotFound();
    }

    public function test_admin_index_searches_by_name(): void
    {
        $this->makeDoctor(['name' => 'Dr. Zebra'.uniqid(), 'specialist' => 'Cardiologist']);
        $this->makeDoctor(['name' => 'Dr. Zebra'.uniqid(), 'specialist' => 'Cardiologist']);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.doctors.index', ['search' => 'NoSuchDoctorName']))->assertOk();
    }
}
