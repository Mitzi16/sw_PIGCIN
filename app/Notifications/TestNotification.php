<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage; // <--- IMPORTANTE
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // <--- IMPORTANTE INTERFACE

class TestNotification extends Notification implements ShouldBroadcast // <--- IMPLEMENTARLA
{
    use Queueable;

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function via($notifiable)
    {
        // Aquí defines que se guarde en BD Y se envíe por Reverb (broadcast)
        return ['database', 'broadcast']; 
    }

    // Estructura para el WebSocket
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message' => $this->message,
            'time' => now()->toDateTimeString()
        ]);
    }
    
    // Estructura para la Base de Datos
    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
        ];
    }
}