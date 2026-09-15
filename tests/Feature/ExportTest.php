<?php

namespace Tests\Feature;

use App\Models\LabOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    public function test_appointments_export_returns_csv_with_data(): void
    {
        $appointment = $this->makeAppointment(['patient_name' => 'Export Alice']);

        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)
            ->get(route('admin.exports.appointments'))
            ->assertOk();

        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Export Alice', $response->streamedContent());
        $this->assertStringContainsString($appointment->appointment_date->format('Y-m-d'), $response->streamedContent());
    }

    public function test_patients_export_returns_csv(): void
    {
        $this->makeUser(['name' => 'Export Patient', 'email' => 'export@example.com']);

        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)
            ->get(route('admin.exports.patients'))
            ->assertOk();

        $this->assertStringContainsString('Export Patient', $response->streamedContent());
        $this->assertStringContainsString('export@example.com', $response->streamedContent());
    }

    public function test_lab_orders_export_returns_csv(): void
    {
        $doctor = $this->makeDoctor();
        $test = $this->makeLabTest(['name' => 'Export Test Name']);

        $order = LabOrder::create([
            'doctor_id' => $doctor->id,
            'patient_name' => 'Export Order Patient',
            'priority' => 'normal',
            'status' => 'completed',
            'total' => $test->price,
        ]);

        $order->items()->create([
            'lab_test_id' => $test->id,
            'price' => $test->price,
        ]);

        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)
            ->get(route('admin.exports.lab-orders'))
            ->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('Export Order Patient', $content);
        $this->assertStringContainsString('Export Test Name', $content);
    }

    public function test_exports_are_admin_only(): void
    {
        $this->actingAs($this->makeUser())
            ->get(route('admin.exports.appointments'))
            ->assertRedirect('/login');
    }
}
