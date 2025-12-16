<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;

class TestNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public $message;
    public $userId;
    public $senderEmail; // <--- 1. NUEVA PROPIEDAD para el remitente

    // 2. Modificamos el constructor para recibir el email del que envía
    public function __construct($message, $userId, $senderEmail)
    {
        $this->message = $message;
        $this->userId = $userId;
        $this->senderEmail = $senderEmail;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast']; 
    }

    public function broadcastOn()
    {
        $nombreCanal = 'App.Models.User.' . $this->userId;
        return new PrivateChannel($nombreCanal);
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message' => $this->message,
            'reply_to' => $this->senderEmail, // <--- 3. CLAVE PARA EL FRONTEND (Tiempo real)
            'role_target' => 'General', // Indica que es para todos los roles,
            'time' => now()->toDateTimeString()
        ]);
    }
    
    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
            'reply_to' => $this->senderEmail, // <--- 4. CLAVE PARA EL FRONTEND (Base de datos),
            'role_target' => 'General' // Indica que es para todos los roles,
        ];
    }
}