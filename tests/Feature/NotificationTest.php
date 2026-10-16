<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_mark_their_notification_as_read(): void
    {
        $owner = User::factory()->create(['role' => 'patient']);
        $notification = $owner->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\Generic',
            'data' => ['message' => 'hello'],
        ]);

        $this->actingAs($owner)
            ->patch(route('notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_marking_another_users_notification_returns_not_found(): void
    {
        $owner = User::factory()->create(['role' => 'patient']);
        $other = User::factory()->create(['role' => 'patient']);
        $notification = $owner->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\Generic',
            'data' => ['message' => 'secret'],
        ]);

        $this->actingAs($other)
            ->patch(route('notifications.read', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_notification_inbox_requires_authentication(): void
    {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
    }
}
