<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\LabOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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

    public function test_admin_home_shows_invoice_revenue_kpis(): void
    {
        Invoice::create([
            'invoice_no' => 'INV-'.Str::random(6),
            'patient_name' => 'Paid Patient',
            'subtotal' => 100.00,
            'total' => 100.00,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        Invoice::create([
            'invoice_no' => 'INV-'.Str::random(6),
            'patient_name' => 'Pending Patient',
            'subtotal' => 50.00,
            'total' => 50.00,
            'status' => 'pending',
        ]);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.home'))
            ->assertOk()
            ->assertSee('Invoice revenue')
            ->assertSee('Collected this month')
            ->assertSee('$100.00')
            ->assertSee('Collected all time')
            ->assertSee('Outstanding')
            ->assertSee('$50.00')
            ->assertSee('Unpaid invoices');
    }
}
