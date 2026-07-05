<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        'title', 'slug', 'excerpt', 'content',
        'image', 'author', 'status', 'order',
        'category', 'tags'
    ];

    public function comments()
    {
        return $this->hasMany(BlogComment::class)->where('status', 1);
    }
}