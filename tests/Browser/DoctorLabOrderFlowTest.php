<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Concerns\CreatesModels;
use Tests\DuskTestCase;

class DoctorLabOrderFlowTest extends DuskTestCase
{
    use CreatesModels;
    use DatabaseMigrations;

    public function test_doctor_creates_a_lab_order_through_the_ui(): void
    {
        $doctor = $this->makeDoctor();
        $this->makeLabTest(['name' => 'E2E Blood Test']);

        $this->browse(function (Browser $browser) use ($doctor) {
            $browser->loginAs($doctor->user_id)
                ->visit('/doctor/lab-orders/create')
                ->type('patient_name', 'Jordan Smith')
                ->type('#patient_phone', '01711-998877')
                ->check('.test-checkbox')
                ->press('Create Lab Request')
                ->waitForText('Lab order created successfully.')
                ->assertSee('Lab request #1');
        });
    }
}
