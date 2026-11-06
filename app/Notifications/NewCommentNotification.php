<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification
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
            'message' => $this->comment->user->name . ' comentó en tu publicación: "' . $this->comment->post->title . '"',
            'url' => route('forum.show', $this->comment->post->slug),
            'icon' => 'message-square',
            'color' => 'emerald'
        ];
    }
}