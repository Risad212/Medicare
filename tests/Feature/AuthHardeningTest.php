<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\LoginController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_cookie_posture_follows_environment(): void
    {
        $this->assertTrue(config('session.http_only'));
        $this->assertSame('lax', config('session.same_site'));

        // No SESSION_SECURE_COOKIE override here: non-production resolves
        // insecure (local HTTP dev keeps working); production defaults to
        // secure via config/session.php. See .env.example (ships true).
        $this->assertFalse(config('session.secure'));
    }

    public function test_role_is_not_mass_assignable(): void
    {
        $user = new User;

        $user->fill(['role' => 'admin']);

        $this->assertNotSame('admin', $user->role);
    }

    public function test_patient_post_to_admin_route_is_forbidden(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($patient)
            ->post(route('admin.categories.store'), ['name' => 'Hijack', 'status' => 1])
            ->assertForbidden();
    }

    public function test_authenticated_wrong_role_is_forbidden_and_guest_redirects_to_login(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($patient)->get('/admin')->assertForbidden();

        auth()->logout();
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_login_default_redirect_is_home_not_admin(): void
    {
        $controller = new LoginController;

        $property = new \ReflectionProperty($controller, 'redirectTo');
        $property->setAccessible(true);

        $this->assertSame('/', $property->getValue($controller));
    }
}
