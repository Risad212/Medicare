<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function mockGoogleUser(array $overrides = []): SocialiteUser
    {
        $user = Mockery::mock(SocialiteUser::class);

        $user->shouldReceive('getId')->andReturn(array_key_exists('id', $overrides) ? $overrides['id'] : 'google-123');
        $user->shouldReceive('getEmail')->andReturn(array_key_exists('email', $overrides) ? $overrides['email'] : 'patient@gmail.com');
        $user->shouldReceive('getName')->andReturn(array_key_exists('name', $overrides) ? $overrides['name'] : 'Test Patient');
        $user->shouldReceive('getNickname')->andReturn(array_key_exists('nickname', $overrides) ? $overrides['nickname'] : null);
        $user->shouldReceive('getAvatar')->andReturn(array_key_exists('avatar', $overrides) ? $overrides['avatar'] : 'https://example.com/avatar.jpg');

        return $user;
    }

    public function test_google_redirect_route_exists_and_redirects(): void
    {
        Socialite::shouldReceive('driver->redirect')
            ->once()
            ->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        $response = $this->get(route('auth.google.redirect'));

        $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_callback_creates_new_patient_when_not_exists(): void
    {
        $googleUser = $this->mockGoogleUser([
            'id' => 'google-999',
            'email' => 'newpatient@gmail.com',
            'name' => 'New Patient',
        ]);

        Socialite::shouldReceive('driver->user')->once()->andReturn($googleUser);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('profile'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'newpatient@gmail.com',
            'google_id' => 'google-999',
            'provider' => 'google',
            'role' => 'patient',
        ]);

        /** @var User $user */
        $user = User::where('email', 'newpatient@gmail.com')->first();
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('password', $user->password) === false); // random password
    }

    public function test_callback_links_existing_patient_by_email(): void
    {
        $existing = User::factory()->create([
            'email' => 'existing@gmail.com',
            'role' => 'patient',
            'google_id' => null,
            'provider' => null,
        ]);

        $googleUser = $this->mockGoogleUser([
            'id' => 'google-link-1',
            'email' => 'existing@gmail.com',
            'name' => 'Existing',
        ]);

        Socialite::shouldReceive('driver->user')->once()->andReturn($googleUser);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('profile'));
        $this->assertAuthenticatedAs($existing->fresh());

        $this->assertDatabaseHas('users', [
            'id' => $existing->id,
            'google_id' => 'google-link-1',
            'provider' => 'google',
            'role' => 'patient', // not overridden
        ]);
    }

    public function test_callback_finds_by_google_id_directly(): void
    {
        $user = User::factory()->create([
            'email' => 'googleuser@gmail.com',
            'google_id' => 'google-123',
            'provider' => 'google',
            'role' => 'patient',
        ]);

        $googleUser = $this->mockGoogleUser([
            'id' => 'google-123',
            'email' => 'googleuser@gmail.com',
        ]);

        Socialite::shouldReceive('driver->user')->once()->andReturn($googleUser);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('profile'));
        $this->assertAuthenticatedAs($user->fresh());
        // ensure no duplicate user created
        $this->assertEquals(1, User::where('email', 'googleuser@gmail.com')->count());
    }

    public function test_callback_respects_admin_role_redirect(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@gmail.com',
            'role' => 'admin',
        ]);

        $googleUser = $this->mockGoogleUser([
            'id' => 'google-admin-1',
            'email' => 'admin@gmail.com',
        ]);

        Socialite::shouldReceive('driver->user')->once()->andReturn($googleUser);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('admin.home'));
        $this->assertAuthenticated();
        $this->assertEquals('admin', auth()->user()->role);
    }

    public function test_callback_respects_doctor_role_redirect(): void
    {
        $doctor = User::factory()->create([
            'email' => 'doctor@gmail.com',
            'role' => 'doctor',
        ]);

        $googleUser = $this->mockGoogleUser([
            'id' => 'google-doc-1',
            'email' => 'doctor@gmail.com',
        ]);

        Socialite::shouldReceive('driver->user')->once()->andReturn($googleUser);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('doctor.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_callback_fails_when_no_email_returned(): void
    {
        $googleUser = $this->mockGoogleUser([
            'email' => null,
        ]);
        // Need to mock getEmail returning null explicitly
        // Our mock already does, but SocialiteUser mock will return null

        Socialite::shouldReceive('driver->user')->once()->andReturn($googleUser);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_callback_handles_socialite_exception(): void
    {
        Socialite::shouldReceive('driver->user')->once()->andThrow(new \Exception('Invalid state'));

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_login_view_contains_google_button(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee(route('auth.google.redirect'));
        $response->assertSee('Continue with Google');
    }

    public function test_register_view_contains_google_button(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee(route('auth.google.redirect'));
        $response->assertSee('Sign up with Google');
    }

    public function test_new_google_user_has_verified_email_and_patient_role(): void
    {
        $googleUser = $this->mockGoogleUser([
            'id' => 'google-verify-1',
            'email' => 'verify@gmail.com',
            'name' => 'Verify Me',
        ]);

        Socialite::shouldReceive('driver->user')->once()->andReturn($googleUser);

        $this->get(route('auth.google.callback'));

        /** @var User $user */
        $user = User::where('email', 'verify@gmail.com')->first();
        $this->assertEquals('patient', $user->role);
        $this->assertNotNull($user->email_verified_at);
        $this->assertEquals('google-verify-1', $user->google_id);
    }

    public function test_google_callback_rejects_unverified_email(): void
    {
        $user = User::factory()->create([
            'email' => 'patient@gmail.com',
            'role' => 'patient',
        ]);

        $googleUser = $this->mockGoogleUser();
        $googleUser->user = ['email_verified' => false];

        Socialite::shouldReceive('driver->user')->once()->andReturn($googleUser);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'google_id' => null,
        ]);
    }

    public function test_google_auth_does_not_override_existing_role(): void
    {
        $doctor = User::factory()->create([
            'email' => 'doc2@gmail.com',
            'role' => 'doctor',
            'google_id' => null,
        ]);

        $googleUser = $this->mockGoogleUser([
            'id' => 'google-doc2',
            'email' => 'doc2@gmail.com',
        ]);

        Socialite::shouldReceive('driver->user')->once()->andReturn($googleUser);

        $this->get(route('auth.google.callback'));

        $this->assertDatabaseHas('users', [
            'id' => $doctor->id,
            'role' => 'doctor', // still doctor
        ]);
    }
}
