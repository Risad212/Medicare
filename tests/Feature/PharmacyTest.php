<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use App\Modules\Pharmacy\Models\Medicine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PharmacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_medicine_to_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/medicines', [
            'name' => 'Napa 500',
            'generic_name' => 'Paracetamol',
            'unit' => 'tablet',
            'stock_quantity' => 100,
            'unit_price' => 2.50,
            'low_stock_threshold' => 10,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('medicines', ['name' => 'Napa 500', 'stock_quantity' => 100]);
    }

    public function test_medicine_name_must_be_unique(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Medicine::create([
            'name' => 'Napa 500',
            'unit' => 'tablet',
            'stock_quantity' => 50,
            'unit_price' => 2.50,
            'low_stock_threshold' => 10,
        ]);

        $response = $this->actingAs($admin)->post('/admin/medicines', [
            'name' => 'Napa 500',
            'unit' => 'tablet',
            'stock_quantity' => 10,
            'unit_price' => 2.50,
            'low_stock_threshold' => 5,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_stock_index_flags_low_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Medicine::create([
            'name' => 'Almost Gone',
            'unit' => 'vial',
            'stock_quantity' => 3,
            'unit_price' => 50,
            'low_stock_threshold' => 10,
        ]);

        $response = $this->actingAs($admin)->get('/admin/medicines');

        $response->assertOk();
        $response->assertSee('Low stock');
    }

    public function test_pharmacist_can_view_stock_but_cannot_manage_it(): void
    {
        $pharmacist = User::factory()->create(['role' => 'pharmacist']);

        $this->actingAs($pharmacist)->get('/admin/medicines')->assertOk();
        $this->actingAs($pharmacist)->get('/admin/medicines/create')->assertForbidden();
        $this->actingAs($pharmacist)->post('/admin/medicines', [
            'name' => 'X',
            'unit' => 'tablet',
            'stock_quantity' => 1,
            'unit_price' => 1,
            'low_stock_threshold' => 1,
        ])->assertForbidden();
    }

    public function test_patient_cannot_access_pharmacy(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($patient)->get('/admin/medicines')->assertRedirect('/login');
    }

    public function test_pharmacist_can_dispense_and_stock_drops(): void
    {
        $pharmacist = User::factory()->create(['role' => 'pharmacist']);
        $medicine = Medicine::create([
            'name' => 'Napa 500',
            'unit' => 'tablet',
            'stock_quantity' => 100,
            'unit_price' => 2.50,
            'low_stock_threshold' => 10,
        ]);
        [$prescription, $item] = $this->makePrescriptionWithItem();

        $response = $this->actingAs($pharmacist)->post(
            "/admin/prescriptions/{$prescription->id}/items/{$item->id}/dispense",
            ['medicine_id' => $medicine->id, 'quantity' => 10]
        );

        $response->assertRedirect()->assertSessionHas('success');
        $this->assertSame(90, $medicine->fresh()->stock_quantity);
        $fresh = $item->fresh();
        $this->assertSame(10, $fresh->dispensed_quantity);
        $this->assertNotNull($fresh->dispensed_at);
        $this->assertSame($pharmacist->id, $fresh->dispensed_by);
    }

    public function test_dispense_more_than_stock_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $medicine = Medicine::create([
            'name' => 'Rare Vial',
            'unit' => 'vial',
            'stock_quantity' => 2,
            'unit_price' => 500,
            'low_stock_threshold' => 1,
        ]);
        [$prescription, $item] = $this->makePrescriptionWithItem();

        $this->actingAs($admin)->post(
            "/admin/prescriptions/{$prescription->id}/items/{$item->id}/dispense",
            ['medicine_id' => $medicine->id, 'quantity' => 5]
        )->assertSessionHasErrors('quantity');

        $this->assertSame(2, $medicine->fresh()->stock_quantity);
        $this->assertNull($item->fresh()->dispensed_at);
    }

    public function test_double_dispense_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $medicine = Medicine::create([
            'name' => 'Napa 500',
            'unit' => 'tablet',
            'stock_quantity' => 100,
            'unit_price' => 2.50,
            'low_stock_threshold' => 10,
        ]);
        [$prescription, $item] = $this->makePrescriptionWithItem();

        $url = "/admin/prescriptions/{$prescription->id}/items/{$item->id}/dispense";
        $this->actingAs($admin)->post($url, ['medicine_id' => $medicine->id, 'quantity' => 10])->assertRedirect();
        $this->actingAs($admin)->post($url, ['medicine_id' => $medicine->id, 'quantity' => 10])->assertSessionHasErrors('item');

        $this->assertSame(90, $medicine->fresh()->stock_quantity);
    }

    public function test_expired_medicine_cannot_be_dispensed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $medicine = Medicine::create([
            'name' => 'Old Syrup',
            'unit' => 'bottle',
            'stock_quantity' => 50,
            'unit_price' => 120,
            'low_stock_threshold' => 5,
            'expiry_date' => now()->subMonth()->toDateString(),
        ]);
        [$prescription, $item] = $this->makePrescriptionWithItem();

        $this->actingAs($admin)->post(
            "/admin/prescriptions/{$prescription->id}/items/{$item->id}/dispense",
            ['medicine_id' => $medicine->id, 'quantity' => 1]
        )->assertSessionHasErrors('medicine_id');

        $this->assertSame(50, $medicine->fresh()->stock_quantity);
    }

    public function test_dispense_item_of_another_prescription_404s(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $medicine = Medicine::create([
            'name' => 'Napa 500',
            'unit' => 'tablet',
            'stock_quantity' => 100,
            'unit_price' => 2.50,
            'low_stock_threshold' => 10,
        ]);
        [$first] = $this->makePrescriptionWithItem();
        [, $otherItem] = $this->makePrescriptionWithItem();

        $this->actingAs($admin)->post(
            "/admin/prescriptions/{$first->id}/items/{$otherItem->id}/dispense",
            ['medicine_id' => $medicine->id, 'quantity' => 1]
        )->assertNotFound();
    }

    public function test_disabled_module_hides_routes_but_keeps_history_readable(): void
    {
        config(['modules.pharmacy' => false]);
        $admin = User::factory()->create(['role' => 'admin']);
        [$prescription] = $this->makePrescriptionWithItem();

        $this->actingAs($admin)->get('/admin/medicines')->assertNotFound();
        $this->actingAs($admin)->get("/admin/prescriptions/{$prescription->id}")->assertOk();
    }

    /**
     * @return array{0: Prescription, 1: PrescriptionItem}
     */
    private function makePrescriptionWithItem(): array
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Dr Dispense',
            'slug' => 'dr-dispense-'.uniqid(),
            'user_id' => $doctorUser->id,
            'status' => 1,
        ]);
        $prescription = Prescription::create([
            'doctor_id' => $doctor->id,
            'patient_name' => 'Walk-in Patient',
            'diagnosis' => 'Fever',
        ]);
        $item = $prescription->items()->create([
            'medicine_name' => 'Paracetamol 500',
            'dosage' => '500mg',
            'quantity' => '10',
        ]);

        return [$prescription, $item];
    }
}
