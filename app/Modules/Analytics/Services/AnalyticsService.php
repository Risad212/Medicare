<?php

namespace App\Modules\Analytics\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\User;

/**
 * plan.md #3: dashboard analytics. Aggregate SQL only, no N+1.
 * Called by AdminController only when the 'analytics' module flag is on.
 */
class AnalyticsService
{
    public function data(): array
    {
        $monthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        $appointmentsThisMonth = Appointment::whereBetween('created_at', [$monthStart, now()])->count();
        $appointmentsLastMonth = Appointment::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $appointmentMonthChange = $appointmentsLastMonth > 0
            ? round(($appointmentsThisMonth - $appointmentsLastMonth) / $appointmentsLastMonth * 100)
            : ($appointmentsThisMonth > 0 ? 100 : 0);

        $statusRows = Appointment::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status')->all();
        $statusBreakdown = [
            'pending' => (int) ($statusRows[0] ?? 0),
            'approved' => (int) ($statusRows[1] ?? 0),
            'completed' => (int) ($statusRows[2] ?? 0),
            'cancelled' => (int) ($statusRows[3] ?? 0),
        ];

        $busiestDoctors = Doctor::withCount('appointments as appointment_count')
            ->orderByDesc('appointment_count')
            ->take(5)
            ->get(['id', 'name']);

        $dailyRows = Appointment::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->all();
        $trendLabels = [];
        $trendCounts = [];
        foreach (range(29, 0) as $d) {
            $day = now()->subDays($d)->format('Y-m-d');
            $trendLabels[] = now()->subDays($d)->format('M j');
            $trendCounts[] = (int) ($dailyRows[$day] ?? 0);
        }

        return [
            'appointmentsThisMonth' => $appointmentsThisMonth,
            'appointmentsLastMonth' => $appointmentsLastMonth,
            'appointmentMonthChange' => $appointmentMonthChange,
            'statusBreakdown' => $statusBreakdown,
            'busiestDoctors' => $busiestDoctors,
            'trendLabels' => $trendLabels,
            'trendCounts' => $trendCounts,
            'totalRegisteredPatients' => User::where('role', 'patient')->count(),
            'newPatientsThisMonth' => User::where('role', 'patient')->whereBetween('created_at', [$monthStart, now()])->count(),
            'invoicePaidLastMonth' => (float) Invoice::where('status', 'paid')
                ->whereBetween('paid_at', [$lastMonthStart, $lastMonthEnd])
                ->sum('total'),
        ];
    }
}
