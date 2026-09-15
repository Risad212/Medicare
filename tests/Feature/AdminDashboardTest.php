<?php

namespace Tests\Feature;

use App\Models\LabOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    public function test_admin_home_is_accessible_to_admin(): void
    {
        $this->actingAs($this->makeAdmin())->get(route('admin.home'))->assertOk();
    }

    public function test_admin_home_redirects_guests_to_login(): void
    {
        $this->get(route('admin.home'))->assertRedirect('/login');
    }

    public function test_admin_home_redirects_patients_to_login(): void
    {
        $this->actingAs($this->makeUser())->get(route('admin.home'))->assertRedirect('/login');
    }

    public function test_admin_home_redirects_doctors_to_login(): void
    {
        $this->actingAs($this->makeUser(['role' => 'doctor']))->get(route('admin.home'))->assertRedirect('/login');
    }

    public function test_admin_home_shows_revenue_and_lab_analytics(): void
    {
        $doctor = $this->makeDoctor();
        $test = $this->makeLabTest(['price' => 25.00]);

        $order = LabOrder::create([
            'doctor_id' => $doctor->id,
            'patient_name' => 'Ana',
            'priority' => 'normal',
            'status' => 'completed',
            'total' => 25.00,
        ]);

        $order->items()->create([
            'lab_test_id' => $test->id,
            'price' => 25.00,
        ]);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.home'))
            ->assertOk()
            ->assertSee('Monthly trends')
            ->assertSee('Revenue')
            ->assertSee('$25.00')
            ->assertSee('Doctor load');
    }

    public function test_admin_home_renders_with_recent_appointments(): void
    {
        $this->makeAppointment(['appointment_date' => now()->toDateString()]);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.home'))
            ->assertOk()
            ->assertSee('Monthly trends')
            ->assertSee('appointments today');
    }
}
