<?php

namespace Tests\Browser;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

// TEMPORARY visual-verification test for the admin redesign. Delete after review.
class TempRedesignShotsTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_capture_redesign_screenshots(): void
    {
        $admin = User::factory()->create([
            'name' => 'E2E Admin',
            'email' => 'e2e-admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        Doctor::create(['name' => 'Dr. Ayesha Rahman', 'slug' => 'dr-ayesha-rahman', 'department' => 'Cardiology', 'specialist' => 'Interventional', 'phone' => '01711-000111', 'status' => 1]);
        Doctor::create(['name' => 'Dr. Tanvir Hasan', 'slug' => 'dr-tanvir-hasan', 'department' => 'Orthopedics', 'specialist' => 'Sports injury', 'phone' => '01811-000222', 'status' => 1]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->visit('/login')
                ->type('email', $admin->email)
                ->type('password', 'password')
                ->press('SIGN IN')
                ->waitForLocation('/admin');

            $browser->visit('/admin/doctors')->pause(600)->screenshot('redesign-doctors-list');
            $browser->visit('/admin/doctors/create')->pause(600)->screenshot('redesign-doctor-create');
            $browser->visit('/admin/appointments')->pause(600)->screenshot('redesign-appointments-list');
            $browser->visit('/admin')->pause(600)->screenshot('redesign-dashboard');
        });

        $this->assertTrue(true);
    }
}
