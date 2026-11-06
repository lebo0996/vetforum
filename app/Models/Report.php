<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'reporter_id',
        'reported_id',
        'status_id',  // <--- Nuevo
        'post_id',    // <--- Nuevo
        'comment_id', // <--- Nuevo
        'reason',
        'description',
        'status',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reported()
    {
        return $this->belongsTo(User::class, 'reported_id');
    }

    // --- NUEVAS RELACIONES ---
    public function statusPost() // Le llamo statusPost porque 'status' ya se usa para 'pendiente/resuelto'
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }
}