<?php

namespace Tests\Feature;

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
}
