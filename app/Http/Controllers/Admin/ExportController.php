<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\LabOrder;
use App\Models\User;
use App\Services\CsvExport;

class ExportController extends Controller
{
    /**
     * Export all appointments as a CSV file.
     */
    public function appointments(CsvExport $csv)
    {
        $rows = Appointment::with('doctor', 'timeSlot')
            ->latest()
            ->cursor()
            ->map(fn ($appointment) => [
                $appointment->id,
                $appointment->patient_name,
                $appointment->phone ?? 'N/A',
                $appointment->email ?? 'N/A',
                $appointment->doctor->name ?? 'N/A',
                $appointment->appointment_date->format('Y-m-d'),
                $appointment->timeSlot->time ?? 'N/A',
                match ((int) $appointment->visit_type) {
                    1 => 'First Visit',
                    2 => 'Second Visit',
                    3 => 'Report Review',
                    default => 'N/A',
                },
                match ((int) $appointment->status) {
                    0 => 'Pending',
                    1 => 'Confirmed',
                    2 => 'Completed',
                    default => 'Cancelled',
                },
                $appointment->created_at->format('Y-m-d H:i:s'),
            ]);

        return $csv->stream(
            'appointments-'.now()->format('Y-m-d').'.csv',
            ['ID', 'Patient', 'Phone', 'Email', 'Doctor', 'Date', 'Time', 'Visit Type', 'Status', 'Created At'],
            $rows
        );
    }

    /**
     * Export all patients as a CSV file.
     */
    public function patients(CsvExport $csv)
    {
        $rows = User::where('role', 'patient')
            ->latest()
            ->cursor()
            ->map(fn ($patient) => [
                $patient->id,
                $patient->name,
                $patient->email,
                $patient->phone ?? 'N/A',
                $patient->created_at->format('Y-m-d H:i:s'),
            ]);

        return $csv->stream(
            'patients-'.now()->format('Y-m-d').'.csv',
            ['ID', 'Name', 'Email', 'Phone', 'Created At'],
            $rows
        );
    }

    /**
     * Export all lab orders as a CSV file.
     */
    public function labOrders(CsvExport $csv)
    {
        $rows = LabOrder::with('doctor', 'items.test', 'reports')
            ->latest()
            ->cursor()
            ->map(fn ($order) => [
                $order->id,
                $order->created_at->format('Y-m-d H:i:s'),
                $order->patient_name,
                $order->phone ?? 'N/A',
                $order->email ?? 'N/A',
                $order->doctor->name ?? 'N/A',
                $order->priority,
                $order->items->map(fn ($item) => $item->test->name ?? 'Removed test')->implode(' | '),
                number_format((float) $order->total, 2),
                $order->status,
                $order->reports->count(),
            ]);

        return $csv->stream(
            'lab-orders-'.now()->format('Y-m-d').'.csv',
            ['ID', 'Created At', 'Patient', 'Phone', 'Email', 'Doctor', 'Priority', 'Tests', 'Total', 'Status', 'Report Count'],
            $rows
        );
    }
}
