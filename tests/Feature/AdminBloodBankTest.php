<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\BloodDonation;
use App\Models\BloodDonor;
use App\Models\BloodGroup;
use App\Models\BloodIssue;
use App\Models\BloodRequest;
use App\Models\User;
use Database\Seeders\BloodGroupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class AdminBloodBankTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(BloodGroupSeeder::class);
    }

    private function group(string $name): BloodGroup
    {
        return BloodGroup::where('name', $name)->firstOrFail();
    }

    private function makeDonor(array $overrides = []): BloodDonor
    {
        return BloodDonor::create(array_merge([
            'name' => 'Donor Jane',
            'blood_group_id' => $this->group('A+')->id,
            'phone' => '01811-333333',
            'email' => 'jane@example.com',
            'status' => 1,
        ], $overrides));
    }

    private function makeAvailableDonation(int $quantity = 450, array $overrides = []): BloodDonation
    {
        $donor = $overrides['donor'] ?? $this->makeDonor();

        return BloodDonation::create(array_merge([
            'donor_id' => $donor->id,
            'blood_group_id' => $this->group('A+')->id,
            'donation_date' => now()->subDay()->toDateString(),
            'quantity' => $quantity,
            'unit' => 'ml',
            'bag_number' => 'BAG-'.Str::upper(Str::random(6)),
            'expiry_date' => now()->addDays(28)->toDateString(),
            'status' => BloodDonation::STATUS_AVAILABLE,
        ], $overrides));
    }

    private function makeRequest(array $overrides = []): BloodRequest
    {
        $patient = $overrides['patient'] ?? $this->makeUser();

        return BloodRequest::create(array_merge([
            'patient_id' => $patient->id,
            'blood_group_id' => $this->group('A+')->id,
            'quantity' => 450,
            'unit' => 'ml',
            'required_date' => now()->addDay()->toDateString(),
            'urgency' => 'normal',
            'status' => BloodRequest::STATUS_PENDING,
            'requested_by' => $patient->id,
        ], $overrides));
    }

    public function test_only_admins_can_access_blood_bank_pages(): void
    {
        $this->actingAs($this->makeUser())
            ->get(route('admin.bloodbank.dashboard'))
            ->assertRedirect('/login');

        $this->actingAs($this->makeUser())
            ->get(route('admin.blood-groups.index'))
            ->assertRedirect('/login');
    }

    public function test_admin_can_create_update_and_toggle_a_blood_group(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.blood-groups.store'), [
                'name' => 'Bombay',
                'status' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('blood_groups', ['name' => 'BOMBAY', 'status' => 1]);

        $bombay = BloodGroup::where('name', 'BOMBAY')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.blood-groups.update', $bombay->id), [
                'name' => 'Hh-Bombay',
                'status' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('blood_groups', ['name' => 'HH-BOMBAY']);

        $this->actingAs($admin)
            ->patch(route('admin.blood-groups.toggle', $bombay->id))
            ->assertRedirect();

        $this->assertDatabaseHas('blood_groups', ['id' => $bombay->id, 'status' => 0]);
    }

    public function test_admin_can_register_a_donor_and_record_donation_then_make_it_available(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.blood-donors.store'), [
                'name' => 'Donor John',
                'blood_group_id' => $this->group('A+')->id,
                'phone' => '01811-444444',
                'status' => 1,
            ])
            ->assertRedirect();

        $donor = BloodDonor::where('phone', '01811-444444')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.blood-donations.store'), [
                'donor_id' => $donor->id,
                'blood_group_id' => $this->group('A+')->id,
                'donation_date' => now()->subDay()->toDateString(),
                'quantity' => 450,
                'bag_number' => 'BAG-TEST-1',
                'expiry_date' => now()->addDays(30)->toDateString(),
                'notes' => 'collected at camp',
            ])
            ->assertRedirect();

        $donation = BloodDonation::where('bag_number', 'BAG-TEST-1')->firstOrFail();
        $this->assertSame('collected', $donation->status);

        $this->actingAs($admin)
            ->patch(route('admin.blood-donations.status', $donation->id), ['status' => 'available'])
            ->assertRedirect();

        $this->assertSame('available', $donation->fresh()->status);

        $this->actingAs($admin)
            ->get(route('admin.bloodbank.inventory'))
            ->assertOk();
    }

    public function test_approving_a_request_reserves_oldest_bags_and_reject_releases_them(): void
    {
        $admin = $this->makeAdmin();
        $oldBag = $this->makeAvailableDonation(450, ['expiry_date' => now()->addDays(5)->toDateString()]);
        $newBag = $this->makeAvailableDonation(450, ['expiry_date' => now()->addDays(30)->toDateString()]);

        $request = $this->makeRequest(['quantity' => 450]);

        $this->actingAs($admin)
            ->post(route('admin.blood-requests.approve', $request->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(BloodRequest::STATUS_APPROVED, $request->fresh()->status);
        $this->assertSame(BloodDonation::STATUS_RESERVED, $oldBag->fresh()->status);
        $this->assertSame($request->id, $oldBag->fresh()->reserved_for_request_id);
        $this->assertSame(BloodDonation::STATUS_AVAILABLE, $newBag->fresh()->status);

        $this->actingAs($admin)
            ->post(route('admin.blood-requests.reject', $request->id))
            ->assertRedirect();

        $this->assertSame(BloodRequest::STATUS_REJECTED, $request->fresh()->status);
        $this->assertSame(BloodDonation::STATUS_AVAILABLE, $oldBag->fresh()->status);
        $this->assertNull($oldBag->fresh()->reserved_for_request_id);
    }

    public function test_approval_fails_when_stock_is_insufficient(): void
    {
        $admin = $this->makeAdmin();
        $request = $this->makeRequest(['quantity' => 900]);

        $this->actingAs($admin)
            ->post(route('admin.blood-requests.approve', $request->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(BloodRequest::STATUS_PENDING, $request->fresh()->status);
    }

    public function test_partial_approval_when_stock_covers_only_part_of_request(): void
    {
        $admin = $this->makeAdmin();
        $this->makeAvailableDonation(450);

        $request = $this->makeRequest(['quantity' => 900]);

        $this->actingAs($admin)
            ->post(route('admin.blood-requests.approve', $request->id))
            ->assertRedirect();

        $this->assertSame(BloodRequest::STATUS_PARTIALLY_APPROVED, $request->fresh()->status);
    }

    public function test_issue_flow_marks_bag_issued_and_fulfils_request(): void
    {
        $admin = $this->makeAdmin();
        $bag = $this->makeAvailableDonation(450);
        $request = $this->makeRequest(['quantity' => 450]);

        $this->actingAs($admin)
            ->post(route('admin.blood-requests.approve', $request->id))
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.blood-issues.store', $request->id), [
                'donation_id' => $bag->id,
                'issue_date' => now()->toDateString(),
                'receiver_name' => 'Alice Patient',
                'notes' => 'transfusion done',
            ])
            ->assertRedirect();

        $issue = BloodIssue::firstOrFail();
        $this->assertSame(BloodRequest::STATUS_FULFILLED, $request->fresh()->status);
        $this->assertSame(BloodDonation::STATUS_ISSUED, $bag->fresh()->status);
        $this->assertNull($bag->fresh()->reserved_for_request_id);
        $this->assertSame(450, $issue->quantity);
        $this->assertSame($admin->id, $issue->issued_by);

        $this->actingAs($admin)
            ->get(route('admin.blood-issues.show', $issue->id))
            ->assertOk();
    }

    public function test_cannot_issue_a_bag_reserved_for_another_request(): void
    {
        $admin = $this->makeAdmin();
        $bag = $this->makeAvailableDonation(450);

        $requestA = $this->makeRequest(['quantity' => 450, 'patient' => $this->makeUser(['name' => 'Patient A'])]);
        $requestB = $this->makeRequest(['quantity' => 450, 'patient' => $this->makeUser(['name' => 'Patient B'])]);

        $this->actingAs($admin)->post(route('admin.blood-requests.approve', $requestA->id))->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.blood-issues.store', $requestB->id), [
                'donation_id' => $bag->id,
                'issue_date' => now()->toDateString(),
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(BloodDonation::STATUS_RESERVED, $bag->fresh()->status);
        $this->assertDatabaseCount('blood_issues', 0);
    }

    public function test_patient_sees_only_own_requests_on_their_page(): void
    {
        $me = $this->makeUser();
        $other = $this->makeUser();
        $this->makeRequest(['patient' => $me]);
        $this->makeRequest(['patient' => $other]);

        $response = $this->actingAs($me)
            ->get(route('profile.blood-requests'))
            ->assertOk()
            ->assertSee('My Blood Requests');

        $visibleIds = $response->viewData('requests')->pluck('id')->all();
        $this->assertCount(1, $visibleIds);
        $this->assertSame($this->group('A+')->id, BloodRequest::find($visibleIds[0])->blood_group_id);
    }

    public function test_doctor_cannot_view_someone_elses_request(): void
    {
        $doctorA = $this->makeDoctor();
        $doctorB = $this->makeDoctor(['slug' => 'dr-b-'.Str::random(6)]);
        $request = $this->makeRequest(['doctor_id' => $doctorA->id]);

        $this->actingAs(User::find($doctorB->user_id))
            ->get(route('doctor.blood-requests.show', $request->id))
            ->assertForbidden();
    }

    public function test_doctor_can_only_raise_requests_for_own_patients(): void
    {
        $doctor = $this->makeDoctor();
        $patient = $this->makeUser();
        $stranger = $this->makeUser();

        Appointment::create([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'time_slot_id' => $this->makeTimeSlot()->id,
            'patient_name' => $patient->name,
            'age' => 30,
            'gender' => 2,
            'phone' => '01711-999999',
            'email' => $patient->email,
            'cancellation_token' => Str::random(40),
            'visit_type' => 1,
            'appointment_date' => now()->addDay()->toDateString(),
            'status' => 0,
        ]);

        $acting = $this->actingAs(User::find($doctor->user_id));

        // Own patient -> allowed
        $acting->post(route('doctor.blood-requests.store'), [
            'patient_id' => $patient->id,
            'blood_group_id' => $this->group('A+')->id,
            'quantity' => 450,
            'required_date' => now()->addDay()->toDateString(),
            'urgency' => 'normal',
        ])->assertRedirect(route('doctor.blood-requests.index'));

        $this->assertDatabaseHas('blood_requests', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => BloodRequest::STATUS_PENDING,
        ]);

        // Someone else's patient -> blocked
        $acting->post(route('doctor.blood-requests.store'), [
            'patient_id' => $stranger->id,
            'blood_group_id' => $this->group('A+')->id,
            'quantity' => 450,
            'required_date' => now()->addDay()->toDateString(),
            'urgency' => 'normal',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseMissing('blood_requests', ['patient_id' => $stranger->id]);
    }

    public function test_expiry_command_marks_overdue_bags_and_releases_reservations(): void
    {
        $admin = $this->makeAdmin();
        $request = $this->makeRequest(['quantity' => 450]);

        $bag = $this->makeAvailableDonation(450, ['expiry_date' => now()->addDays(30)->toDateString()]);
        $expiredReservedBag = $this->makeAvailableDonation(450, ['expiry_date' => now()->subDay()->toDateString()]);

        // Simulate reservations that were made before the bag went past expiry.
        BloodDonation::whereIn('id', [$bag->id, $expiredReservedBag->id])->update([
            'status' => BloodDonation::STATUS_RESERVED,
            'reserved_for_request_id' => $request->id,
        ]);

        $this->assertSame(BloodDonation::STATUS_RESERVED, $expiredReservedBag->fresh()->status);

        $this->artisan('bloodbank:expire')->assertExitCode(0);

        $this->assertSame(BloodDonation::STATUS_EXPIRED, $expiredReservedBag->fresh()->status);
        $this->assertNull($expiredReservedBag->fresh()->reserved_for_request_id);
        $this->assertSame(BloodDonation::STATUS_RESERVED, $bag->fresh()->status);
        $this->assertSame($request->id, $bag->fresh()->reserved_for_request_id);
    }

    public function test_reports_page_renders_with_donations_and_issues(): void
    {
        $admin = $this->makeAdmin();
        $this->makeAvailableDonation(450);

        $this->actingAs($admin)
            ->get(route('admin.bloodbank.reports'))
            ->assertOk()
            ->assertSee('<em>reports</em>', false);

        $this->actingAs($admin)
            ->get(route('admin.bloodbank.reports.donations'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_donor_eligibility_respects_min_donation_interval(): void
    {
        $donor = $this->makeDonor(['last_donation_date' => now()->subDays(30)->toDateString()]);

        $this->assertFalse($donor->isEligible(90));

        $donor->update(['last_donation_date' => now()->subDays(120)->toDateString()]);

        $this->assertTrue($donor->fresh()->isEligible(90));
    }
}
