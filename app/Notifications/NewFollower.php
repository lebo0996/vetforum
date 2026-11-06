<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewFollower extends Notification
{
    use Queueable;

    public $follower;

    // Recibimos al usuario que acaba de dar clic en "Seguir"
    public function __construct($follower)
    {
        $this->follower = $follower;
    }

    // Le decimos a Laravel que guarde esto en la Base de Datos
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    // Estructuramos la información que guardaremos
    public function toDatabase(object $notifiable): array
    {
        return [
            'follower_id' => $this->follower->id,
            'follower_name' => $this->follower->name,
            'follower_avatar' => $this->follower->avatar,
            'message' => 'ha comenzado a seguirte.',
            'url' => route('users.public', $this->follower->id) // Link para visitar su perfil
        ];
    }
}