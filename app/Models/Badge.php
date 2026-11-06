<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = [
        'name',
        'color',
        'icon',
        'requirement_type',
        'requirement_goal'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}