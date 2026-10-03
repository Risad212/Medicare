<?php

namespace App\Services;

use App\Models\LabOrder;
use App\Models\LabReport;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LabReportService
{
    /**
     * Store an uploaded report file against a lab order.
     */
    public function store(LabOrder $order, array $validated, User $uploader): LabReport
    {
        return DB::transaction(function () use ($order, $validated, $uploader) {
            /** @var UploadedFile $file */
            $file = $validated['report_file'];

            $path = $file->store('lab_reports', 'public');

            return LabReport::create([
                'lab_order_id' => $order->id,
                'report_name' => $validated['report_name'],
                'file_path' => $path,
                'uploaded_by' => $uploader->id,
                'notes' => $validated['notes'] ?? null,
            ]);
        });
    }

    /**
     * Delete a report including its stored file.
     */
    public function delete(LabReport $report): void
    {
        DB::transaction(function () use ($report) {
            Storage::disk('public')->delete($report->file_path);
            $report->delete();
        });
    }
}
