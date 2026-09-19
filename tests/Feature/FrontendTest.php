<?php

namespace Tests\Feature;

use App\Models\AboutSetting;
use App\Models\SeoSetting;
use App\Models\Service;
use App\Models\ServiceSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class FrontendTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    public function test_all_public_pages_load(): void
    {
        $this->get(route('home'))->assertOk();
        $this->get(route('about'))->assertOk();
        $this->get(route('service'))->assertOk();
        $this->get(route('doctor'))->assertOk();
        $this->get(route('blog'))->assertOk();
        $this->get(route('contact'))->assertOk();
        $this->get(route('appointment'))->assertOk();
    }

    public function test_doctor_profile_page_renders_for_existing_doctor(): void
    {
        $doctor = $this->makeDoctor();

        $this->get(route('doctor.show', $doctor->id))
            ->assertOk()
            ->assertSee($doctor->name);
    }

    public function test_doctor_profile_page_returns_404_for_missing_doctor(): void
    {
        $this->get(route('doctor.show', 999999))->assertNotFound();
    }

    public function test_blog_detail_page_renders_published_post(): void
    {
        $blog = $this->makeBlog(['slug' => 'public-post']);

        $this->get(route('blog.show', $blog->slug))
            ->assertOk()
            ->assertSee($blog->title);
    }

    public function test_blog_detail_page_returns_404_for_unpublished_or_missing_post(): void
    {
        $unpublished = $this->makeBlog(['slug' => 'draft-post', 'status' => 0]);

        $this->get(route('blog.show', $unpublished->slug))->assertNotFound();
        $this->get(route('blog.show', 'no-such-post'))->assertNotFound();
    }

    public function test_blog_index_filters_by_category_and_tag(): void
    {
        $this->makeBlog(['category' => 'Cardiology', 'tags' => 'heart']);
        $this->makeBlog(['category' => 'Dental', 'tags' => 'teeth']);

        $this->get(route('blog', ['category' => 'Cardiology']))
            ->assertOk()
            ->assertSee('Sample Blog Post');

        $this->get(route('blog', ['tag' => 'teeth']))
            ->assertOk();
    }

    public function test_blog_index_hides_unpublished_posts(): void
    {
        $this->makeBlog(['title' => 'Visible Draft Only', 'status' => 0]);

        $this->get(route('blog'))->assertOk()->assertDontSee('Visible Draft Only');
    }

    public function test_home_blog_section_shows_published_and_hides_drafts(): void
    {
        $this->makeBlog(['title' => 'Homepage Featured Post', 'status' => 1]);
        $this->makeBlog(['title' => 'Homepage Hidden Draft', 'status' => 0]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Homepage Featured Post')
            ->assertDontSee('Homepage Hidden Draft');
    }

    public function test_service_page_renders_admin_seo(): void
    {
        SeoSetting::create([
            'page' => 'service',
            'meta_title' => 'Service Meta Title XYZ',
        ]);

        $this->get(route('service'))
            ->assertOk()
            ->assertSee('Service Meta Title XYZ');
    }

    public function test_appointment_page_renders_admin_seo(): void
    {
        SeoSetting::create([
            'page' => 'appointment',
            'meta_title' => 'Appointment Meta Title XYZ',
        ]);

        $this->get(route('appointment'))
            ->assertOk()
            ->assertSee('Appointment Meta Title XYZ');
    }

    public function test_doctor_list_shows_page_label(): void
    {
        $this->get(route('doctor'))
            ->assertOk()
            ->assertSee('Our Doctors');
    }

    public function test_about_page_renders_configured_content(): void
    {
        AboutSetting::create([
            'title' => 'Configured About Title XYZ',
            'mission_title' => 'Configured Mission XYZ',
        ]);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Configured About Title XYZ')
            ->assertSee('Configured Mission XYZ');
    }

    public function test_service_page_renders_configured_content_and_cards(): void
    {
        ServiceSetting::create([
            'emergency_title' => 'Configured Emergency XYZ',
            'prevention_1_title' => 'Configured Prevention XYZ',
        ]);
        Service::create([
            'title' => 'Configured Service Card XYZ',
            'description' => 'Card body.',
            'status' => 1,
        ]);

        $this->get(route('service'))
            ->assertOk()
            ->assertSee('Configured Emergency XYZ')
            ->assertSee('Configured Prevention XYZ')
            ->assertSee('Configured Service Card XYZ');
    }

    public function test_home_renders_dynamic_service_cards(): void
    {
        Service::create(['title' => 'Home Service Card XYZ', 'status' => 1]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Home Service Card XYZ');
    }
}
