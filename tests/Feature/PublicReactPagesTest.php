<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Blog;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicReactPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_listing_pages_use_react_components(): void
    {
        $pages = [
            '/' => 'Public/Home',
            '/about' => 'Public/About',
            '/service' => 'Public/Services/Index',
            '/doctor' => 'Public/Doctors/Index',
            '/blog' => 'Public/Blog/Index',
            '/contact' => 'Public/Contact',
            '/appointment' => 'Public/Appointment',
        ];

        foreach ($pages as $url => $component) {
            $this->get($url)->assertInertia(fn ($page) => $page->component($component));
        }
    }

    public function test_public_detail_pages_share_only_public_record_data(): void
    {
        $doctor = Doctor::create([
            'name' => 'Dr Public Profile',
            'slug' => 'dr-public-profile',
            'degree' => 'MD',
            'department' => 'Cardiology',
            'specialist' => 'Heart care',
            'phone' => '01700000002',
            'status' => 1,
        ]);
        $service = Service::create([
            'title' => 'Public Service',
            'slug' => 'public-service',
            'description' => 'Service details.',
            'status' => 1,
        ]);
        $blog = Blog::create([
            'title' => 'Public Article',
            'slug' => 'public-article',
            'excerpt' => 'A short public summary.',
            'content' => '<p onclick="alert(1)">Useful <strong>information</strong>.</p><script>alert(1)</script><a href="javascript:alert(1)">Unsafe link</a>',
            'author' => 'MediCare',
            'status' => 1,
        ]);

        $this->get("/doctor/{$doctor->id}")
            ->assertInertia(fn ($page) => $page
                ->component('Public/Doctors/Show')
                ->where('doctor.name', 'Dr Public Profile')
                ->missing('doctor.user')
            );

        $this->get('/service/public-service')
            ->assertInertia(fn ($page) => $page
                ->component('Public/Services/Show')
                ->where('service.title', 'Public Service')
            );

        $this->get('/blog/public-article')
            ->assertInertia(fn ($page) => $page
                ->component('Public/Blog/Show')
                ->where('blog.title', 'Public Article')
                ->where('blog.content', '<p>Useful <strong>information</strong>.</p><a>Unsafe link</a>')
            );
    }

    public function test_blog_content_preserves_safe_formatting_and_removes_active_content(): void
    {
        $blog = Blog::create([
            'title' => 'Formatted Article',
            'slug' => 'formatted-article',
            'content' => '<h2>Section</h2><p>Read <a href="https://example.test" target="_blank" onclick="alert(1)">more</a>.</p><img src="javascript:alert(1)" onerror="alert(1)"><style>body{display:none}</style>',
            'status' => 1,
        ]);

        $this->get("/blog/{$blog->slug}")
            ->assertInertia(fn ($page) => $page
                ->where('blog.content', '<h2>Section</h2><p>Read <a href="https://example.test" target="_blank" rel="noopener noreferrer">more</a>.</p>')
            );
    }

    public function test_appointment_cancellation_page_is_a_react_page(): void
    {
        $doctor = Doctor::create([
            'name' => 'Dr Cancellation',
            'slug' => 'dr-cancellation',
            'status' => 1,
        ]);
        $slot = TimeSlot::create(['time' => '12:00 PM', 'status' => 1]);
        $appointment = Appointment::create([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'patient_name' => 'Public Patient',
            'age' => 30,
            'gender' => 1,
            'phone' => '01700000003',
            'email' => 'public-patient@example.test',
            'visit_type' => 1,
            'appointment_date' => now()->addDay()->toDateString(),
            'status' => 0,
            'cancellation_token' => 'public-cancel-token',
        ]);

        $this->get('/appointment/cancel/public-cancel-token')
            ->assertInertia(fn ($page) => $page
                ->component('Public/AppointmentCancel')
                ->where('appointment.name', 'Public Patient')
                ->where('appointment.status', 0)
                ->where('token', 'public-cancel-token')
            );
    }
}
