<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'media_path', 
        'media_type', 
        'caption', 
        'expires_at'
    ];

    // Convertimos expires_at automáticamente a formato de fecha
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function views()
    {
        return $this->hasMany(StoryView::class);
    }
    
    // Relación: Una historia tiene muchos likes
    public function likes()
    {
        return $this->hasMany(StoryLike::class);
    }

    // Nos dirá si el usuario actual ya vio esta historia
    public function isViewedBy(User $user)
    {
        return $this->views()->where('user_id', $user->id)->exists();
    }
}