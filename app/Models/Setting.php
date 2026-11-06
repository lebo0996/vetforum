<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'site_name',
        'contact_email',
        'site_description',
        'allow_registrations',
        'allow_guest_posting',
        'posts_require_approval',
        'logo',     // <-- Nuevo
        'favicon',  // <-- Nuevo
    ];
}