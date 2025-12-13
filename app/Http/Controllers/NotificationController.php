<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\TestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class NotificationController extends Controller
{
    public function sendNotification(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        // ---------------------------------------------------------
        // 1. REGLA DE SEGURIDAD (ROLES)
        // ---------------------------------------------------------
        $allowedRoles = ['Editor', 'Revisor', 'Editor/Revisor'];

        if (!$currentUser->hasRole($allowedRoles)) {
            return response()->json([
                'message' => 'No tienes permisos suficientes (Rol requerido: Editor o Revisor).'
            ], 403);
        }

        // ---------------------------------------------------------
        // 2. VALIDACIÓN DE DATOS
        // ---------------------------------------------------------
        $request->validate([
            'recipient_id' => 'required|string', // Aceptamos emails separados por comas
            'message'      => 'required|string|max:1000',
        ]);

        // ---------------------------------------------------------
        // 3. PROCESAR DESTINATARIOS (LISTA DE EMAILS)
        // ---------------------------------------------------------
        $recipientInput = $request->recipient_id;
        
        // Convertimos el string "a@x.com, b@x.com" en array ['a@x.com', 'b@x.com']
        // array_map('trim', ...) elimina espacios en blanco accidentales
        $emails = array_map('trim', explode(',', $recipientInput));

        // Filtramos solo los que parezcan emails válidos para evitar errores en la query
        $validEmails = array_filter($emails, function($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });

        if (empty($validEmails)) {
            return response()->json([
                'message' => 'No se proporcionaron correos electrónicos válidos.'
            ], 422);
        }

        // Buscamos TODOS los usuarios que coincidan con esos emails
        $recipients = User::whereIn('email', $validEmails)->get();

        if ($recipients->isEmpty()) {
            return response()->json([
                'message' => 'Ninguno de los usuarios destinatarios fue encontrado.'
            ], 404);
        }

        // ---------------------------------------------------------
        // 4. ENVÍO DE LA NOTIFICACIÓN (A CADA UNO)
        // ---------------------------------------------------------
        $successCount = 0;
        $failedCount = 0;

        try {
            $fullMessage = "De {$currentUser->name}: " . $request->message;

            foreach ($recipients as $recipient) {
                // Evitar auto-envío (opcional)
                if ($recipient->id === $currentUser->id) {
                    continue; 
                }

                // Enviar notificación individualmente
                $recipient->notify(new TestNotification(
                    $fullMessage, 
                    $recipient->id, 
                    $currentUser->email // <--- Pasamos el email aquí
                ));
                $successCount++;
            }

            // Validamos si se envió al menos a alguien
            if ($successCount === 0 && $recipients->count() > 0) {
                 return response()->json([
                    'message' => 'No se envió ninguna notificación (quizás solo te intentaste enviar a ti mismo).'
                ], 422);
            }

            // Opcional: Construir mensaje de advertencia si faltaron usuarios
            $foundEmails = $recipients->pluck('email')->toArray();
            $missingEmails = array_diff($validEmails, $foundEmails);
            
            $responseText = 'Notificaciones enviadas correctamente.';
            if (count($missingEmails) > 0) {
                $responseText .= ' (Advertencia: No se encontraron los usuarios: ' . implode(', ', $missingEmails) . ')';
            }

            return response()->json([
                'message' => $responseText,
                'sent_count' => $successCount,
                'warning' => count($missingEmails) > 0 ? implode(', ', $missingEmails) : null
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error enviando notificación masiva: " . $e->getMessage());
            
            return response()->json([
                'message' => 'Ocurrió un error al intentar enviar las notificaciones.'
            ], 500);
        }
    }

    public function getUserNotifications()
    {
        $user = Auth::user();

        // Obtenemos las notificaciones. 
        // Puedes usar ->unreadNotifications para solo las no leídas
        // O ->notifications para todas (leídas y no leídas)
        $notifications = $user->notifications()->latest()->take(20)->get();

        // Opcional: Marcar como leídas apenas se recuperan (depende de tu lógica)
        // $user->unreadNotifications->markAsRead();

        return response()->json([
            'notifications' => $notifications
        ]);
    }

    // Método extra útil: Marcar una notificación específica como leída
    public function markAsRead($id) 
    {
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['message' => 'Notificación marcada como leída']);
    }

    public function markAllAsRead()
    {
    $user = Auth::user();

    // Esta línea mágica marca todas las pendientes como leídas en la BD
    // y rellena la columna 'read_at' con la fecha actual.
    $user->unreadNotifications->markAsRead();

    return response()->json([
        'message' => 'Todas las notificaciones marcadas como leídas.'
    ]);
    }
}