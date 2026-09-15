<?php

namespace Tests\Feature;

use App\Models\LabOrder;
use App\Models\LabReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Storage;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class AdminLabOrdersTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): LabOrder
    {
        $doctor = $overrides['doctor_id'] ?? $this->makeDoctor()->id;
        $test = $this->makeLabTest();

        $order = LabOrder::create(array_merge([
            'doctor_id' => $doctor,
            'patient_name' => 'Dana Patient',
            'phone' => '01710-111111',
            'priority' => 'normal',
            'status' => 'pending',
            'total' => $test->price,
        ], $overrides));

        $order->items()->create([
            'lab_test_id' => $test->id,
            'price' => $test->price,
        ]);

        return $order;
    }

    public function test_admin_can_view_and_filter_lab_orders(): void
    {
        $this->makeOrder(['patient_name' => 'Alice A']);
        $this->makeOrder(['patient_name' => 'Bob B', 'status' => 'completed']);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.lab-orders.index'))
            ->assertOk()
            ->assertSee('Alice A')
            ->assertSee('Bob B');

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.lab-orders.index', ['status' => 'completed']))
            ->assertOk()
            ->assertSee('Bob B')
            ->assertDontSee('Alice A');
    }

    public function test_admin_can_update_order_status(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.lab-orders.status', $order->id), ['status' => 'in-progress'])
            ->assertRedirect();

        $this->assertDatabaseHas('lab_orders', [
            'id' => $order->id,
            'status' => 'in-progress',
        ]);
    }

    public function test_admin_can_upload_report_and_file_is_stored(): void
    {
        Storage::fake('public');

        $order = $this->makeOrder();

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.lab-orders.reports.store', $order->id), [
                'report_name' => 'CBC Report',
                'notes' => 'All normal.',
                'report_file' => UploadedFile::fake()->create('cbc.pdf', 200, 'application/pdf'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lab_reports', [
            'lab_order_id' => $order->id,
            'report_name' => 'CBC Report',
            'notes' => 'All normal.',
        ]);

        $report = LabReport::where('lab_order_id', $order->id)->firstOrFail();
        Storage::disk('public')->assertExists($report->file_path);
    }

    public function test_report_upload_rejects_non_allowed_files(): void
    {
        Storage::fake('public');

        $order = $this->makeOrder();

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.lab-orders.reports.store', $order->id), [
                'report_name' => 'Bad Report',
                'report_file' => UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload'),
            ])
            ->assertSessionHasErrors('report_file');

        $this->assertDatabaseCount('lab_reports', 0);
    }

    public function test_admin_can_save_item_result(): void
    {
        $order = $this->makeOrder();
        $item = $order->items->first();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.lab-order-items.result', $item->id), ['result' => '5.2'])
            ->assertRedirect();

        $this->assertDatabaseHas('lab_order_items', [
            'id' => $item->id,
            'result' => '5.2',
        ]);
    }

    public function test_admin_can_delete_report_and_removes_file(): void
    {
        Storage::fake('public');

        $order = $this->makeOrder();
        $path = UploadedFile::fake()->create('report.pdf', 200, 'application/pdf')->store('lab_reports', 'public');
        $report = LabReport::create([
            'lab_order_id' => $order->id,
            'report_name' => 'Old Report',
            'file_path' => $path,
            'uploaded_by' => $this->makeAdmin()->id,
        ]);

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.lab-reports.destroy', $report->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('lab_reports', ['id' => $report->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_patient_guest_cannot_access_admin_lab_orders(): void
    {
        $this->actingAs($this->makeUser())
            ->get(route('admin.lab-orders.index'))
            ->assertRedirect('/login');
    }

    public function test_download_report_returns_file(): void
    {
        Storage::fake('public');

        $order = $this->makeOrder();
        $path = UploadedFile::fake()->create('result.pdf', 100, 'application/pdf')->store('lab_reports', 'public');
        $report = LabReport::create([
            'lab_order_id' => $order->id,
            'report_name' => 'Result',
            'file_path' => $path,
            'uploaded_by' => $this->makeAdmin()->id,
        ]);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.lab-reports.download', $report->id))
            ->assertOk();
    }
}
