<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_correct_aggregates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Dr Stats',
            'slug' => 'dr-stats-'.uniqid(),
            'user_id' => $doctorUser->id,
            'status' => 1,
        ]);
        User::factory()->create(['role' => 'patient']);
        User::factory()->create(['role' => 'patient', 'created_at' => now()->subMonth()]);

        // 2 this month (pending + completed), 1 last month (cancelled).
        $a1 = $this->makeAppointment($doctor->id, 0);
        $a2 = $this->makeAppointment($doctor->id, 2);
        $old = $this->makeAppointment($doctor->id, 3);
        Appointment::whereKey($old->id)->update(['created_at' => now()->subMonth()]);

        Invoice::create([
            'invoice_no' => 'INV-1',
            'patient_name' => 'Pays Bills',
            'subtotal' => 100,
            'tax' => 0,
            'discount' => 0,
            'total' => 100,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $analytics = $response->viewData('analytics');
        $this->assertSame(2, $analytics['appointmentsThisMonth']);
        $this->assertSame(1, $analytics['appointmentsLastMonth']);
        $this->assertSame([
            'pending' => 1,
            'approved' => 0,
            'completed' => 1,
            'cancelled' => 1,
        ], $analytics['statusBreakdown']);
        $this->assertSame(2, $analytics['totalRegisteredPatients']);
        $this->assertSame(1, $analytics['newPatientsThisMonth']);
        $response->assertSee('Dr Stats', false);
        $response->assertSee('analyticsTrend', false);
        $response->assertSee('analyticsStatus', false);

        // Trend covers exactly the last 30 days of bookings.
        $trend = $analytics['trendCounts'];
        $this->assertCount(30, $trend);
        $expected = Appointment::where('created_at', '>=', now()->subDays(29)->startOfDay())->count();
        $this->assertSame($expected, array_sum($trend));
        $this->assertSame(30, count($analytics['trendLabels']));
    }

    public function test_non_admin_cannot_see_analytics_section(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($patient)->get('/admin')->assertRedirect('/login');
    }

    public function test_disabled_module_hides_section_without_queries(): void
    {
        config()->set('modules.analytics', false);
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertSee('Dashboard', false); // core page intact
        $response->assertDontSee('analyticsTrend', false);
        $response->assertDontSee('analyticsStatus', false);
        $this->assertSame([], $response->viewData('analytics'));
    }

    private function makeAppointment(int $doctorId, int $status): Appointment
    {
        return Appointment::create([
            'doctor_id' => $doctorId,
            'patient_name' => 'Stat Patient',
            'gender' => 1,
            'phone' => '01700000000',
            'visit_type' => 1,
            'appointment_date' => now()->toDateString(),
            'status' => $status,
        ]);
    }
}
