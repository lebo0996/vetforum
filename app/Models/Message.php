<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    // Asegúrate de que 'image' esté en esta lista:
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'body',
        'image', // <--- ¡ESTE ES EL CAMPO QUE FALTABA!
        'read_at',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}