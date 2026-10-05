<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\DoctorOffDay;
use App\Models\DoctorSchedule;
use App\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_text_search_matches_name_specialist_and_department(): void
    {
        $this->makeDoctor(['name' => 'Dr Heart Surgeon', 'specialist' => 'Cardiology', 'department' => 'Cardiac']);
        $this->makeDoctor(['name' => 'Dr Jane Doe', 'specialist' => 'Neurology', 'department' => 'Neuro']);

        $this->get('/doctor?search=heart')->assertSee('Dr Heart Surgeon', false)->assertDontSee('Dr Jane Doe', false);
        $this->get('/doctor?search=neuro')->assertSee('Dr Jane Doe', false)->assertDontSee('Dr Heart Surgeon', false);
        $this->get('/doctor?search=cardiac')->assertSee('Dr Heart Surgeon', false);
    }

    public function test_inactive_doctors_are_excluded_and_empty_state_shows(): void
    {
        $this->makeDoctor(['name' => 'Dr Active', 'status' => 1]);
        $this->makeDoctor(['name' => 'Dr Hidden', 'status' => 0]);

        $response = $this->get('/doctor');

        $response->assertSee('Dr Active', false)->assertDontSee('Dr Hidden', false);

        $this->get('/doctor?search=nobody-matches-this')
            ->assertSee('No doctors found.', false);
    }

    public function test_department_filter_is_exact(): void
    {
        $this->makeDoctor(['name' => 'Dr A', 'department' => 'Cardiac']);
        $this->makeDoctor(['name' => 'Dr B', 'department' => 'Neuro']);

        $this->get('/doctor?department=Cardiac')->assertSee('Dr A', false)->assertDontSee('Dr B', false);
    }

    public function test_date_filter_respects_schedule_and_off_days(): void
    {
        $date = now()->addDays(2)->toDateString();
        $weekday = now()->addDays(2)->dayOfWeek;
        $slot = TimeSlot::create(['time' => '10:00 AM', 'status' => 1]);

        $scheduled = $this->makeDoctor(['name' => 'Dr Scheduled']);
        DoctorSchedule::create(['doctor_id' => $scheduled->id, 'weekday' => $weekday, 'time_slot_id' => $slot->id]);

        $dayOff = $this->makeDoctor(['name' => 'Dr DayOff']);
        DoctorOffDay::create(['doctor_id' => $dayOff->id, 'date' => $date]);

        $unconfigured = $this->makeDoctor(['name' => 'Dr Open']);

        $response = $this->get('/doctor?date='.$date);

        $response->assertSee('Dr Scheduled', false)
            ->assertSee('Dr Open', false)
            ->assertDontSee('Dr DayOff', false);

        // A doctor configured for a different weekday is unavailable.
        $other = $this->makeDoctor(['name' => 'Dr OtherDay']);
        DoctorSchedule::create(['doctor_id' => $other->id, 'weekday' => ($weekday + 1) % 7, 'time_slot_id' => $slot->id]);

        $this->get('/doctor?date='.$date)->assertDontSee('Dr OtherDay', false);
        $this->assertSame($unconfigured->id, $unconfigured->fresh()->id); // sanity
    }

    public function test_past_date_is_rejected(): void
    {
        $this->get('/doctor?date='.now()->subDay()->toDateString())->assertStatus(302);
    }

    public function test_disabled_module_shows_plain_listing_without_form(): void
    {
        config()->set('modules.search', false);
        $this->makeDoctor(['name' => 'Dr Plain']);

        $response = $this->get('/doctor?search=ignored-without-module');

        // No filtering applied, no form rendered, listing still works.
        $response->assertOk();
        $response->assertSee('Dr Plain', false);
        $response->assertDontSee('doctor-search', false);
    }

    public function test_results_paginate_twelve_per_page(): void
    {
        for ($i = 1; $i <= 13; $i++) {
            $this->makeDoctor(['name' => "Dr Page {$i}"]);
        }

        $this->get('/doctor')->assertInertia(fn ($page) => $page
            ->component('Public/Doctors/Index')
            ->has('doctors.data', 12)
            ->where('doctors.total', 13)
        );
    }

    private function makeDoctor(array $overrides = []): Doctor
    {
        static $counter = 0;
        $counter++;

        return Doctor::create(array_merge([
            'name' => 'Dr Test '.$counter,
            'slug' => 'dr-test-'.$counter.'-'.uniqid(),
            'specialist' => 'General',
            'department' => 'General',
            'status' => 1,
        ], $overrides));
    }
}
