<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingRecordsReactPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_list_and_details_render_react_pages(): void
    {
        config(['modules.lab' => false]);
        $admin = User::factory()->create(['role' => 'admin']);
        $invoice = Invoice::create([
            'invoice_no' => 'INV-2026-0001',
            'patient_name' => 'Jordan Patient',
            'phone' => '555-0100',
            'email' => 'jordan@example.test',
            'subtotal' => 100,
            'tax' => 5,
            'discount' => 10,
            'total' => 95,
            'status' => 'pending',
            'created_by' => $admin->id,
        ]);
        $invoice->items()->create([
            'description' => 'Consultation',
            'quantity' => 2,
            'unit_price' => 50,
            'line_total' => 100,
        ]);

        $this->actingAs($admin)
            ->get('/admin/invoices?status=pending&search=Jordan')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Invoices/Index')
                ->where('invoices.data.0.number', 'INV-2026-0001')
                ->where('invoices.total', 1)
                ->where('filters.status', 'pending')
                ->where('features.lab', false)
            );

        $this->get("/admin/invoices/{$invoice->id}")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Invoices/Show')
                ->where('invoice.patientName', 'Jordan Patient')
                ->where('invoice.items.0.description', 'Consultation')
                ->where('invoice.total', '95.00')
            );
    }

    public function test_invoice_status_update_preserves_existing_billing_workflow(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $invoice = Invoice::create([
            'invoice_no' => 'INV-2026-0002',
            'patient_name' => 'Taylor Patient',
            'total' => 75,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->from("/admin/invoices/{$invoice->id}")
            ->patch("/admin/invoices/{$invoice->id}/status", ['status' => 'paid'])
            ->assertRedirect("/admin/invoices/{$invoice->id}");

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertNotNull($invoice->fresh()->paid_at);
    }

    public function test_prescription_list_and_details_render_react_pages_without_pharmacy_module(): void
    {
        config(['modules.pharmacy' => false]);
        $admin = User::factory()->create(['role' => 'admin']);
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Dr Records',
            'slug' => 'dr-records-'.uniqid(),
            'user_id' => $doctorUser->id,
            'status' => 1,
        ]);
        $prescription = Prescription::create([
            'doctor_id' => $doctor->id,
            'patient_name' => 'Morgan Patient',
            'age' => 38,
            'gender' => 1,
            'phone' => '555-0188',
            'diagnosis' => 'Seasonal allergy',
            'follow_up_date' => '2026-11-05',
        ]);
        $prescription->items()->create([
            'medicine_name' => 'Antihistamine',
            'dosage' => '10 mg',
            'frequency' => 'Once daily',
            'quantity' => '7',
        ]);

        $this->actingAs($admin)
            ->get('/admin/prescriptions?search=Morgan')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Prescriptions/Index')
                ->where('prescriptions.data.0.patientName', 'Morgan Patient')
                ->where('prescriptions.total', 1)
            );

        $this->get("/admin/prescriptions/{$prescription->id}")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Prescriptions/Show')
                ->where('prescription.patientName', 'Morgan Patient')
                ->where('prescription.genderLabel', 'Male')
                ->where('prescription.items.0.medicineName', 'Antihistamine')
                ->where('features.pharmacy', false)
                ->has('medicines', 0)
            );
    }
}
