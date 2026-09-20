<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Blog;
use App\Models\BlogComment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\LabOrder;
use App\Models\Patient;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Carbon;

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
     *
     * @return Renderable
     */
    public function index()
    {
        $totalDoctors = Doctor::count();
        $totalAppointments = Appointment::count();
        $totalPatients = Patient::count();
        $totalBlogs = Blog::count();
        $pendingComments = BlogComment::where('status', 0)->count();

        $pendingAppointments = Appointment::where('status', 0)->count();
        $confirmedAppointments = Appointment::where('status', 1)->count();
        $completedAppointments = Appointment::where('status', 2)->count();
        $cancelledAppointments = Appointment::where('status', 3)->count();
        $todayAppointments = Appointment::whereDate('appointment_date', now()->toDateString())->count();
        $activeDoctorsToday = Doctor::whereHas('appointments', function ($q) {
            $q->whereDate('appointment_date', now()->toDateString());
        })->count();

        // Last 7 days of bookings (oldest -> newest) for the trend sparkline.
        $weekByDay = Appointment::where('appointment_date', '>=', now()->subDays(6)->toDateString())
            ->where('appointment_date', '<=', now()->toDateString())
            ->get(['appointment_date'])
            ->groupBy(fn ($a) => Carbon::parse($a->appointment_date)->toDateString());
        $weekTrend = collect(range(6, 0))->map(
            fn ($d) => $weekByDay->get(now()->subDays($d)->toDateString(), collect())->count()
        )->values()->all();

        // Pending first so the morning queue surfaces what needs action,
        // then newest within each group.
        $recentAppointments = Appointment::with(['doctor', 'timeSlot'])
            ->orderByRaw('CASE WHEN status = 0 THEN 0 ELSE 1 END')
            ->latest()
            ->take(6)
            ->get();

        $topDoctors = Doctor::withCount('appointments as appointment_count')
            ->orderByDesc('appointment_count')
            ->take(4)
            ->get();

        $recentComments = BlogComment::with('blog')
            ->where('status', 0)
            ->latest()
            ->take(3)
            ->get();

        // ---------- Analytics ----------

        $completedLabOrders = LabOrder::where('status', 'completed');

        $revenueAllTime = (float) $completedLabOrders->clone()->sum('total');
        $revenueThisMonth = (float) $completedLabOrders->clone()
            ->whereBetween('created_at', [now()->startOfMonth(), now()])
            ->sum('total');
        $labOrdersThisMonth = LabOrder::whereBetween('created_at', [now()->startOfMonth(), now()])->count();
        $labOrdersPending = LabOrder::where('status', 'pending')->count();

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
        $labOrdersByMonth = LabOrder::where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['created_at'])
            ->groupBy(fn ($o) => $o->created_at->format('Y-m'));

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
        $hospitalEarning = (float) (Invoice::where('status', 'paid')->sum('total'))
            + (float) (LabOrder::where('status', 'completed')->sum('total'));

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

        return view('backend.home', compact(
            'totalDoctors',
            'totalAppointments',
            'totalPatients',
            'hospitalEarning',
            'upcomingSchedule',
            'rangeStats',
            'calMonth',
            'calBookings',
            'totalBlogs',
            'pendingComments',
            'pendingAppointments',
            'confirmedAppointments',
            'completedAppointments',
            'cancelledAppointments',
            'todayAppointments',
            'activeDoctorsToday',
            'weekTrend',
            'recentAppointments',
            'topDoctors',
            'recentComments',
            'revenueAllTime',
            'revenueThisMonth',
            'labOrdersThisMonth',
            'labOrdersPending',
            'invoicePaidThisMonth',
            'invoicePaidAllTime',
            'invoiceOutstanding',
            'invoicesPendingCount',
            'monthlyAppointments',
            'monthlyLabOrders',
            'monthLabels',
            'doctorLoad'
        ));
    }
}
