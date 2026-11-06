<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $fillable = ['user_id', 'content', 'image', 'repost_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación de Repost / Republicar
    public function repost()
    {
        return $this->belongsTo(Status::class, 'repost_id');
    }

    public function statusLikes()
    {
        return $this->hasMany(StatusLike::class);
    }

    public function comments()
    {
        return $this->hasMany(StatusComment::class)->latest();
    }
    public function likes()
    {
        return $this->hasMany(StatusLike::class, 'status_id');
    }
    // Relación para saber cuántas veces han republicado este status
    public function reposts()
    {
        return $this->hasMany(Status::class, 'repost_id');
    }
    public function poll()
    {
    return $this->hasOne(Poll::class)->with('options.votes');
    }
}