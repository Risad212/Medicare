<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GoogleAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_set_a_password_from_the_profile(): void
    {
        $user = User::factory()->create([
            'role' => 'patient',
            'email' => 'setter@example.test',
        ]);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'Brand-New-Pass-123!',
                'password_confirmation' => 'Brand-New-Pass-123!',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('Brand-New-Pass-123!', $user->fresh()->password));
    }

    public function test_weak_password_is_rejected_from_the_profile(): void
    {
        $user = User::factory()->create([
            'role' => 'patient',
            'email' => 'weak@example.test',
        ]);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'brand-new-pass',
                'password_confirmation' => 'brand-new-pass',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_password_is_optional_when_updating_the_profile(): void
    {
        $user = User::factory()->create([
            'role' => 'patient',
            'email' => 'keep@example.test',
        ]);
        $original = $user->password;

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Renamed Patient',
                'email' => $user->email,
            ])
            ->assertRedirect();

        $this->assertSame($original, $user->fresh()->password);
        $this->assertSame('Renamed Patient', $user->fresh()->name);
    }

    public function test_google_linked_user_sees_the_disconnect_option(): void
    {
        $user = User::factory()->create([
            'role' => 'patient',
            'email' => 'linked@example.test',
            'google_id' => 'google-123',
            'provider' => 'google',
        ]);

        $this->actingAs($user)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee(route('profile.google.unlink'), false);
    }

    public function test_google_linked_user_can_disconnect_with_the_correct_password(): void
    {
        $user = User::factory()->create([
            'role' => 'patient',
            'email' => 'unlink@example.test',
            'password' => 'known-password',
            'google_id' => 'google-456',
            'provider' => 'google',
        ]);

        $this->actingAs($user)
            ->delete(route('profile.google.unlink'), ['current_password' => 'known-password'])
            ->assertRedirect();

        $this->assertNull($user->fresh()->google_id);
        $this->assertNull($user->fresh()->provider);
    }

    public function test_disconnect_requires_the_correct_password(): void
    {
        $user = User::factory()->create([
            'role' => 'patient',
            'email' => 'wrongpass@example.test',
            'password' => 'known-password',
            'google_id' => 'google-789',
            'provider' => 'google',
        ]);

        $this->actingAs($user)
            ->delete(route('profile.google.unlink'), ['current_password' => 'wrong-password'])
            ->assertSessionHasErrors('current_password');

        $this->assertSame('google-789', $user->fresh()->google_id);
    }

    public function test_disconnect_is_rejected_when_no_google_account_is_linked(): void
    {
        $user = User::factory()->create([
            'role' => 'patient',
            'email' => 'notlinked@example.test',
            'password' => 'known-password',
            'google_id' => null,
        ]);

        $this->actingAs($user)
            ->delete(route('profile.google.unlink'), ['current_password' => 'known-password'])
            ->assertSessionHasErrors('current_password');

        $this->assertNull($user->fresh()->google_id);
    }
}
