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
        'answers' => 'required|array',
        'answers.*.rubric_item_id' => 'required|integer',
        'answers.*.rubric_answer_id' => 'required|integer',
        'answers.*.score' => 'required|integer',
    ]);

    // Obtener el revisor asignado al artículo (de article_reviews)
    $articleReview = \App\Models\ArticleReview::where('article_id', $data['article_id'])
                            ->first();

    if (!$articleReview) {
        return response()->json(['message' => 'No se encontró revisión asignada para este artículo'], 404);
    }

    // El user_id del revisor asignado
    $reviewerId = $articleReview->reviewer_id;

    //  Contar cuántos ítems de rúbrica tiene el artículo
    $rubric = \App\Models\Rubric::where('article_type_id', $articleReview->article->article_type_id)
                ->withCount('items')
                ->first();

    if (!$rubric) {
        return response()->json(['message' => 'No se encontró rúbrica asociada a este artículo'], 404);
    }

    // Validar que se hayan contestado todos los ítems
    if (count($data['answers']) < $rubric->items_count) {
        return response()->json([
            'message' => 'La evaluación está incompleta, debes responder todas las preguntas'
        ], 422);
    }

    // Guardar/actualizar cada respuesta
    foreach ($data['answers'] as $answer) {
        \App\Models\ArticleEvaluation::updateOrCreate(
            [
                'article_id' => $data['article_id'],
                'rubric_item_id' => $answer['rubric_item_id'],
                'user_id' => $reviewerId,
            ],
            [
                'rubric_answer_id' => $answer['rubric_answer_id'],
                'score' => $answer['score'],
            ]
        );
    }

    // Calcular la suma total para el artículo
    $totalScore = \App\Models\ArticleEvaluation::where('article_id', $data['article_id'])
        ->sum('score');

    // Actualizar todas las filas de ese artículo con el nuevo total
    \App\Models\ArticleEvaluation::where('article_id', $data['article_id'])
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
        $query->where('user_id', $reviewerId);
    }

    $evaluations = $query->get();

    // calcular el total directamente con sum()
    $articleScore = $query->sum('score');

    return response()->json([
        'evaluations' => $evaluations->map(function($eval) {
            return [
                'question' => $eval->rubricItem->question,
                'answer' => $eval->rubricAnswer->text,
                'score' => $eval->score,
                'reviewer_name' => $eval->reviewer->name ?? 'Desconocido'
            ];
        }),
        'article_score' => $articleScore
    ]);
    }
}