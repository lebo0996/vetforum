<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewStatusCommentNotification extends Notification
{
    use Queueable;

    public $comment;

    public function __construct($comment)
    {
        $this->comment = $comment;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            // Mensaje que saldrá en la campana
            'message' => $this->comment->user->name . ' comentó en tu estado del muro.',
            // Si tienes una ruta para ver un estado individual ponla aquí, si no, mándalo al index del muro
            'url' => route('statuses.index'), 
            'icon' => 'message-circle',
            'color' => 'blue' // Color distinto para diferenciarlo del foro
        ];
    }
}