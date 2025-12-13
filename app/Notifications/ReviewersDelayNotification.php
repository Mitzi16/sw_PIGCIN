<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ReviewersDelayNotification extends Notification
{
    use Queueable;

    public $articleTitle;
    public $reviewers;

    public function __construct($articleTitle, $reviewers)
    {
        $this->articleTitle = $articleTitle;
        $this->reviewers = $reviewers;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return $this->getNotificationData();
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->getNotificationData());
    }

    private function getNotificationData()
    {
        $names = collect($this->reviewers)->pluck('name')->join(', ');
        $emails = collect($this->reviewers)->pluck('email')->toArray();

        return [
            // Estandarizamos el ID del mensaje para ambos casos
            'message' => "Retraso: Los revisores {$names} no han calificado el artículo '{$this->articleTitle}'.",
            'article_title' => $this->articleTitle,
            'reviewer_names' => $names,
            'reviewer_emails' => $emails,
            'type' => 'review_delay', // Opcional: útil si tienes diferentes tipos de notificaciones
            'role_target' => 'Editor', // Indica que es para el rol Editor
        ];
    }
}