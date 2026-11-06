<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewLikeNotification extends Notification
{
    use Queueable;

    public $post;
    public $liker;

    public function __construct($post, $liker)
    {
        $this->post = $post;
        $this->liker = $liker;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->liker->name . ' le dio "Me gusta" a tu caso: "' . $this->post->title . '"',
            'url' => route('forum.show', $this->post->slug),
            'icon' => 'heart',
            'color' => 'rose'
        ];
    }
}