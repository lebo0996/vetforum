<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'user_id', 
        'category_id', 
        'title', 
        'slug', 
        'content', 
        'image', 
        'is_approved', 
        'is_pinned'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    // RELACIÓN DE LIKES PARA LOS POSTS DEL FORO
    public function likes()
    {
        return $this->hasMany(PostLike::class); 
        // Si tu tabla de likes en base de datos se llama distinto, 
        // por ejemplo 'forum_post_likes', cámbialo a: 
        // return $this->hasMany(ForumPostLike::class, 'post_id');
    }
}