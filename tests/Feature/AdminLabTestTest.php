<?php

namespace Tests\Feature;

use App\Models\LabTest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class AdminLabTestTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    public function test_index_and_create_pages_load(): void
    {
        $this->actingAs($this->makeAdmin())->get(route('admin.lab-tests.index'))->assertOk();
        $this->actingAs($this->makeAdmin())->get(route('admin.lab-tests.create'))->assertOk();
    }

    public function test_admin_creates_lab_test_with_default_active_status(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.lab-tests.store'), [
                'name' => 'Complete Blood Count',
                'category' => 'Hematology',
                'description' => 'Full hemogram panel.',
                'price' => 450.50,
                'normal_range' => '13.0 - 17.0',
                'unit' => 'g/dL',
            ])
            ->assertRedirect(route('admin.lab-tests.index'))
            ->assertSessionHas('success', 'Laboratory test added successfully!');

        $this->assertDatabaseHas('lab_tests', [
            'name' => 'Complete Blood Count',
            'category' => 'Hematology',
            'status' => 1,
        ]);

        $test = LabTest::where('name', 'Complete Blood Count')->first();
        $this->assertEquals('450.50', $test->price);
    }

    public function test_admin_creates_lab_test_marked_inactive(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.lab-tests.store'), [
                'name' => 'Inactive Test',
                'price' => 100,
                'status' => 0,
            ])
            ->assertRedirect(route('admin.lab-tests.index'));

        $this->assertDatabaseHas('lab_tests', ['name' => 'Inactive Test', 'status' => 0]);
    }

    public function test_lab_test_store_validates_name_and_price(): void
    {
        $this->actingAs($this->makeAdmin())
            ->from(route('admin.lab-tests.create'))
            ->post(route('admin.lab-tests.store'), [
                'name' => '',
                'price' => 'not-a-number',
            ])->assertSessionHasErrors(['name', 'price']);
    }

    public function test_admin_updates_lab_test(): void
    {
        $test = LabTest::create([
            'name' => 'Old Test',
            'price' => 100,
            'status' => 1,
        ]);

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.lab-tests.update', $test->id), [
                'name' => 'Updated Test',
                'category' => 'Biochemistry',
                'price' => 250,
            ])
            ->assertRedirect(route('admin.lab-tests.index'))
            ->assertSessionHas('success', 'Laboratory test updated successfully!');

        $this->assertDatabaseHas('lab_tests', [
            'id' => $test->id,
            'name' => 'Updated Test',
            'category' => 'Biochemistry',
            'price' => 250,
        ]);
    }

    public function test_admin_deletes_lab_test(): void
    {
        $test = LabTest::create(['name' => 'Doomed Test', 'price' => 50, 'status' => 1]);

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.lab-tests.destroy', $test->id))
            ->assertRedirect(route('admin.lab-tests.index'))
            ->assertSessionHas('success', 'Laboratory test deleted successfully!');

        $this->assertDatabaseMissing('lab_tests', ['id' => $test->id]);
    }

    public function test_admin_index_searches_lab_tests(): void
    {
        LabTest::create(['name' => 'Fasting Blood Sugar', 'category' => 'Endocrinology', 'price' => 120, 'status' => 1]);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.lab-tests.index', ['search' => 'Sugar']))
            ->assertOk()
            ->assertSee('Fasting Blood Sugar');
    }
}
