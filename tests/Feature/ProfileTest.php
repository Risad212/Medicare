<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    public function test_profile_page_lists_only_own_appointments(): void
    {
        $patient = $this->makeUser();
        $doctorOwn = $this->makeDoctor(['name' => 'Dr. Alice Own']);
        $this->makeAppointment(['user_id' => $patient->id, 'doctor_id' => $doctorOwn->id]);

        $otherUser = $this->makeUser();
        $doctorOther = $this->makeDoctor(['name' => 'Dr. Bob Other']);
        $this->makeAppointment(['user_id' => $otherUser->id, 'doctor_id' => $doctorOther->id]);

        $this->actingAs($patient)->get(route('profile'))
            ->assertOk()
            ->assertSee('Dr. Alice Own')
            ->assertDontSee('Dr. Bob Other');
    }

    public function test_profile_update_updates_name_and_email(): void
    {
        $patient = $this->makeUser(['name' => 'Old Name', 'email' => 'old@example.com']);

        $this->actingAs($patient)
            ->from(route('profile'))
            ->put(route('profile.update'), [
                'name' => 'New Name',
                'email' => 'new@example.com',
                'phone' => '01711-555555',
            ])->assertRedirect()->assertSessionHas('success', 'Profile updated successfully.');

        $this->assertDatabaseHas('users', [
            'id' => $patient->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
            'phone' => '01711-555555',
        ]);
    }

    public function test_profile_update_allows_unchanged_email(): void
    {
        $patient = $this->makeUser(['email' => 'same@example.com']);

        $this->actingAs($patient)
            ->from(route('profile'))
            ->put(route('profile.update'), [
                'name' => 'New Name',
                'email' => 'same@example.com',
            ])->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $patient->id, 'email' => 'same@example.com']);
    }

    public function test_profile_update_rejects_another_users_email(): void
    {
        $patient = $this->makeUser();
        User::factory()->create(['email' => 'taken@example.com', 'role' => 'patient']);

        $this->actingAs($patient)
            ->from(route('profile'))
            ->put(route('profile.update'), [
                'name' => 'New Name',
                'email' => 'taken@example.com',
            ])->assertSessionHasErrors('email');
    }

    public function test_profile_update_validates_required_fields(): void
    {
        $patient = $this->makeUser();

        $this->actingAs($patient)
            ->from(route('profile'))
            ->put(route('profile.update'), [])
            ->assertSessionHasErrors(['name', 'email']);
    }

    public function test_profile_update_uploads_profile_image(): void
    {
        Storage::fake('public');
        $patient = $this->makeUser();

        $this->actingAs($patient)
            ->from(route('profile'))
            ->put(route('profile.update'), [
                'name' => 'New Name',
                'email' => 'new@example.com',
                'profile_image' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
            ])->assertRedirect();

        $user = $patient->fresh();
        $this->assertNotNull($user->profile_image);
        Storage::disk('public')->assertExists($user->profile_image);
    }

    public function test_profile_update_rejects_non_image_upload(): void
    {
        $patient = $this->makeUser();

        $this->actingAs($patient)
            ->from(route('profile'))
            ->put(route('profile.update'), [
                'name' => 'New Name',
                'email' => 'new@example.com',
                'profile_image' => UploadedFile::fake()->create('doc.txt', 100),
            ])->assertSessionHasErrors('profile_image');
    }
}
