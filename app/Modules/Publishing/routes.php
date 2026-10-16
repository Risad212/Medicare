<?php

use App\Modules\Publishing\Http\Controllers\Admin\BlogCommentController as AdminBlogCommentController;
use App\Modules\Publishing\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Modules\Publishing\Http\Controllers\Admin\CategoryController as AdminBlogCategoryController;
use App\Modules\Publishing\Http\Controllers\Admin\TagController as AdminBlogTagController;
use App\Modules\Publishing\Http\Controllers\Frontend\BlogCommentController;
use App\Modules\Publishing\Http\Controllers\Frontend\BlogController as FrontBlogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin', 'module:publishing', 'staff.modules'])->group(function () {
    Route::resource('/admin/blogs', AdminBlogController::class)->names('admin.blogs')->except(['show']);

    Route::resource('/admin/blog/categories', AdminBlogCategoryController::class)->names('admin.categories')->except(['show']);

    Route::resource('/admin/blog/tags', AdminBlogTagController::class)->names('admin.tags')->except(['show']);

    Route::resource('/admin/comments', AdminBlogCommentController::class)
        ->names('admin.comments')
        ->only(['index', 'edit', 'update', 'destroy']);
});

Route::middleware(['web', 'module:publishing'])->group(function () {
    Route::get('/blog', [FrontBlogController::class, 'index'])->name('blog');

    Route::get('/blog/{slug}', [FrontBlogController::class, 'show'])->name('blog.show');

    Route::post('/blog/{blog_id}/comment', [BlogCommentController::class, 'store'])->middleware('throttle:10,1')->name('blog.comment.store');
});
