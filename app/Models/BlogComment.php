<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogComment extends Model
{
    protected $fillable = [
        'blog_id', 'name', 'email', 'comment', 'status'
    ];

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
