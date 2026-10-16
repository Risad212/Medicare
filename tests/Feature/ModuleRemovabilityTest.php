<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\TimeSlot;
use App\Models\User;
use App\Support\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ModuleRemovabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_module_is_never_enabled(): void
    {
        $this->assertFalse(Module::enabled('does_not_exist'));
    }

    public function test_provider_list_skips_missing_classes(): void
    {
        $providers = require base_path('bootstrap/providers.php');

        $this->assertNotEmpty($providers);
        foreach ($providers as $provider) {
            $this->assertTrue(class_exists($provider), "Registered provider missing: {$provider}");
        }
    }

    public function test_disabled_modules_hide_routes_while_core_flows_pass(): void
    {
        config()->set('modules.language', false);
        config()->set('modules.google_calendar', false);
        config()->set('modules.zoom', false);
        config()->set('modules.lab', false);

        $this->get('/language/bn')->assertNotFound();

        [$doctorUser] = $this->makeDoctor();
        $this->actingAs($doctorUser)->get(route('doctor.calendar.index'))->assertNotFound();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.lab-orders.index'))->assertNotFound();

        $this->get('/')->assertOk();
        $this->get(route('appointment'))->assertOk();
        $this->get(route('contact'))->assertOk();
    }

    public function test_core_booking_and_dashboards_work_with_every_module_disabled(): void
    {
        foreach (array_keys(config('modules')) as $module) {
            config()->set("modules.{$module}", false);
        }

        Mail::fake();
        [, $doctor] = $this->makeDoctor();
        $slot = TimeSlot::create(['time' => '09:00 AM', 'status' => 1]);

        $this->post(route('appointment.store'), [
            'doctor_id' => $doctor->id,
            'patient_name' => 'Core Patient',
            'phone' => '+1 555 0100',
            'visit_type' => 1,
            'consultation_mode' => 'in_person',
            'appointment_date' => now()->addDays(2)->toDateString(),
            'time_slot_id' => $slot->id,
            'gender' => 1,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', ['patient_name' => 'Core Patient']);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.home'))->assertOk();

        [$doctorUser] = $this->makeDoctor();
        $this->actingAs($doctorUser)->get(route('doctor.dashboard'))->assertOk();

        $patient = User::factory()->create(['role' => 'patient']);
        $this->actingAs($patient)->get(route('profile'))->assertOk();
    }

    public function test_lab_pdf_download_404s_when_lab_disabled(): void
    {
        config()->set('modules.lab', false);

        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($patient)->get(route('profile.lab-orders.pdf', 1))->assertNotFound();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.invoices.index'))->assertOk();
    }

    /**
     * Physically move each module directory aside in turn and prove, in a
     * clean subprocess, that the app still boots and the module routes are
     * gone. Follows the documented removal procedure (move + composer
     * dump-autoload): optimized installs keep a class map, and a stale map
     * fatals the boot when a removed file is first referenced. Subprocess
     * isolation matters: class_exists() stays true for classes already
     * loaded in this test process.
     */
    #[DataProvider('removableModules')]
    public function test_physically_removed_module_still_boots(string $module, string $routeFragment): void
    {
        $path = base_path("app/Modules/{$module}");
        $this->assertDirectoryExists($path);
        $backup = base_path("app/Modules/__removal_backup_{$module}");

        if (is_dir($backup)) {
            $this->fail("Stale backup dir exists: {$backup}");
        }

        rename($path, $backup);
        try {
            $this->dumpAutoload();

            $boot = Process::env(['MODULE_ZOOM' => 'true', 'MODULE_GOOGLE_CALENDAR' => 'true'])
                ->run(PHP_BINARY.' artisan about --only=environment');

            $this->assertSame(
                0,
                $boot->exitCode(),
                "App failed to boot with {$module} removed: ".$boot->errorOutput()
            );

            $routes = Process::env(['MODULE_ZOOM' => 'true', 'MODULE_GOOGLE_CALENDAR' => 'true'])
                ->run(PHP_BINARY.' artisan route:list');

            $this->assertSame(0, $routes->exitCode(), 'route:list failed with '.$module.' removed.');
            $this->assertStringNotContainsString(
                $routeFragment,
                $routes->output(),
                "Routes for removed module [{$module}] are still registered."
            );
        } finally {
            rename($backup, $path);
            $this->dumpAutoload();
        }

        $this->assertDirectoryExists($path);
    }

    public static function removableModules(): array
    {
        // Fragments must be module-only: the core profile keeps a
        // /profile/lab-orders/... route even with the lab module gone.
        return [
            'google calendar' => ['GoogleCalendar', 'calendar'],
            'zoom' => ['Zoom', 'telehealth'],
            'lab' => ['Lab', 'lab-tests'],
            'publishing' => ['Publishing', 'admin/blogs'],
            'directory' => ['Directory', 'admin/sliders'],
            'billing' => ['Billing', 'admin/invoices'],
            'prescriptions' => ['Prescriptions', 'doctor/prescriptions'],
        ];
    }

    private function makeDoctor(): array
    {
        $user = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Removal Doctor',
            'slug' => 'removal-doctor-'.uniqid(),
            'user_id' => $user->id,
            'status' => 1,
        ]);

        return [$user, $doctor];
    }

    private function dumpAutoload(): void
    {
        $result = Process::path(base_path())->run('composer dump-autoload -q --no-scripts');

        $this->assertSame(0, $result->exitCode(), 'composer dump-autoload failed: '.$result->errorOutput());
    }
}
