<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\BlogComment;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class AdminBlogTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    public function test_blog_pages_load(): void
    {
        $this->actingAs($this->makeAdmin())->get(route('admin.blogs.index'))->assertOk();
        $this->actingAs($this->makeAdmin())->get(route('admin.blogs.create'))->assertOk();
    }

    public function test_admin_creates_blog_post(): void
    {
        Storage::fake('public');

        $this->actingAs($this->makeAdmin())
            ->from(route('admin.blogs.create'))
            ->post(route('admin.blogs.store'), [
                'title' => 'Healthy Heart Guide',
                'content' => 'Full article body.',
                'excerpt' => 'Short excerpt',
                'category' => 'Cardiology',
                'tags' => 'heart, wellness',
                'status' => 1,
                'image' => UploadedFile::fake()->image('blog.jpg', 800, 400),
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Blog post added successfully!');

        $this->assertDatabaseHas('blogs', [
            'title' => 'Healthy Heart Guide',
            'category' => 'Cardiology',
            'status' => 1,
        ]);

        $blog = Blog::where('title', 'Healthy Heart Guide')->first();
        $this->assertNotNull($blog->slug);
        Storage::disk('public')->assertExists($blog->image);
    }

    public function test_blog_post_defaults_to_draft_without_status(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.blogs.store'), [
                'title' => 'Draft Post',
                'content' => 'Draft content.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('blogs', ['title' => 'Draft Post', 'status' => 0]);
    }

    public function test_blog_store_validates_title_and_content(): void
    {
        $this->actingAs($this->makeAdmin())
            ->from(route('admin.blogs.create'))
            ->post(route('admin.blogs.store'), [])
            ->assertSessionHasErrors(['title', 'content']);
    }

    public function test_admin_updates_blog_post(): void
    {
        $blog = $this->makeBlog();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.blogs.update', $blog->id), [
                'title' => 'Updated Title',
                'content' => 'Updated body.',
                'status' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Blog post updated successfully!');

        $this->assertDatabaseHas('blogs', ['id' => $blog->id, 'title' => 'Updated Title']);
    }

    public function test_admin_deletes_blog_post(): void
    {
        $blog = $this->makeBlog();

        $this->actingAs($this->makeAdmin())
            ->from(route('admin.blogs.index'))
            ->delete(route('admin.blogs.destroy', $blog->id))
            ->assertRedirect()
            ->assertSessionHas('success', 'Blog post deleted successfully!');

        $this->assertDatabaseMissing('blogs', ['id' => $blog->id]);
    }

    public function test_admin_approves_and_deletes_comments(): void
    {
        $comment = BlogComment::create([
            'blog_id' => $this->makeBlog()->id,
            'name' => 'Commenter',
            'email' => 'commenter@example.com',
            'comment' => 'Nice post',
            'status' => 0,
        ]);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.comments.index'))
            ->assertOk();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.comments.update', $comment->id))
            ->assertRedirect()
            ->assertSessionHas('success', 'Comment approved!');

        $this->assertDatabaseHas('blog_comments', ['id' => $comment->id, 'status' => 1]);

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.comments.destroy', $comment->id))
            ->assertRedirect()
            ->assertSessionHas('success', 'Comment deleted!');

        $this->assertDatabaseMissing('blog_comments', ['id' => $comment->id]);
    }

    public function test_admin_crud_for_categories_and_tags(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.categories.store'), ['name' => 'Cardiology'])
            ->assertRedirect()
            ->assertSessionHas('success', 'Category added successfully!');

        $category = Category::where('name', 'Cardiology')->first();
        $this->assertNotNull($category);
        $this->assertEquals('cardiology', $category->slug);

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.categories.update', $category->id), ['name' => 'Heart Care'])
            ->assertSessionHas('success', 'Category updated successfully!');

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Heart Care']);

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.categories.destroy', $category->id))
            ->assertSessionHas('success', 'Category deleted successfully!');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.tags.store'), ['name' => 'wellness'])
            ->assertSessionHas('success', 'Tag added successfully!');

        $tag = Tag::where('name', 'wellness')->first();
        $this->assertNotNull($tag);

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.tags.update', $tag->id), ['name' => 'wellness-new'])
            ->assertSessionHas('success', 'Tag updated successfully!');

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.tags.destroy', $tag->id))
            ->assertSessionHas('success', 'Tag deleted successfully!');

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_category_and_tag_names_must_be_unique(): void
    {
        Category::create(['name' => 'Cardiology', 'slug' => 'cardiology']);
        Tag::create(['name' => 'wellness', 'slug' => 'wellness']);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.categories.store'), ['name' => 'Cardiology'])
            ->assertSessionHasErrors('name');

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.tags.store'), ['name' => 'wellness'])
            ->assertSessionHasErrors('name');
    }
}
