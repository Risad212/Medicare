<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishingModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_module_hides_publishing_routes_but_keeps_core(): void
    {
        config(['modules.publishing' => false]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/blogs')->assertNotFound();
        $this->actingAs($admin)->get('/admin/blog/categories')->assertNotFound();
        $this->actingAs($admin)->get('/admin/blog/tags')->assertNotFound();
        $this->actingAs($admin)->get('/admin/comments')->assertNotFound();
        $this->get('/blog')->assertNotFound();

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->get('/')->assertOk();
    }

    public function test_enabled_module_serves_blog(): void
    {
        Blog::create([
            'title' => 'Hello', 'slug' => 'hello-'.uniqid(), 'content' => '<p>Hi</p>',
            'author' => 'A', 'status' => 1,
        ]);

        $this->get('/blog')->assertOk()->assertSee('Hello');
    }
}
