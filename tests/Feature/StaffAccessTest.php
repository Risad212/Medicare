<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class StaffAccessTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    private function staff(string $role)
    {
        return $this->makeUser(['role' => $role]);
    }

    public function test_receptionist_reaches_front_desk_modules(): void
    {
        $user = $this->staff('receptionist');

        $this->actingAs($user)->get(route('admin.home'))->assertOk();
        $this->actingAs($user)->get(route('admin.appointments.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.patients.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.doctors.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.invoices.index'))->assertOk();
    }

    public function test_receptionist_is_denied_outside_modules_and_deletes(): void
    {
        $user = $this->staff('receptionist');
        $appointment = $this->makeAppointment();

        $this->actingAs($user)->get(route('settings.general'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.lab-orders.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.prescriptions.index'))->assertForbidden();
        $this->actingAs($user)->delete(route('admin.appointments.destroy', $appointment))->assertForbidden();
    }

    public function test_receptionist_manages_blood_bank_daily_ops(): void
    {
        $user = $this->staff('receptionist');

        $this->actingAs($user)->get(route('admin.bloodbank.dashboard'))->assertOk();
        $this->actingAs($user)->get(route('admin.bloodbank.inventory'))->assertOk();
        $this->actingAs($user)->get(route('admin.blood-donors.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.blood-donations.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.blood-requests.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.blood-issues.index'))->assertOk();

        // Settings and deletes stay admin-only.
        $this->actingAs($user)->patch(route('admin.bloodbank.settings.update'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.bloodbank.reports'))->assertOk();
    }

    public function test_lab_technician_lives_in_lab_modules_only(): void
    {
        $user = $this->staff('lab-technician');

        $this->actingAs($user)->get(route('admin.home'))->assertOk();
        $this->actingAs($user)->get(route('admin.lab-tests.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.lab-orders.index'))->assertOk();

        $this->actingAs($user)->get(route('admin.appointments.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.invoices.index'))->assertForbidden();
        $this->actingAs($user)->get(route('settings.general'))->assertForbidden();
    }

    public function test_pharmacist_reads_prescriptions_only(): void
    {
        $user = $this->staff('pharmacist');

        $this->actingAs($user)->get(route('admin.home'))->assertOk();
        $this->actingAs($user)->get(route('admin.prescriptions.index'))->assertOk();

        $this->actingAs($user)->get(route('admin.lab-orders.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.appointments.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_admin_keeps_full_access(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->get(route('admin.home'))->assertOk();
        $this->actingAs($admin)->get(route('settings.general'))->assertOk();
        $this->actingAs($admin)->get(route('admin.lab-orders.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.prescriptions.index'))->assertOk();
    }

    public function test_doctor_is_kept_out_of_admin_panel(): void
    {
        $doctor = $this->makeDoctor();

        $this->actingAs($doctor->user)->get(route('admin.home'))->assertRedirect('/login');
    }

    public function test_dashboard_today_card_shows_only_todays_queue(): void
    {
        $admin = $this->makeAdmin();
        $this->makeAppointment([
            'patient_name' => 'Today Patient',
            'appointment_date' => now()->toDateString(),
        ]);
        $this->makeAppointment([
            'patient_name' => 'Old Patient',
            'appointment_date' => now()->subDays(5)->toDateString(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.home'));

        $response->assertOk();
        $response->assertSee('Today Patient');
        $response->assertDontSee('Old Patient');
    }

    public function test_dashboard_cards_follow_role_scope(): void
    {
        $adminHome = route('admin.home');

        $this->actingAs($this->staff('receptionist'))->get($adminHome)
            ->assertOk()
            ->assertSee("Today's appointments", false)
            ->assertSee('Doctors on duty')
            ->assertDontSee('Lab queue')
            ->assertDontSee('Recent prescriptions')
            ->assertDontSee('Pending comments')
            ->assertDontSee('Hospital Earning');

        $this->actingAs($this->staff('lab-technician'))->get($adminHome)
            ->assertOk()
            ->assertSee('Lab queue')
            ->assertSee('Pending Lab Orders')
            ->assertDontSee("today's queue across all doctors")
            ->assertDontSee('Doctors on duty')
            ->assertDontSee('Hospital Earning');

        $this->actingAs($this->staff('pharmacist'))->get($adminHome)
            ->assertOk()
            ->assertSee('Recent prescriptions')
            ->assertDontSee("today's queue across all doctors")
            ->assertDontSee('Lab queue')
            ->assertDontSee('Pending comments');

        $this->actingAs($this->makeAdmin())->get($adminHome)
            ->assertOk()
            ->assertSee("Today's appointments", false)
            ->assertSee('Lab queue')
            ->assertSee('Recent prescriptions')
            ->assertSee('Pending comments');
    }
}
