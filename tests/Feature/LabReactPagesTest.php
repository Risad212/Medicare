<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\User;
use App\Modules\Lab\Models\LabOrder;
use App\Modules\Lab\Models\LabTest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LabReactPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_lab_test_register_and_forms_render_react_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $test = LabTest::create([
            'name' => 'Complete Blood Count',
            'category' => 'Hematology',
            'description' => 'Measures blood cells.',
            'price' => 45,
            'normal_range' => '13–17',
            'unit' => 'g/dL',
            'status' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin/lab-tests?search=Hematology')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Lab/Tests/Index')
                ->where('labTests.data.0.name', 'Complete Blood Count')
                ->where('filters.search', 'Hematology')
            );

        $this->get('/admin/lab-tests/create')
            ->assertInertia(fn ($page) => $page->component('Admin/Lab/Tests/Create'));

        $this->get("/admin/lab-tests/{$test->id}/edit")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Lab/Tests/Edit')
                ->where('labTest.normalRange', '13–17')
            );
    }

    public function test_lab_test_crud_preserves_validation_and_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/lab-tests', [
            'name' => 'Urinalysis',
            'category' => 'Urine',
            'price' => 22.50,
            'status' => '0',
        ])->assertRedirect('/admin/lab-tests');

        $test = LabTest::where('name', 'Urinalysis')->firstOrFail();
        $this->assertFalse($test->status);

        $this->put("/admin/lab-tests/{$test->id}", [
            'name' => 'Complete urinalysis',
            'category' => 'Urine',
            'price' => 24,
            'status' => '1',
        ])->assertRedirect('/admin/lab-tests');
        $this->assertTrue($test->fresh()->status);

        $this->delete("/admin/lab-tests/{$test->id}")->assertRedirect('/admin/lab-tests');
        $this->assertDatabaseMissing('lab_tests', ['id' => $test->id]);
    }

    public function test_lab_order_react_pages_keep_results_reports_and_status_actions(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $doctor = Doctor::create([
            'name' => 'Dr Lab',
            'slug' => 'dr-lab-'.uniqid(),
            'status' => 1,
        ]);
        $test = LabTest::create([
            'name' => 'Blood panel',
            'price' => 75,
            'normal_range' => '12–16',
            'unit' => 'g/dL',
            'status' => true,
        ]);
        $order = LabOrder::create([
            'doctor_id' => $doctor->id,
            'patient_name' => 'Patient Lab',
            'phone' => '555-0110',
            'priority' => 'urgent',
            'status' => 'pending',
            'total' => 75,
        ]);
        $item = $order->items()->create(['lab_test_id' => $test->id, 'price' => 75]);

        $this->actingAs($admin)
            ->get('/admin/lab-orders?search=Patient&status=pending')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Lab/Orders/Index')
                ->where('orders.data.0.patientName', 'Patient Lab')
                ->where('orders.data.0.tests.0', 'Blood panel')
                ->where('filters.status', 'pending')
            );

        $this->get("/admin/lab-orders/{$order->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Lab/Orders/Show')
                ->where('order.patientName', 'Patient Lab')
                ->where('order.items.0.testName', 'Blood panel')
                ->has('routes.reportsStore')
            );

        $this->put("/admin/lab-order-items/{$item->id}/result", ['result' => '14.2 g/dL'])
            ->assertRedirect("/admin/lab-orders/{$order->id}");
        $this->assertSame('14.2 g/dL', $item->fresh()->result);

        $this->from("/admin/lab-orders/{$order->id}")
            ->put("/admin/lab-orders/{$order->id}/status", ['status' => 'cancelled'])
            ->assertRedirect("/admin/lab-orders/{$order->id}");
        $this->assertSame('cancelled', $order->fresh()->status);

        $this->from("/admin/lab-orders/{$order->id}")
            ->post("/admin/lab-orders/{$order->id}/reports", [
                'report_name' => 'Blood panel result',
                'notes' => 'Reviewed by the lab.',
                'report_file' => UploadedFile::fake()->create('result.pdf', 120, 'application/pdf'),
            ])
            ->assertRedirect("/admin/lab-orders/{$order->id}");

        $report = $order->reports()->firstOrFail();
        Storage::disk('public')->assertExists($report->file_path);

        $this->actingAs($admin)->delete("/admin/lab-reports/{$report->id}")
            ->assertRedirect("/admin/lab-orders/{$order->id}");
        Storage::disk('public')->assertMissing($report->file_path);
    }
}
