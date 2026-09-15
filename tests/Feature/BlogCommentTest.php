<?php

namespace Tests\Feature;

use App\Models\BlogComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class BlogCommentTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    public function test_comment_is_submitted_pending_approval(): void
    {
        $blog = $this->makeBlog();

        $this->from(route('blog.show', $blog->slug))->post(route('blog.comment.store', $blog->id), [
            'name' => 'Sara Khan',
            'email' => 'sara@example.com',
            'comment' => 'Very helpful article!',
        ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Comment submitted — waiting for approval.');

        $this->assertDatabaseHas('blog_comments', [
            'blog_id' => $blog->id,
            'name' => 'Sara Khan',
            'email' => 'sara@example.com',
            'comment' => 'Very helpful article!',
            'status' => 0,
        ]);
    }

    public function test_comment_requires_all_fields(): void
    {
        $blog = $this->makeBlog();

        $this->from('/')->post(route('blog.comment.store', $blog->id), [])
            ->assertSessionHasErrors(['name', 'email', 'comment']);
    }

    public function test_comment_rejects_invalid_email(): void
    {
        $blog = $this->makeBlog();

        $this->from('/')->post(route('blog.comment.store', $blog->id), [
            'name' => 'Sara Khan',
            'email' => 'nope',
            'comment' => 'Hello',
        ])->assertSessionHasErrors('email');
    }

    public function test_commenting_on_missing_blog_returns_404(): void
    {
        $this->post(route('blog.comment.store', 999999), [
            'name' => 'Sara Khan',
            'email' => 'sara@example.com',
            'comment' => 'Hello',
        ])->assertNotFound();
    }

    public function test_only_approved_comments_are_visible_on_blog_page(): void
    {
        $blog = $this->makeBlog();

        BlogComment::create([
            'blog_id' => $blog->id,
            'name' => 'Approved Reviewer',
            'email' => 'approved@example.com',
            'comment' => 'This one is approved.',
            'status' => 1,
        ]);

        BlogComment::create([
            'blog_id' => $blog->id,
            'name' => 'Pending Reviewer',
            'email' => 'pending@example.com',
            'comment' => 'This one is pending.',
            'status' => 0,
        ]);

        $this->get(route('blog.show', $blog->slug))
            ->assertOk()
            ->assertSee('Approved Reviewer')
            ->assertDontSee('Pending Reviewer');
    }
}
