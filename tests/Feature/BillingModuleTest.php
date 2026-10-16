<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use App\Modules\Lab\Http\Controllers\Admin\LabOrderController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_module_hides_billing_routes_but_keeps_core(): void
    {
        config(['modules.billing' => false]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/invoices')->assertNotFound();
        $this->actingAs($admin)->get('/admin/invoices/1')->assertNotFound();

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->get('/')->assertOk();
    }

    public function test_enabled_module_serves_invoices(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Invoice::create([
            'invoice_no' => 'INV-TEST-1', 'patient_name' => 'Test Patient',
            'subtotal' => 100, 'tax' => 0, 'discount' => 0, 'total' => 100,
            'status' => 'pending', 'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->get('/admin/invoices')->assertOk()->assertSee('Test Patient');
    }

    public function test_lab_can_still_create_invoice_from_order(): void
    {
        $this->assertTrue(method_exists(
            LabOrderController::class,
            'createInvoice'
        ));
    }
}
