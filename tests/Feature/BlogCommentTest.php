<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\BlogComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_on_unpublished_post_is_not_found(): void
    {
        $blog = $this->blog(status: 0);

        $this->post(route('blog.comment.store', $blog->id), $this->payload())
            ->assertNotFound();

        $this->assertSame(0, BlogComment::count());
    }

    public function test_comment_on_published_post_is_queued_for_approval(): void
    {
        $blog = $this->blog(status: 1);

        $this->post(route('blog.comment.store', $blog->id), $this->payload())
            ->assertSessionHas('success');

        $comment = BlogComment::first();
        $this->assertNotNull($comment);
        $this->assertSame(0, (int) $comment->status);
    }

    public function test_honeypot_field_silently_drops_bot_comments(): void
    {
        $blog = $this->blog(status: 1);

        $this->post(route('blog.comment.store', $blog->id), array_merge($this->payload(), [
            'website' => 'https://spam.example',
        ]))
            ->assertSessionHas('success');

        $this->assertSame(0, BlogComment::count());
    }

    public function test_tag_filter_matches_the_exact_tag_only(): void
    {
        $this->blog(status: 1, slug: 'health-post', tags: 'health');
        $this->blog(status: 1, slug: 'healthy-post', tags: 'healthy');

        $this->get(route('blog', ['tag' => 'health']))
            ->assertOk()
            ->assertSee('health-post')
            ->assertDontSee('healthy-post');
    }

    private function blog(int $status, string $slug = 'blog-post', string $tags = ''): Blog
    {
        return Blog::create([
            'title' => 'Title '.$slug,
            'slug' => $slug,
            'excerpt' => 'Excerpt',
            'content' => '<p>Body</p>',
            'author' => 'Author',
            'status' => $status,
            'tags' => $tags,
        ]);
    }

    private function payload(): array
    {
        return [
            'name' => 'Commenter',
            'email' => 'commenter@example.test',
            'comment' => 'Nice article.',
        ];
    }
}
