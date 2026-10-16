<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicContentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_homepage_copy_seen_by_public_visitors(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('settings.home.update'), [
                'about_title' => 'Care shaped around you',
                'about_description' => 'A message managed by the hospital team.',
            ])
            ->assertSessionHasNoErrors();

        $this->get('/')
            ->assertOk()
            ->assertSee('Care shaped around you')
            ->assertSee('A message managed by the hospital team.');
    }
}
