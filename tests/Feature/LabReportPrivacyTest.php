<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\User;
use App\Modules\Lab\Models\LabOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LabReportPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_file_is_stored_off_the_public_disk(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->makeOrder();

        $this->actingAs($admin)->post(route('admin.lab-orders.reports.store', $order), [
            'report_name' => 'Blood panel',
            'report_file' => UploadedFile::fake()->create('panel.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $report = $order->reports()->first();
        $this->assertNotNull($report);
        Storage::disk('local')->assertExists($report->file_path);
        Storage::disk('public')->assertMissing($report->file_path);
    }

    public function test_owner_can_download_but_direct_public_url_is_dead(): void
    {
        Storage::fake('local');
        $patient = User::factory()->create(['role' => 'patient']);
        $order = $this->makeOrder($patient);
        $report = $order->reports()->create([
            'report_name' => 'Panel',
            'file_path' => UploadedFile::fake()->create('panel.pdf', 100, 'application/pdf')
                ->store('lab_reports', 'local'),
            'uploaded_by' => $patient->id,
        ]);

        $this->actingAs($patient)
            ->get(route('profile.lab-reports.download', $report))
            ->assertOk();

        // No public-disk copy exists, so the static /storage URL can never serve it.
        $this->assertFalse(Storage::disk('public')->exists($report->file_path));
        $this->assertNotSame(200, $this->get('/storage/'.$report->file_path)->status());
    }

    public function test_stranger_cannot_download_someone_elses_report(): void
    {
        Storage::fake('local');
        $patient = User::factory()->create(['role' => 'patient']);
        $stranger = User::factory()->create(['role' => 'patient']);
        $order = $this->makeOrder($patient);
        $report = $order->reports()->create([
            'report_name' => 'Panel',
            'file_path' => UploadedFile::fake()->create('panel.pdf', 100, 'application/pdf')
                ->store('lab_reports', 'local'),
            'uploaded_by' => $patient->id,
        ]);

        $this->actingAs($stranger)
            ->get(route('profile.lab-reports.download', $report))
            ->assertForbidden();
    }

    private function makeOrder(?User $patient = null): LabOrder
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Privacy Doctor',
            'slug' => 'privacy-doctor-'.uniqid(),
            'user_id' => $doctorUser->id,
            'status' => 1,
        ]);

        return LabOrder::create([
            'doctor_id' => $doctor->id,
            'patient_name' => 'Privacy Patient',
            'status' => 'completed',
            'user_id' => $patient?->id,
        ]);
    }
}
