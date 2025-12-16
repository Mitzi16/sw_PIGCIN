<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ArticleReview; // Tu modelo
use App\Notifications\ReviewersDelayNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckPendingReviews extends Command
{
    protected $signature = 'reviews:check-delays';
    protected $description = 'Notificar a editores sobre revisiones atrasadas (5 días hábiles)';

    public function handle()
    {
        // 1. Calcular la fecha objetivo: Hoy menos 5 días HÁBILES
        // Si hoy es Lunes, busca asignaciones del Lunes pasado.
        $targetDate = Carbon::now()->subWeekdays(5)->format('Y-m-d');

        $this->info("Buscando revisiones asignadas el: $targetDate que sigan pendientes...");

        // 2. Consulta usando tu modelo ArticleReview
        $pendingReviews = ArticleReview::query()
            // Filtramos por fecha de creación (fecha de asignación)
            ->whereDate('created_at', $targetDate)
            
            // LOGICA DE "PENDIENTE":
            // Asumo que si el 'comment' está null, no han calificado.
            // SI usas un estatus específico, cambia esto por: ->where('article_status_id', ID_PENDIENTE)
            ->whereNull('comment') 
            
            // Cargamos relaciones necesarias para no hacer mil consultas
            ->with(['article.editor', 'reviewer'])
            ->get();

        if ($pendingReviews->isEmpty()) {
            $this->info("No hay revisiones atrasadas para esta fecha.");
            return;
        }

        // 3. Agrupar por Artículo (Article ID)
        // Para que si un artículo tiene 2 revisores atrasados, solo mandemos 1 notificación al editor.
        $groupedReviews = $pendingReviews->groupBy('article_id');

        foreach ($groupedReviews as $articleId => $reviews) {
            
            // Obtenemos el artículo del primer review del grupo
            $article = $reviews->first()->article;

            // Validamos que exista el artículo y tenga editor
            if (!$article || !$article->editor) {
                continue; 
            }

           // 4. Obtener los OBJETOS de los revisores (no solo los nombres string)
            // Esto crea una colección de usuarios con id, name, email, etc.
            $reviewers = $reviews->map(function ($review) {
                return $review->reviewer;
            })->filter(); // filter() quita nulos por si algún usuario fue borrado

            // Si por alguna razón extraña no quedaron revisores validos, saltamos
            if ($reviewers->isEmpty()) continue;

            // Para el log de consola, podemos sacar los nombres aquí
            $namesForLog = $reviewers->pluck('name')->join(', ');

            // 5. Enviar Notificación al Editor
            try {
                // Pasamos la COLECCIÓN COMPLETA de $reviewers, no el string
                $article->editor->notify(new ReviewersDelayNotification(
                    $article->title,
                    $reviewers 
                ));
                
                $this->info("Notificación enviada al editor {$article->editor->name} sobre: {$namesForLog}");
                
            } catch (\Exception $e) {
                Log::error("Error enviando notificación de retraso: " . $e->getMessage());
            }
        }

        $this->info('Proceso finalizado.');
    }
}