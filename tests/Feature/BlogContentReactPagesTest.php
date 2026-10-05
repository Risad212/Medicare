<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\BlogComment;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogContentReactPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_management_and_comment_moderation_pages_use_react(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Health tips', 'slug' => 'health-tips']);
        $tag = Tag::create(['name' => 'Wellness', 'slug' => 'wellness']);
        $blog = Blog::create([
            'title' => 'A React-managed article',
            'slug' => 'react-managed-article',
            'content' => '<p>Full article content.</p>',
            'category' => $category->name,
            'tags' => $tag->name,
            'status' => 1,
        ]);
        BlogComment::create([
            'blog_id' => $blog->id,
            'name' => 'Reader',
            'email' => 'reader@example.test',
            'comment' => 'A useful article.',
            'status' => 0,
        ]);

        $this->actingAs($admin)
            ->get('/admin/blogs')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Blogs/Index')
                ->where('blogs.total', 1)
                ->where('blogs.data.0.title', 'A React-managed article')
            );

        $this->actingAs($admin)
            ->get('/admin/blogs/create')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Blogs/Create')
                ->where('categories.0.name', 'Health tips')
                ->where('tags.0.name', 'Wellness')
            );

        $this->actingAs($admin)
            ->get("/admin/blogs/{$blog->id}/edit")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Blogs/Edit')
                ->where('blog.content', '<p>Full article content.</p>')
                ->where('blog.category', 'Health tips')
            );

        $this->actingAs($admin)
            ->get('/admin/blog/categories')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Taxonomy/Index')
                ->where('kind', 'categories')
                ->where('records.0.slug', 'health-tips')
            );

        $this->actingAs($admin)
            ->get('/admin/blog/categories/create')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Taxonomy/Form')
                ->where('kind', 'categories')
            );

        $this->actingAs($admin)
            ->get('/admin/blog/tags')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Taxonomy/Index')
                ->where('kind', 'tags')
                ->where('records.0.name', 'Wellness')
            );

        $this->actingAs($admin)
            ->get('/admin/blog/tags/create')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Taxonomy/Form')
                ->where('kind', 'tags')
            );

        $this->actingAs($admin)
            ->get('/admin/comments')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Comments/Index')
                ->where('comments.0.blogTitle', 'A React-managed article')
                ->where('comments.0.status', 0)
            );
    }

    public function test_blog_create_update_and_taxonomy_crud_keep_existing_workflows(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->from('/admin/blogs/create')->post('/admin/blogs', [
            'title' => 'New Health Article',
            'excerpt' => '<b>A short overview</b>',
            'content' => '<p>Safe content</p><script>alert(1)</script>',
            'category' => '',
            'tags' => '',
            'status' => '1',
        ])->assertRedirect('/admin/blogs/create');

        $blog = Blog::where('title', 'New Health Article')->firstOrFail();
        $this->assertSame('A short overview', $blog->excerpt);
        $this->assertSame(1, (int) $blog->status);
        $this->assertStringNotContainsString('<script', $blog->content);

        $this->actingAs($admin)->from("/admin/blogs/{$blog->id}/edit")->post("/admin/blogs/{$blog->id}", [
            '_method' => 'PUT',
            'title' => 'Updated Health Article',
            'excerpt' => 'Updated excerpt',
            'content' => '<h2>Updated content</h2>',
        ])->assertRedirect("/admin/blogs/{$blog->id}/edit");

        $this->assertSame('Updated Health Article', $blog->fresh()->title);
        $this->assertSame(0, (int) $blog->fresh()->status);

        $this->actingAs($admin)->from('/admin/blog/categories/create')->post('/admin/blog/categories', [
            'name' => 'Cardiology',
        ])->assertRedirect('/admin/blog/categories/create');

        $category = Category::where('name', 'Cardiology')->firstOrFail();
        $this->assertSame('cardiology', $category->slug);

        $this->actingAs($admin)->from('/admin/blog/tags/create')->post('/admin/blog/tags', [
            'name' => 'Prevention',
        ])->assertRedirect('/admin/blog/tags/create');

        $tag = Tag::where('name', 'Prevention')->firstOrFail();
        $this->assertSame('prevention', $tag->slug);

        $this->actingAs($admin)->put("/admin/comments/{$blog->comments()->create([
            'name' => 'Approver',
            'email' => 'approver@example.test',
            'comment' => 'Please review',
            'status' => 0,
        ])->id}")
            ->assertSessionHas('success', 'Comment approved!');

        $this->assertDatabaseHas('blog_comments', ['name' => 'Approver', 'status' => 1]);
    }
}
