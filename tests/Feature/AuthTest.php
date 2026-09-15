<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    public function test_registration_creates_patient_and_redirects_to_profile(): void
    {
        $response = $this->from(route('register'))->post('/register', [
            'name' => 'New Patient',
            'email' => 'patient@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/profile');
        $this->assertDatabaseHas('users', [
            'email' => 'patient@example.com',
            'role' => 'patient',
        ]);
        $this->assertAuthenticated();
    }

    public function test_registration_validates_passwords(): void
    {
        $this->from(route('register'))->post('/register', [
            'name' => 'New Patient',
            'email' => 'patient@example.com',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['password']);

        $this->assertGuest();
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com', 'role' => 'patient']);

        $this->from(route('register'))->post('/register', [
            'name' => 'Other',
            'email' => 'taken@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('email');
    }

    public function test_registration_backfills_guest_appointments_by_email(): void
    {
        $appointment = $this->makeAppointment([
            'email' => 'patient@example.com',
            'user_id' => null,
        ]);

        $this->post('/register', [
            'name' => 'New Patient',
            'email' => 'patient@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect('/profile');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'user_id' => auth()->id(),
        ]);
    }

    public function test_login_redirects_admin_to_admin_home(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com', 'role' => 'admin']);

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.home'));
    }

    public function test_login_redirects_doctor_to_doctor_dashboard(): void
    {
        $doctor = User::factory()->create(['email' => 'doctor@example.com', 'role' => 'doctor']);

        $this->post(route('login'), [
            'email' => $doctor->email,
            'password' => 'password',
        ])->assertRedirect(route('doctor.dashboard'));
    }

    public function test_login_redirects_patient_to_profile(): void
    {
        $patient = User::factory()->create(['email' => 'patient@example.com', 'role' => 'patient']);

        $this->post(route('login'), [
            'email' => $patient->email,
            'password' => 'password',
        ])->assertRedirect(route('profile'));
    }

    public function test_login_redirects_unknown_role_to_home(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('home'));
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'patient@example.com', 'role' => 'patient']);

        $this->post(route('login'), [
            'email' => 'patient@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_logs_the_user_out(): void
    {
        $this->actingAs($this->makeUser())->post(route('logout'))->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_login_backfills_guest_appointments_by_email(): void
    {
        $patient = User::factory()->create([
            'email' => 'patient@example.com',
            'role' => 'patient',
            'password' => 'password',
        ]);

        $appointment = $this->makeAppointment([
            'email' => 'patient@example.com',
            'user_id' => null,
        ]);

        $this->post(route('login'), [
            'email' => $patient->email,
            'password' => 'password',
        ])->assertRedirect(route('profile'));

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'user_id' => $patient->id,
        ]);
    }
}
