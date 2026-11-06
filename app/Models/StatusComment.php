<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusComment extends Model
{
    protected $fillable = ['user_id', 'status_id', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}