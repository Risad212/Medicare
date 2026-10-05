<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\BloodBank\Models\BloodDonation;
use App\Modules\BloodBank\Models\BloodDonor;
use App\Modules\BloodBank\Models\BloodGroup;
use App\Modules\BloodBank\Models\BloodIssue;
use App\Modules\BloodBank\Models\BloodRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BloodBankModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_module_hides_bloodbank_routes(): void
    {
        config(['modules.bloodbank' => false]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/bloodbank')->assertNotFound();
        $this->actingAs($admin)->get('/admin/bloodbank/inventory')->assertNotFound();
        $this->actingAs($admin)->get('/admin/blood-donors')->assertNotFound();
        $this->actingAs($admin)->get('/admin/blood-requests')->assertNotFound();
        $this->actingAs($admin)->get('/admin/blood-reports')->assertNotFound();

        $doctor = User::factory()->create(['role' => 'doctor']);
        $this->actingAs($doctor)->get('/doctor/blood-requests')->assertNotFound();

        $patient = User::factory()->create(['role' => 'patient']);
        $this->actingAs($patient)->get('/profile/blood-requests')->assertNotFound();
    }

    public function test_enabled_module_serves_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/bloodbank')
            ->assertInertia(fn ($page) => $page->component('Admin/BloodBank/Dashboard'));

        $this->actingAs($admin)->get('/admin/bloodbank/inventory')
            ->assertInertia(fn ($page) => $page->component('Admin/BloodBank/Inventory'));

        $this->actingAs($admin)->get('/admin/blood-reports')
            ->assertInertia(fn ($page) => $page->component('Admin/BloodBank/Reports/Index'));
    }

    public function test_blood_group_management_pages_render_react_components(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $group = BloodGroup::create(['name' => 'O+', 'status' => true]);

        $this->actingAs($admin)->get('/admin/blood-groups')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/BloodBank/Groups/Index')
                ->where('bloodGroups.0.name', 'O+')
            );

        $this->actingAs($admin)->get('/admin/blood-groups/create')
            ->assertInertia(fn ($page) => $page->component('Admin/BloodBank/Groups/Form')->where('mode', 'create'));

        $this->actingAs($admin)->get("/admin/blood-groups/{$group->id}/edit")
            ->assertInertia(fn ($page) => $page->component('Admin/BloodBank/Groups/Form')->where('bloodGroup.name', 'O+'));
    }

    public function test_blood_donor_management_pages_render_react_components(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $group = BloodGroup::create(['name' => 'A+', 'status' => true]);
        $donor = BloodDonor::create([
            'name' => 'Test Donor',
            'blood_group_id' => $group->id,
            'phone' => '01710000000',
            'status' => true,
        ]);

        $this->actingAs($admin)->get('/admin/blood-donors?search=Test')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/BloodBank/Donors/Index')
                ->where('donors.data.0.name', 'Test Donor')
                ->where('filters.search', 'Test')
            );

        $this->actingAs($admin)->get('/admin/blood-donors/create')
            ->assertInertia(fn ($page) => $page->component('Admin/BloodBank/Donors/Form')->where('mode', 'create'));

        $this->actingAs($admin)->get("/admin/blood-donors/{$donor->id}")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/BloodBank/Donors/Show')
                ->where('donor.name', 'Test Donor')
                ->where('donor.eligible', true)
            );

        $this->actingAs($admin)->get("/admin/blood-donors/{$donor->id}/edit")
            ->assertInertia(fn ($page) => $page->component('Admin/BloodBank/Donors/Form')->where('donor.name', 'Test Donor'));
    }

    public function test_blood_donation_pages_render_react_components(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $group = BloodGroup::create(['name' => 'B+', 'status' => true]);
        $donor = BloodDonor::create([
            'name' => 'Donation Test',
            'blood_group_id' => $group->id,
            'phone' => '01710000000',
            'status' => true,
        ]);
        $donation = BloodDonation::create([
            'donor_id' => $donor->id,
            'blood_group_id' => $group->id,
            'donation_date' => now()->toDateString(),
            'quantity' => 450,
            'unit' => 'ml',
            'bag_number' => 'B-TEST',
            'expiry_date' => now()->addMonths(3)->toDateString(),
            'status' => BloodDonation::STATUS_COLLECTED,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->get('/admin/blood-donations')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/BloodBank/Donations/Index')
                ->where('donations.data.0.status', BloodDonation::STATUS_COLLECTED)
                ->where('donations.data.0.canSetAvailable', true)
            );

        $this->actingAs($admin)->get('/admin/blood-donations/create')
            ->assertInertia(fn ($page) => $page->component('Admin/BloodBank/Donations/Form')->where('mode', 'create'));

        $this->actingAs($admin)->get("/admin/blood-donations/{$donation->id}")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/BloodBank/Donations/Show')
                ->where('donation.bagNumber', 'B-TEST')
            );

        $this->actingAs($admin)->get("/admin/blood-donations/{$donation->id}/edit")
            ->assertInertia(fn ($page) => $page->component('Admin/BloodBank/Donations/Form')->where('donation.bagNumber', 'B-TEST'));
    }

    public function test_blood_request_and_issue_pages_render_react_components(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = User::factory()->create(['role' => 'patient']);
        $group = BloodGroup::create(['name' => 'AB+', 'status' => true]);
        $donor = BloodDonor::create([
            'name' => 'Issue Donor',
            'blood_group_id' => $group->id,
            'phone' => '01710000000',
            'status' => true,
        ]);
        $bloodRequest = BloodRequest::create([
            'patient_id' => $patient->id,
            'blood_group_id' => $group->id,
            'quantity' => 450,
            'unit' => 'ml',
            'required_date' => now()->toDateString(),
            'urgency' => 'urgent',
            'status' => BloodRequest::STATUS_APPROVED,
            'requested_by' => $admin->id,
        ]);
        $bag = BloodDonation::create([
            'donor_id' => $donor->id,
            'blood_group_id' => $group->id,
            'donation_date' => now()->toDateString(),
            'quantity' => 450,
            'unit' => 'ml',
            'bag_number' => 'B-ISSUE',
            'expiry_date' => now()->addMonths(3)->toDateString(),
            'status' => BloodDonation::STATUS_RESERVED,
            'reserved_for_request_id' => $bloodRequest->id,
            'created_by' => $admin->id,
        ]);
        $issue = BloodIssue::create([
            'request_id' => $bloodRequest->id,
            'patient_id' => $patient->id,
            'blood_group_id' => $group->id,
            'donation_id' => $bag->id,
            'quantity' => 450,
            'unit' => 'ml',
            'issue_date' => now()->toDateString(),
            'issued_by' => $admin->id,
            'receiver_name' => $patient->name,
        ]);

        $this->actingAs($admin)->get('/admin/blood-requests')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/BloodBank/Requests/Index')
                ->where('requests.data.0.patientName', $patient->name)
            );

        $this->actingAs($admin)->get('/admin/blood-requests/create')
            ->assertInertia(fn ($page) => $page->component('Admin/BloodBank/Requests/Create'));

        $this->actingAs($admin)->get("/admin/blood-requests/{$bloodRequest->id}")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/BloodBank/Requests/Show')
                ->where('bloodRequest.issues.0.id', $issue->id)
                ->where('reservedUnits.0.bagNumber', 'B-ISSUE')
            );

        $this->actingAs($admin)->get('/admin/blood-issues')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/BloodBank/Issues/Index')
                ->where('issues.data.0.patientName', $patient->name)
            );

        $this->actingAs($admin)->get("/admin/blood-issues/create/{$bloodRequest->id}")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/BloodBank/Issues/Create')
                ->where('reservedBags.0.bagNumber', 'B-ISSUE')
            );

        $this->actingAs($admin)->get("/admin/blood-issues/{$issue->id}")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/BloodBank/Issues/Show')
                ->where('issue.bagNumber', 'B-ISSUE')
            );
    }
}
