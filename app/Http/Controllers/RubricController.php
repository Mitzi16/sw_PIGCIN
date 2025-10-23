<?php

namespace App\Http\Controllers;

use App\Models\Rubric;
use Illuminate\Http\Request;

class RubricController extends Controller
{
    /**
     * Devuelve la rúbrica con sus preguntas y respuestas
     * según el tipo de artículo.
     */
    public function getByArticleType($articleTypeId)
    {
        $rubric = Rubric::where('article_type_id', $articleTypeId)
            ->with([
                'items.answers' // relaciones que traeremos
            ])
            ->first();

        if (!$rubric) {
            return response()->json(['message' => 'No se encontró rúbrica para este tipo de artículo'], 404);
        }

        return response()->json([
            'rubric' => [
                'id' => $rubric->id,
                'title' => $rubric->title,
                'items' => $rubric->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'question' => $item->question,
                        'answers' => $item->answers->map(function ($answer) {
                            return [
                                'id' => $answer->id,
                                'text' => $answer->text,
                                'score' => $answer->score
                            ];
                        })
                    ];
                })
            ]
        ]);
    }

    public function storeEvaluation(Request $request)
    {
        $data = $request->validate([
            'article_id' => 'required|integer',
            'reviewer_id' => 'required|integer|exists:users,id', //  Validar el reviewer_id
            'answers' => 'required|array',
            'answers.*.rubric_item_id' => 'required|integer',
            'answers.*.rubric_answer_id' => 'required|integer',
            'answers.*.score' => 'required|integer',
        ]);

        // 2. Usamos el reviewer_id del request
        $reviewerId = $data['reviewer_id'];

        $isAssigned = \App\Models\ArticleReview::where('article_id', $data['article_id'])
                                              ->where('reviewer_id', $reviewerId)
                                              ->exists();
        
        if (!$isAssigned) {
             return response()->json(['message' => 'Este revisor no está asignado a este artículo'], 403);
        }
        
        
        $article = \App\Models\Article::find($data['article_id']);
        $rubric = \App\Models\Rubric::where('article_type_id', $article->article_type_id)
            ->withCount('items')
            ->first();

        if (!$rubric) {
            return response()->json(['message' => 'No se encontró rúbrica asociada a este artículo'], 404);
        }

        if (count($data['answers']) < $rubric->items_count) {
            return response()->json([
                'message' => 'La evaluación está incompleta, debes responder todas las preguntas'
            ], 422);
        }
        

        // 4. Guardar/actualizar cada respuesta
        foreach ($data['answers'] as $answer) {
            \App\Models\ArticleEvaluation::updateOrCreate(
                [
                    'article_id' => $data['article_id'],
                    'rubric_item_id' => $answer['rubric_item_id'],
                    'user_id' => $reviewerId, // <-- 5. Usamos el $reviewerId correcto
                ],
                [
                    'rubric_answer_id' => $answer['rubric_answer_id'],
                    'score' => $answer['score'],
                ]
            );
        }

        // 6. Calcular la suma total solo de las respuestas de este revisor
        $totalScore = collect($data['answers'])->sum('score');

        // 7. Actualizar el puntaje SÓLO en las filas de este revisor
        \App\Models\ArticleEvaluation::where('article_id', $data['article_id'])
            ->where('user_id', $reviewerId) 
            ->update(['article_score' => $totalScore]);

        return response()->json([
            'message' => 'Evaluación guardada con éxito',
            'total_score' => $totalScore,
            'reviewer_id' => $reviewerId
        ]);
    }

    public function getArticleEvaluation($articleId, Request $request)
    {
        $reviewerId = $request->query('reviewer_id');
        
        $query = \App\Models\ArticleEvaluation::where('article_id', $articleId)
            ->with([
                'rubricItem:id,question',
                'rubricAnswer:id,text',
                'reviewer:id,name'
            ]);

        if ($reviewerId) {
            // Este filtro es la clave.
            $query->where('user_id', $reviewerId);
        }

        // 1. Ejecutamos la consulta UNA SOLA VEZ.
        $evaluations = $query->get();

        // 2. Calculamos la suma A PARTIR DE LA COLECCIÓN que ya está filtrada.
        $articleScore = $evaluations->sum('score');

        return response()->json([
            'evaluations' => $evaluations->map(function($eval) {
                // Se añaden ?? para evitar errores si una relación es nula
                return [
                    'question' => $eval->rubricItem->question ?? 'Pregunta no disponible',
                    'answer' => $eval->rubricAnswer->text ?? 'Respuesta no disponible',
                    'score' => $eval->score,
                    'reviewer_name' => $eval->reviewer->name ?? 'Desconocido'
                ];
            }),
            'article_score' => $articleScore // Devolvemos el puntaje calculado
        ]);
    }
}