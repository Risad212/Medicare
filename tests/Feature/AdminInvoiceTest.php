<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\LabOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class AdminInvoiceTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    private function makeCompletedOrder(): LabOrder
    {
        $doctor = $this->makeDoctor();
        $testA = $this->makeLabTest(['name' => 'CBC', 'price' => 20.50]);
        $testB = $this->makeLabTest(['name' => 'Lipid', 'price' => 15.00]);

        $order = LabOrder::create([
            'doctor_id' => $doctor->id,
            'patient_name' => 'Invoice Alice',
            'phone' => '01711-555555',
            'email' => 'alice@example.com',
            'priority' => 'normal',
            'status' => 'completed',
            'total' => 35.50,
        ]);

        $order->items()->create(['lab_test_id' => $testA->id, 'price' => 20.50]);
        $order->items()->create(['lab_test_id' => $testB->id, 'price' => 15.00]);

        return $order;
    }

    public function test_admin_can_create_invoice_from_completed_order(): void
    {
        $order = $this->makeCompletedOrder();

        $response = $this->actingAs($this->makeAdmin())
            ->post(route('admin.invoices.create-from-order', $order->id))
            ->assertRedirect();

        $invoice = Invoice::where('lab_order_id', $order->id)->firstOrFail();

        $this->assertSame('INV-'.now()->year.'-0001', $invoice->invoice_no);
        $this->assertEquals(35.50, $invoice->subtotal);
        $this->assertEquals(35.50, $invoice->total);
        $this->assertSame('pending', $invoice->status);
        $this->assertSame('Invoice Alice', $invoice->patient_name);
        $this->assertCount(2, $invoice->items);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'description' => 'CBC',
            'line_total' => 20.50,
        ]);
        $this->assertSame($invoice->id, $order->fresh()->invoice->id);
    }

    public function test_invoice_only_allowed_for_completed_orders(): void
    {
        $doctor = $this->makeDoctor();
        $test = $this->makeLabTest();

        $order = LabOrder::create([
            'doctor_id' => $doctor->id,
            'patient_name' => 'Pending Alice',
            'priority' => 'normal',
            'status' => 'pending',
            'total' => $test->price,
        ]);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.invoices.create-from-order', $order->id))
            ->assertStatus(422);

        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_invoice_prevents_duplicate_for_same_order(): void
    {
        $order = $this->makeCompletedOrder();

        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.invoices.create-from-order', $order->id))
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.invoices.create-from-order', $order->id))
            ->assertStatus(409);

        $this->assertDatabaseCount('invoices', 1);
    }

    public function test_admin_can_mark_invoice_as_paid_and_void(): void
    {
        $invoice = $this->makeInvoiceForAdmin();

        $this->actingAs($this->makeAdmin())
            ->patch(route('admin.invoices.status', $invoice->id), ['status' => 'paid'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNotNull($invoice->fresh()->paid_at);
        $this->assertSame('paid', $invoice->fresh()->status);

        $this->actingAs($this->makeAdmin())
            ->patch(route('admin.invoices.status', $invoice->id), ['status' => 'void'])
            ->assertRedirect();

        $this->assertNull($invoice->fresh()->paid_at);
        $this->assertSame('void', $invoice->fresh()->status);
    }

    public function test_admin_can_download_invoice_pdf(): void
    {
        $invoice = $this->makeInvoiceForAdmin();

        $response = $this->actingAs($this->makeAdmin())
            ->get(route('admin.invoices.pdf', $invoice->id))
            ->assertOk();

        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_invoice_index_filters_by_status_and_search(): void
    {
        $this->makeInvoiceForAdmin();
        $paid = $this->makeInvoiceForAdmin(['patient_name' => 'Second Patient']);
        $paid->update(['status' => 'paid', 'paid_at' => now()]);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.invoices.index', ['status' => 'paid']))
            ->assertOk()
            ->assertSee('Second Patient')
            ->assertDontSee('Invoice Alice');

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.invoices.index', ['search' => 'INV-']))
            ->assertOk();
    }

    public function test_guest_cannot_access_invoices(): void
    {
        $this->get(route('admin.invoices.index'))->assertRedirect('/login');
    }

    private function makeInvoiceForAdmin(array $overrides = []): Invoice
    {
        $order = $this->makeCompletedOrder();

        $invoice = Invoice::create(array_merge([
            'invoice_no' => 'INV-'.now()->year.'-M-'.Str::upper(Str::random(3)),
            'lab_order_id' => $order->id,
            'patient_name' => 'Invoice Alice',
            'phone' => '01711-555555',
            'email' => 'alice@example.com',
            'subtotal' => 35.50,
            'tax' => 0,
            'discount' => 0,
            'total' => 35.50,
            'status' => 'pending',
            'created_by' => $this->makeAdmin()->id,
        ], $overrides));

        $invoice->items()->create([
            'description' => 'CBC',
            'quantity' => 1,
            'unit_price' => 20.50,
            'line_total' => 20.50,
        ]);

        return $invoice;
    }
}
