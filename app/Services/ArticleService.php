<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleType;
use Illuminate\Support\Facades\DB;

class ArticleService
{
    protected $articles;

    public function __construct(Article $articles)
    {
        $this->articles = $articles;
    }

    public function getUniqueCode(): string
    {
        $congreso = 'CITCA';
        $ano = now()->format('Y');
        $mes = now()->format('m');
        $date = now()->format('d-m-Y');
        $edicion_citca = $mes <= 6 ? '01' : '02';

        $nextId = str_pad((Article::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT);
        $baseCode = "{$congreso}{$ano}-{$edicion_citca}-{$nextId}-{$date}";

        $suffix = 0;
        $originalBase = $baseCode;
        while (Article::where('article_folio', $baseCode)->exists()) {
            $suffix++;
            $baseCode = "{$originalBase}-{$suffix}";
        }

        return $baseCode;
    }

    public function createWithRubrics(array $data, ?array $rubrics = null): ArticleType
    {
        return DB::transaction(function () use ($data, $rubrics) {
            $articleType = ArticleType::create($data);

            if ($rubrics) {
                $this->createRubrics($articleType, $rubrics);
            }

            return $articleType->load(['rubrics.items.answers']);
        });
    }

    public function updateWithRubrics(ArticleType $articleType, array $data, ?array $rubrics = null): ArticleType
    {
        return DB::transaction(function () use ($articleType, $data, $rubrics) {
            $articleType->update($data);

            if ($rubrics !== null) {
                $rubricData = collect($rubrics)->first();

                if ($rubricData) {
                    $rubric = $articleType->rubrics()->first();
                    if ($rubric) {
                        $rubric->update([
                            'title' => $rubricData['title'],
                        ]);
                        $rubric->items()->delete();
                        foreach ($rubricData['items'] as $itemData) {
                            $answers = collect($itemData['answers'] ?? [])->take(5);
                            unset($itemData['answers']);

                            $item = $rubric->items()->create($itemData);

                            foreach ($answers as $answerData) {
                                $item->answers()->create($answerData);
                            }
                        }
                    } else {
                        $this->createRubrics($articleType, $rubrics);
                    }
                }
            }

            return $articleType->load(['rubrics.items.answers']);
        });
    }


    private function createRubrics(ArticleType $articleType, array $rubrics): void
    {
        // Solo tomamos la primera rúbrica
        $rubricData = collect($rubrics)->first();

        if (!$rubricData) return;

        $items = collect($rubricData['items'] ?? [])->take(10); // Máx 10 preguntas
        unset($rubricData['items']);

        $rubric = $articleType->rubrics()->create($rubricData);

        foreach ($items as $itemData) {
            $answers = collect($itemData['answers'] ?? [])->take(5); // Máx 4 respuestas
            unset($itemData['answers']);

            $item = $rubric->items()->create($itemData);

            foreach ($answers as $answerData) {
                $item->answers()->create($answerData);
            }
        }
    }
}
