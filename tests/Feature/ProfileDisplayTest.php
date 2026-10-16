<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_patient_sees_verified_badge(): void
    {
        $patient = $this->patient(verified: true);

        $this->actingAs($patient)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Verified patient')
            ->assertDontSee('Unverified patient');
    }

    public function test_unverified_patient_sees_unverified_badge_not_verified(): void
    {
        $patient = $this->patient(verified: false);

        $this->actingAs($patient)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Unverified patient')
            ->assertDontSee('Verified patient');
    }

    public function test_google_avatar_is_displayed_when_no_profile_image(): void
    {
        $patient = $this->patient(verified: true, overrides: [
            'google_id' => 'google-uid-123',
            'avatar' => 'https://lh3.example.test/avatar.png',
            'profile_image' => null,
        ]);

        $this->actingAs($patient)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('https://lh3.example.test/avatar.png', false);
    }

    public function test_local_profile_image_takes_priority_over_google_avatar(): void
    {
        $patient = $this->patient(verified: true, overrides: [
            'google_id' => 'google-uid-123',
            'avatar' => 'https://lh3.example.test/avatar.png',
            'profile_image' => 'profile-images/local.jpg',
        ]);

        $this->actingAs($patient)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('storage/profile-images/local.jpg', false)
            ->assertDontSee('https://lh3.example.test/avatar.png', false);
    }

    private function patient(bool $verified, array $overrides = []): User
    {
        return User::factory()->create($overrides + [
            'role' => 'patient',
            'email_verified_at' => $verified ? now() : null,
        ]);
    }
}
