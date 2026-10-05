<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminExportDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_csv_export_remains_a_download_and_escapes_formula_cells(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Patient::create([
            'name' => '=HYPERLINK("https://example.test")',
            'email' => 'patient@example.test',
            'phone' => '+15550100',
        ]);

        $response = $this->actingAs($admin)->get('/admin/exports/patients');

        $response->assertDownload('patients-'.now()->format('Y-m-d').'.csv');
        $this->assertStringContainsString("'=HYPERLINK", $response->streamedContent());
    }
}
