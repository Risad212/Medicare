<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use App\Support\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffModuleGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_registry_merges_enabled_module_grants(): void
    {
        $grants = Module::staffGrants();

        $this->assertContains('admin.medicines.index', $grants['pharmacist'] ?? []);
        $this->assertContains('admin.invoices.*', $grants['receptionist'] ?? []);
        $this->assertContains('admin.lab-orders.*', $grants['lab-technician'] ?? []);
        $this->assertContains('admin.prescriptions.index', $grants['pharmacist'] ?? []);
    }

    public function test_disabled_module_grants_nothing(): void
    {
        config(['modules.pharmacy' => false, 'modules.billing' => false]);

        $grants = Module::staffGrants();

        $this->assertNotContains('admin.medicines.index', $grants['pharmacist'] ?? []);
        $this->assertNotContains('admin.invoices.*', $grants['receptionist'] ?? []);
    }

    public function test_receptionist_keeps_invoice_access_while_billing_on(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $invoice = Invoice::create([
            'invoice_no' => 'INV-GATE-1', 'patient_name' => 'Gate Patient',
            'subtotal' => 50, 'tax' => 0, 'discount' => 0, 'total' => 50,
            'status' => 'pending', 'created_by' => $admin->id,
        ]);

        $this->actingAs($receptionist)->get('/admin/invoices')->assertOk();
        $this->actingAs($receptionist)->delete('/admin/invoices/'.$invoice->id)->assertForbidden();
    }

    public function test_pharmacist_keeps_prescription_access_while_module_on(): void
    {
        $pharmacist = User::factory()->create(['role' => 'pharmacist']);

        $this->actingAs($pharmacist)->get('/admin/prescriptions')->assertOk();
        $this->actingAs($pharmacist)->get('/admin/invoices')->assertForbidden();
    }
}
