<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Http\Request;
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

        // Obtener el próximo ID basado en el máximo ID actual
        $nextId = str_pad((Article::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT);

        // Generar el código base
        $baseCode = "{$congreso}{$ano}-{$edicion_citca}-{$nextId}-{$date}";

        // Verificar unicidad
        $suffix = 0;
        $originalBase = $baseCode;

        while (Article::where('article_folio', $baseCode)->exists()) {
            $suffix++;
            $baseCode = "{$originalBase}-{$suffix}";
        }

        return $baseCode;
    }
}
