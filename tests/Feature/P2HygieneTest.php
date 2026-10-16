<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Blog;
use App\Models\BlogComment;
use App\Models\Category;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Tag;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class P2HygieneTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_blog_show_route_is_gone(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $blog = Blog::create([
            'title' => 'Post', 'slug' => 'post-'.uniqid(), 'content' => '<p>x</p>',
            'author' => 'A', 'status' => 1,
        ]);

        // GET on the resource URI is no longer routable (only PUT/PATCH update
        // occupies it now), so the dead show action is unreachable.
        $this->actingAs($admin)->get('/admin/blogs/'.$blog->id)->assertStatus(405);
    }

    public function test_comment_edit_page_renders_and_updates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $blog = Blog::create([
            'title' => 'Post', 'slug' => 'post-'.uniqid(), 'content' => '<p>x</p>',
            'author' => 'A', 'status' => 1,
        ]);
        $comment = BlogComment::create([
            'blog_id' => $blog->id, 'name' => 'N', 'email' => 'n@example.test',
            'comment' => 'Old', 'status' => 0,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.comments.edit', $comment->id))
            ->assertOk()
            ->assertSee('Update comment');

        $this->actingAs($admin)
            ->put(route('admin.comments.update', $comment->id), [
                'name' => 'New Name',
                'email' => 'new@example.test',
                'comment' => 'Edited body',
                'status' => '1',
            ])
            ->assertRedirect(route('admin.comments.index'));

        $this->assertDatabaseHas('blog_comments', [
            'id' => $comment->id, 'name' => 'New Name', 'comment' => 'Edited body', 'status' => 1,
        ]);
    }

    public function test_comment_approve_button_still_works(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $blog = Blog::create([
            'title' => 'Post', 'slug' => 'post-'.uniqid(), 'content' => '<p>x</p>',
            'author' => 'A', 'status' => 1,
        ]);
        $comment = BlogComment::create([
            'blog_id' => $blog->id, 'name' => 'N', 'email' => 'n@example.test',
            'comment' => 'Hi', 'status' => 0,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.comments.update', $comment->id))
            ->assertRedirect();

        $this->assertDatabaseHas('blog_comments', ['id' => $comment->id, 'status' => 1]);
    }

    public function test_time_slot_delete_blocked_when_booked(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $slot = TimeSlot::create(['time' => '02:00 PM', 'status' => 1]);
        $this->appointment($slot);

        $this->actingAs($admin)
            ->delete(route('admin.time-slots.destroy', $slot->id))
            ->assertRedirect();

        $this->assertDatabaseHas('time_slots', ['id' => $slot->id]);
    }

    public function test_time_slot_delete_allowed_when_free(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $slot = TimeSlot::create(['time' => '03:00 PM', 'status' => 1]);

        $this->actingAs($admin)
            ->delete(route('admin.time-slots.destroy', $slot->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('time_slots', ['id' => $slot->id]);
    }

    public function test_department_delete_blocked_when_assigned(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $department = Department::create(['name' => 'Cardiology', 'status' => 1]);
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        Doctor::create([
            'name' => 'Heart Doc', 'slug' => 'heart-doc-'.uniqid(),
            'user_id' => $doctorUser->id, 'department' => 'Cardiology', 'status' => 1,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.departments.destroy', $department->id))
            ->assertRedirect();

        $this->assertDatabaseHas('departments', ['id' => $department->id]);
    }

    public function test_category_delete_blocked_when_used(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Health', 'slug' => 'health']);
        Blog::create([
            'title' => 'Post', 'slug' => 'post-'.uniqid(), 'content' => '<p>x</p>',
            'author' => 'A', 'status' => 1, 'category' => 'Health',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category->id))
            ->assertRedirect();

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_tag_delete_blocked_when_used(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tag = Tag::create(['name' => 'health', 'slug' => 'health']);
        Blog::create([
            'title' => 'Post', 'slug' => 'post-'.uniqid(), 'content' => '<p>x</p>',
            'author' => 'A', 'status' => 1, 'tags' => 'health, wellness',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.tags.destroy', $tag->id))
            ->assertRedirect();

        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
    }

    public function test_patient_show_requires_both_email_and_phone_when_both_present(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = Patient::create([
            'name' => 'Pat', 'email' => 'pat@example.test', 'phone' => '555-0200',
        ]);
        $slot = TimeSlot::create(['time' => '04:00 PM', 'status' => 1]);
        // Same email but different phone must NOT match (old OR logic would match).
        $other = $this->appointment($slot, email: 'pat@example.test', phone: '555-9999');

        $this->actingAs($admin)
            ->get(route('admin.patients.show', $patient->id))
            ->assertOk()
            ->assertDontSee($other->doctor->name);
    }

    public function test_patient_show_matches_on_email_alone(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = Patient::create(['name' => 'Pat', 'email' => 'solo@example.test']);
        $slot = TimeSlot::create(['time' => '05:00 PM', 'status' => 1]);
        $appt = $this->appointment($slot, email: 'solo@example.test', phone: '555-8888');

        $this->actingAs($admin)
            ->get(route('admin.patients.show', $patient->id))
            ->assertOk()
            ->assertSee($appt->doctor->name);
    }

    public function test_cancel_token_expires_48h_after_day_end(): void
    {
        $slot = TimeSlot::create(['time' => '06:00 PM', 'status' => 1]);
        $old = $this->appointment($slot, date: now()->subDays(4)->toDateString());

        $this->get('/appointment/cancel/'.$old->cancellation_token)->assertStatus(410);

        $fresh = $this->appointment($slot, date: now()->addDay()->toDateString());
        $this->get('/appointment/cancel/'.$fresh->cancellation_token)->assertOk();
    }

    public function test_cancel_flow_strings_are_localized(): void
    {
        foreach (['ar', 'bn', 'de', 'en', 'es', 'fr', 'it', 'pt', 'ru', 'tr', 'zh'] as $locale) {
            app()->setLocale($locale);
            $this->assertNotSame('messages.public.cancel_success', __('messages.public.cancel_success'));
            $this->assertNotSame('messages.public.link_expired', __('messages.public.link_expired'));
        }
        app()->setLocale('en');
    }

    public function test_footer_quick_links_point_to_real_routes(): void
    {
        $html = view('frontend.layouts.partial.footer', [
            'setting' => null,
            'footerServices' => collect(),
        ])->render();

        foreach (['about', 'service', 'doctor', 'appointment', 'contact'] as $name) {
            $this->assertStringContainsString(route($name), $html);
        }
        $this->assertStringNotContainsString('href="#"', $html);
    }

    private function appointment(TimeSlot $slot, ?string $date = null, string $email = 'p2@example.test', string $phone = '555-0100'): Appointment
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'P2 Doctor '.uniqid(), 'slug' => 'p2-doc-'.uniqid(),
            'user_id' => $doctorUser->id, 'status' => 1,
        ]);

        return Appointment::create([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'patient_name' => 'P2 Patient',
            'email' => $email,
            'phone' => $phone,
            'gender' => 1,
            'visit_type' => 1,
            'consultation_mode' => 'in_person',
            'appointment_date' => $date ?? now()->addDays(2)->toDateString(),
            'cancellation_token' => Str::random(40),
            'status' => 0,
        ]);
    }
}
