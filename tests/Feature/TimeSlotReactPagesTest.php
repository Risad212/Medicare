<?php

namespace Tests\Feature;

use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeSlotReactPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_time_slot_list_and_forms_use_react_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $slot = TimeSlot::create(['time' => '09:30 AM', 'status' => 1]);

        $this->actingAs($admin)
            ->get('/admin/time-slots')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/TimeSlots/Index')
                ->where('timeSlots.0.time', '09:30 AM')
                ->where('timeSlots.0.status', 1)
            );

        $this->actingAs($admin)
            ->get('/admin/time-slots/create')
            ->assertInertia(fn ($page) => $page->component('Admin/TimeSlots/Create'));

        $this->actingAs($admin)
            ->get("/admin/time-slots/{$slot->id}/edit")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/TimeSlots/Edit')
                ->where('timeSlot.time', '09:30 AM')
            );
    }

    public function test_time_slot_create_update_and_delete_preserve_status_and_uniqueness(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->from('/admin/time-slots/create')->post('/admin/time-slots', [
            'time' => '10:30 AM',
            'status' => '1',
        ])->assertRedirect('/admin/time-slots/create');

        $slot = TimeSlot::where('time', '10:30 AM')->firstOrFail();
        $this->assertSame(1, (int) $slot->status);

        $this->actingAs($admin)->from("/admin/time-slots/{$slot->id}/edit")->put("/admin/time-slots/{$slot->id}", [
            'time' => '11:00 AM',
        ])->assertRedirect("/admin/time-slots/{$slot->id}/edit");

        $this->assertSame('11:00 AM', $slot->fresh()->time);
        $this->assertSame(0, (int) $slot->fresh()->status);

        $this->actingAs($admin)->from('/admin/time-slots/create')->post('/admin/time-slots', [
            'time' => '11:00 AM',
        ])->assertRedirect('/admin/time-slots/create')->assertSessionHasErrors('time');

        $this->actingAs($admin)->from('/admin/time-slots')->delete("/admin/time-slots/{$slot->id}")
            ->assertRedirect('/admin/time-slots');
        $this->assertDatabaseMissing('time_slots', ['id' => $slot->id]);
    }
}
