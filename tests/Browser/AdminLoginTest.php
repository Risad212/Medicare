<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminLoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_admin_can_log_in_and_reaches_dashboard(): void
    {
        $admin = User::factory()->create([
            'name' => 'E2E Admin',
            'email' => 'e2e-admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->visit('/login')
                ->type('email', $admin->email)
                ->type('password', 'password')
                ->press('SIGN IN')
                ->waitForLocation('/admin')
                ->assertPathIs('/admin')
                ->assertSee('E2E Admin')
                ->assertSee('appointments today');
        });
    }
}
