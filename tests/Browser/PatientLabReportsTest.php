<?php

namespace Tests\Browser;

use App\Models\LabOrder;
use App\Models\LabOrderItem;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Concerns\CreatesModels;
use Tests\DuskTestCase;

class PatientLabReportsTest extends DuskTestCase
{
    use CreatesModels;
    use DatabaseMigrations;

    public function test_patient_sees_completed_lab_order_on_profile(): void
    {
        $doctor = $this->makeDoctor();
        $test = $this->makeLabTest(['name' => 'E2E Glucose Fasting']);
        $patient = $this->makeUser(['name' => 'Sam Patient', 'email' => 'e2e-patient@example.com']);

        $order = LabOrder::create([
            'doctor_id' => $doctor->id,
            'user_id' => $patient->id,
            'patient_name' => $patient->name,
            'phone' => '01711-111222',
            'email' => $patient->email,
            'status' => 'completed',
            'total' => $test->price,
        ]);

        LabOrderItem::create([
            'lab_order_id' => $order->id,
            'lab_test_id' => $test->id,
            'price' => $test->price,
        ]);

        $this->browse(function (Browser $browser) use ($patient, $order) {
            $browser->loginAs($patient->id)
                ->visit('/profile')
                ->click('#lab-tab')
                ->waitForText('Lab Request #'.$order->id)
                ->assertSee('E2E Glucose Fasting');
        });
    }
}
