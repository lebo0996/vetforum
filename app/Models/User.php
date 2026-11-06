<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 
    'username', 
    'email', 
    'password', 
    'avatar', 
    'specialty', 
    'banner_color',
    'role',
    'is_verified',
    'is_active', 
    'suspended_until'
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail // <-- 2. Añade "implements MustVerifyEmail"
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'suspended_until' => 'datetime',
        ];
    }
    
    public function statuses()
    {
        return $this->hasMany(Status::class)->latest();
    }

    // Usuarios que siguen a este usuario (Seguidores)
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'following_id', 'follower_id')->withTimestamps();
    }

    // Usuarios a los que este usuario sigue (Seguidos)
    public function following()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'following_id')->withTimestamps();
    }
    
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
    
    public function badges()
    {
        return $this->belongsToMany(Badge::class);
    }
}