<?php

namespace Tests\Feature;

use App\Models\Blog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogContentXssTest extends TestCase
{
    use RefreshDatabase;

    public function test_stored_script_content_is_inert_on_the_public_page(): void
    {
        $blog = Blog::create([
            'title' => 'Unsafe Post',
            'slug' => 'unsafe-post',
            'excerpt' => 'excerpt',
            'content' => '<p>safe</p><script>alert(1)</script><img src=x onerror="alert(2)">',
            'author' => 'Author',
            'status' => 1,
        ]);

        $response = $this->get(route('blog.show', $blog->slug))->assertOk();

        $response->assertSee('<p>safe</p>', false);
        $content = $response->getContent();
        $this->assertStringNotContainsString('<script>alert(1)', $content);
        $this->assertStringNotContainsString('onerror', $content);
        $this->assertStringNotContainsString('<img src=x', $content);
    }
}
