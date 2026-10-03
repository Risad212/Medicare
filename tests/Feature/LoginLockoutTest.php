<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\LoginController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LoginLockoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_five_failed_attempts_then_rate_limited(): void
    {
        User::factory()->create(['email' => 'doc@example.com', 'password' => Hash::make('correct-pw')]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'doc@example.com', 'password' => 'wrong-pw'])
                ->assertRedirect()
                ->assertSessionHasErrors('email');
        }

        // 6th rapid attempt hits the per-IP throttle, even with right password.
        $this->post('/login', ['email' => 'doc@example.com', 'password' => 'correct-pw'])
            ->assertStatus(429);
    }

    public function test_successful_login_resets_and_works(): void
    {
        User::factory()->create([
            'email' => 'nurse@example.com',
            'password' => Hash::make('correct-pw'),
            'role' => 'patient',
        ]);

        $this->post('/login', ['email' => 'nurse@example.com', 'password' => 'wrong-pw'])
            ->assertRedirect()
            ->assertSessionHasErrors('email');

        $this->post('/login', ['email' => 'nurse@example.com', 'password' => 'correct-pw'])
            ->assertRedirect();

        $this->assertAuthenticated();
    }

    public function test_lockout_is_per_account_not_global(): void
    {
        User::factory()->create(['email' => 'a@example.com', 'password' => Hash::make('pw-a'), 'role' => 'patient']);
        User::factory()->create(['email' => 'b@example.com', 'password' => Hash::make('pw-b'), 'role' => 'patient']);

        for ($i = 0; $i < 3; $i++) {
            $this->post('/login', ['email' => 'a@example.com', 'password' => 'wrong']);
        }

        $this->post('/login', ['email' => 'b@example.com', 'password' => 'pw-b'])->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_login_page_still_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Sign in', false);
    }

    public function test_disabled_module_skips_per_account_lockout(): void
    {
        config()->set('modules.lockout', false);

        $controller = new LoginController;
        $request = Request::create('/login', 'POST', ['email' => 'x@y.z']);
        $method = new \ReflectionMethod($controller, 'hasTooManyLoginAttempts');

        $this->assertFalse($method->invoke($controller, $request));

        // And the boot-time route throttle is registered while enabled.
        config()->set('modules.lockout', true);
        $route = Route::getRoutes()->getByName('login.attempt');
        $this->assertNotNull($route);
        $this->assertContains('throttle:5,1', $route->gatherMiddleware());
    }
}
