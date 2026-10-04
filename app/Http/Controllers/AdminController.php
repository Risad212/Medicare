<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BlogComment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Prescription;
use App\Modules\Analytics\Services\AnalyticsService;
use App\Modules\Lab\Models\LabOrder;
use App\Support\AdminNavigation;
use App\Support\Module;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     */
    public function index(): Response
    {
        $totalDoctors = Doctor::count();
        $totalAppointments = Appointment::count();
        $totalPatients = Patient::count();
        $pendingComments = BlogComment::where('status', 0)->count();

        $pendingAppointments = Appointment::where('status', 0)->count();
        $confirmedAppointments = Appointment::where('status', 1)->count();
        $completedAppointments = Appointment::where('status', 2)->count();
        $cancelledAppointments = Appointment::where('status', 3)->count();
        $todayAppointments = Appointment::whereDate('appointment_date', now()->toDateString())->count();
        $activeDoctorsToday = Doctor::whereHas('appointments', function ($q) {
            $q->whereDate('appointment_date', now()->toDateString());
        })->count();

        // Today's queue: pending first so the morning register surfaces
        // what needs action, then by slot order.
        $recentAppointments = Appointment::with(['doctor', 'timeSlot'])
            ->whereDate('appointment_date', now()->toDateString())
            ->orderByRaw('CASE WHEN status = 0 THEN 0 ELSE 1 END')
            ->orderBy('time_slot_id')
            ->take(6)
            ->get();

        $topDoctors = Doctor::withCount('appointments as appointment_count')
            ->orderByDesc('appointment_count')
            ->take(4)
            ->get();

        // Doctors actually on duty today (non-cancelled bookings today).
        $dutyDoctors = Doctor::withCount(['appointments as appointment_count' => function ($q) {
            $q->whereDate('appointment_date', now()->toDateString())
                ->where('status', '!=', 3);
        }])
            ->whereHas('appointments', function ($q) {
                $q->whereDate('appointment_date', now()->toDateString());
            })
            ->orderByDesc('appointment_count')
            ->take(4)
            ->get();

        $recentComments = BlogComment::with('blog')
            ->where('status', 0)
            ->latest()
            ->take(3)
            ->get();

        // ---------- Analytics ----------

        $revenueAllTime = 0.0;
        $revenueThisMonth = 0.0;
        $labOrdersThisMonth = 0;
        $labOrdersPending = 0;

        if (Module::enabled('lab')) {
            $completedLabOrders = LabOrder::where('status', 'completed');

            $revenueAllTime = (float) $completedLabOrders->clone()->sum('total');
            $revenueThisMonth = (float) $completedLabOrders->clone()
                ->whereBetween('created_at', [now()->startOfMonth(), now()])
                ->sum('total');
            $labOrdersThisMonth = LabOrder::whereBetween('created_at', [now()->startOfMonth(), now()])->count();
            $labOrdersPending = LabOrder::where('status', 'pending')->count();
        }

        // Invoice revenue: only "paid" invoices count as collected; "pending"
        // invoices are outstanding. Filters mirror InvoiceController's statuses.
        $invoicePaidThisMonth = (float) Invoice::where('status', 'paid')
            ->whereBetween('paid_at', [now()->startOfMonth(), now()])
            ->sum('total');
        $invoicePaidAllTime = (float) Invoice::where('status', 'paid')->sum('total');
        $invoiceOutstanding = (float) Invoice::where('status', 'pending')->sum('total');
        $invoicesPendingCount = Invoice::where('status', 'pending')->count();

        // Last 6 months of appointments vs lab orders (oldest -> newest).
        $appointmentsByMonth = Appointment::where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['created_at'])
            ->groupBy(fn ($a) => $a->created_at->format('Y-m'));
        $labOrdersByMonth = collect();

        if (Module::enabled('lab')) {
            $labOrdersByMonth = LabOrder::where('created_at', '>=', now()->subMonths(5)->startOfMonth())
                ->get(['created_at'])
                ->groupBy(fn ($o) => $o->created_at->format('Y-m'));
        }

        $monthlyAppointments = collect(range(5, 0))->map(
            fn ($i) => $appointmentsByMonth->get(now()->subMonths($i)->format('Y-m'), collect())->count()
        )->values()->all();

        $monthlyLabOrders = collect(range(5, 0))->map(
            fn ($i) => $labOrdersByMonth->get(now()->subMonths($i)->format('Y-m'), collect())->count()
        )->values()->all();

        $monthLabels = collect(range(5, 0))->map(
            fn ($i) => now()->subMonths($i)->format('M')
        )->values()->all();

        // Per-doctor load: non-cancelled bookings, most booked first.
        $doctorLoad = Doctor::withCount(['appointments as appointment_count' => function ($q) {
            $q->where('status', '!=', 3);
        }])
            ->orderByDesc('appointment_count')
            ->take(6)
            ->get();

        // Welly stat: hospital earning = collected invoices + completed lab revenue.
        $hospitalEarning = (float) (Invoice::where('status', 'paid')->sum('total'));

        if (Module::enabled('lab')) {
            $hospitalEarning += (float) (LabOrder::where('status', 'completed')->sum('total'));
        }

        // Welly schedule rail: upcoming non-cancelled bookings grouped by day.
        $upcomingSchedule = Appointment::with(['doctor', 'timeSlot'])
            ->where('status', '!=', 3)
            ->where('appointment_date', '>=', now()->toDateString())
            ->orderBy('appointment_date')
            ->take(9)
            ->get()
            ->groupBy(fn ($a) => Carbon::parse($a->appointment_date)->format('l, F jS'));

        // Patient Percentage tabs: real status breakdowns per range
        // (Daily = today, Weekly = last 7 days, Monthly = last 30 days).
        $rangeStats = collect([
            'daily' => [now()->toDateString(), now()->toDateString()],
            'weekly' => [now()->subDays(6)->toDateString(), now()->toDateString()],
            'monthly' => [now()->subDays(29)->toDateString(), now()->toDateString()],
        ])->mapWithKeys(function ($range, $key) {
            [$from, $to] = $range;
            $rows = Appointment::whereBetween('appointment_date', [$from, $to])->get(['status']);
            $total = max(1, $rows->count());
            $pending = $rows->where('status', 0)->count();
            $recovered = $rows->where('status', 2)->count();
            $treating = $rows->where('status', 1)->count();

            return [$key => [
                'total' => $rows->count(),
                'new' => round($pending / $total * 100),
                'recovered' => round($recovered / $total * 100),
                'treating' => round($treating / $total * 100),
            ]];
        })->all();

        // Schedule calendar: browsable month (?cal=YYYY-MM) with real booking dots.
        $calMonth = now()->startOfMonth();
        $calParam = request('cal');
        if (is_string($calParam) && preg_match('/^\d{4}-\d{2}$/', $calParam)) {
            try {
                $parsed = Carbon::createFromFormat('Y-m', $calParam)->startOfMonth();
                if ($parsed && abs($parsed->diffInMonths(now()->startOfMonth())) <= 24) {
                    $calMonth = $parsed;
                }
            } catch (\Exception $e) {
            }
        }
        $calBookings = Appointment::where('status', '!=', 3)
            ->whereBetween('appointment_date', [
                $calMonth->copy()->startOfMonth()->toDateString(),
                $calMonth->copy()->endOfMonth()->toDateString(),
            ])
            ->get(['appointment_date'])
            ->groupBy(fn ($a) => Carbon::parse($a->appointment_date)->toDateString())
            ->map->count();

        // Lab work queue: oldest unprocessed orders first (lab dashboard feed).
        $pendingLabOrders = collect();

        if (Module::enabled('lab')) {
            $pendingLabOrders = LabOrder::with(['doctor', 'items.test'])
                ->whereIn('status', ['pending', 'in-progress'])
                ->oldest()
                ->take(6)
                ->get();
        }

        // Recent prescriptions feed (pharmacist dashboard feed).
        $recentPrescriptions = Prescription::with(['doctor'])
            ->latest()
            ->take(5)
            ->get();

        // Analytics module (app/Modules/Analytics/): computed only when the flag is on.
        // The ::class reference is a plain string until app() resolves it, so a
        // deleted module folder is never autoloaded while the flag is off.
        $analytics = Module::enabled('analytics')
            ? app(AnalyticsService::class)->data()
            : [];

        $role = (string) auth()->user()->role;
        $isAdmin = $role === 'admin';
        $isFrontDesk = in_array($role, ['admin', 'receptionist'], true);
        $isLabStaff = in_array($role, ['admin', 'lab-technician'], true) && Module::enabled('lab');
        $isPharmacyStaff = in_array($role, ['admin', 'pharmacist'], true);

        $calendarStart = $calMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $today = now()->toDateString();
        $calendar = collect(range(0, 41))->map(function ($offset) use ($calendarStart, $calMonth, $calBookings, $today) {
            $day = $calendarStart->copy()->addDays($offset);
            $date = $day->toDateString();

            return [
                'date' => $date,
                'day' => $day->day,
                'inMonth' => $day->month === $calMonth->month,
                'isToday' => $date === $today,
                'hasBookings' => $calBookings->get($date, 0) > 0,
            ];
        });

        $queue = $isFrontDesk ? $recentAppointments->map(fn (Appointment $appointment) => [
            'id' => $appointment->id,
            'patientName' => $appointment->patient_name,
            'phone' => $appointment->phone,
            'age' => $appointment->age,
            'doctorName' => $appointment->doctor->name ?? '—',
            'department' => $appointment->doctor->department ?? 'General',
            'date' => Carbon::parse($appointment->appointment_date)->format('M j'),
            'time' => $appointment->timeSlot->time ?? '—',
            'status' => (int) $appointment->status,
        ])->values() : [];

        $schedule = $isFrontDesk ? $upcomingSchedule->map(fn ($appointments, $dayLabel) => [
            'day' => $dayLabel,
            'appointments' => $appointments->take(2)->map(fn (Appointment $appointment) => [
                'id' => $appointment->id,
                'time' => $appointment->timeSlot->time ?? '—',
                'doctorName' => $appointment->doctor->name ?? '—',
                'patientName' => $appointment->patient_name,
                'status' => (int) $appointment->status,
            ])->values(),
        ])->values() : [];

        $analyticsData = $isAdmin && Module::enabled('analytics')
            ? [
                ...$analytics,
                'busiestDoctors' => collect($analytics['busiestDoctors'] ?? [])->map(fn ($doctor) => [
                    'name' => $doctor->name,
                    'appointmentCount' => $doctor->appointment_count,
                ])->values(),
            ]
            : null;

        return Inertia::render('Admin/Dashboard', [
            'metrics' => [
                'frontDesk' => $isFrontDesk ? [
                    'todayAppointments' => $todayAppointments,
                    'totalPatients' => $totalPatients,
                    'totalDoctors' => $totalDoctors,
                    'totalAppointments' => $totalAppointments,
                    'pendingAppointments' => $pendingAppointments,
                    'confirmedAppointments' => $confirmedAppointments,
                    'completedAppointments' => $completedAppointments,
                    'cancelledAppointments' => $cancelledAppointments,
                    'activeDoctorsToday' => $activeDoctorsToday,
                ] : null,
                'admin' => $isAdmin ? [
                    'hospitalEarning' => $hospitalEarning,
                    'pendingComments' => $pendingComments,
                    'revenueAllTime' => $revenueAllTime,
                    'revenueThisMonth' => $revenueThisMonth,
                    'invoicePaidThisMonth' => $invoicePaidThisMonth,
                    'invoicePaidAllTime' => $invoicePaidAllTime,
                    'invoiceOutstanding' => $invoiceOutstanding,
                    'invoicesPendingCount' => $invoicesPendingCount,
                    'monthlyAppointments' => $monthlyAppointments,
                    'monthlyLabOrders' => $monthlyLabOrders,
                    'monthLabels' => $monthLabels,
                    'rangeStats' => $rangeStats,
                    'topDoctors' => $topDoctors->take(5)->map(fn (Doctor $doctor) => [
                        'id' => $doctor->id,
                        'name' => $doctor->name,
                    ])->values(),
                    'comments' => $recentComments->map(fn (BlogComment $comment) => [
                        'comment' => Str::limit($comment->comment, 90),
                        'name' => $comment->name,
                        'blogTitle' => $comment->blog->title ?? '—',
                    ])->values(),
                    'analytics' => $analyticsData,
                ] : null,
                'laboratory' => $isLabStaff ? [
                    'ordersThisMonth' => $labOrdersThisMonth,
                    'ordersPending' => $labOrdersPending,
                    'pendingOrders' => $pendingLabOrders->map(fn (LabOrder $order) => [
                        'id' => $order->id,
                        'createdAt' => $order->created_at->format('M j'),
                        'doctorName' => $order->doctor->name ?? '—',
                        'patientName' => $order->patient_name,
                        'phone' => $order->phone ?? '—',
                        'testCount' => $order->items->count(),
                        'status' => $order->status,
                    ])->values(),
                ] : null,
                'pharmacy' => $isPharmacyStaff ? [
                    'prescriptions' => $recentPrescriptions->map(fn (Prescription $prescription) => [
                        'id' => $prescription->id,
                        'patientName' => $prescription->patient_name,
                        'doctorName' => $prescription->doctor->name ?? '—',
                        'createdAt' => $prescription->created_at->format('M j, Y'),
                    ])->values(),
                ] : null,
            ],
            'calendar' => $isFrontDesk ? [
                'month' => $calMonth->format('F Y'),
                'monthParam' => $calMonth->format('Y-m'),
                'todayMonth' => now()->format('Y-m'),
                'previousMonth' => $calMonth->copy()->subMonth()->format('Y-m'),
                'nextMonth' => $calMonth->copy()->addMonth()->format('Y-m'),
                'days' => $calendar,
            ] : null,
            'schedule' => $schedule,
            'queue' => $queue,
            'dutyDoctors' => $isFrontDesk ? $dutyDoctors->map(fn (Doctor $doctor) => [
                'name' => $doctor->name,
                'department' => $doctor->department ?? 'General',
                'appointmentCount' => $doctor->appointment_count,
                'active' => (bool) $doctor->status,
            ])->values() : [],
            'doctorLoad' => $isFrontDesk ? $doctorLoad->map(fn (Doctor $doctor) => [
                'name' => $doctor->name,
                'appointmentCount' => $doctor->appointment_count,
            ])->values() : [],
            'features' => [
                'lab' => Module::enabled('lab'),
                'analytics' => Module::enabled('analytics'),
            ],
            'routes' => AdminNavigation::routes(),
        ]);
    }
}
