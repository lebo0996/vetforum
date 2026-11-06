<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    protected $fillable = ['status_id', 'question'];

    public function status() { return $this->belongsTo(Status::class); }
    public function options() { return $this->hasMany(PollOption::class); }
    public function votes() { return $this->hasMany(PollVote::class); }
}