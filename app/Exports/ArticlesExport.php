<?php

namespace App\Exports;

use App\Models\Article;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ArticlesExport implements FromQuery, WithHeadings, WithMapping
{
    protected array $typeIds;

    public function __construct(array $typeIds = [])
    {
        $this->typeIds = $typeIds;
    }

    public function query()
    {
        $query = Article::query()->with([
            'postulant',
            'editor',
            'knowledgeArea.parent',
            'knowledgeArea',
            'articleStatus',
            'call',
            'paymentVoucher',
            'articleType'
        ]);

        if (!empty($this->typeIds)) {
            $query->whereIn('article_type_id', $this->typeIds);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Folio',
            'Título',
            'Tipo de Artículo',
            'Programa de Estudio',
            'Área de Prioritaria',
            'Palabras clave',
            'Comentario',
            'Postulante',
            'Editor',
            'Estatus'
        ];
    }

    public function map($article): array
    {
        $program = null;
        if ($article->knowledgeArea) {
            $program = $article->knowledgeArea->parent
                ? $article->knowledgeArea->parent->name
                : $article->knowledgeArea->name;
        }

        return [
            $article->article_folio,
            $article->title,
            optional($article->articleType)->name ?? 'N/A',
            $program ?? 'N/A',
            optional($article->knowledgeArea)->name ?? 'N/A',
            is_array($article->key_works) ? implode(', ', $article->key_works) : $article->key_works,
            $article->comment,
            optional($article->postulant)->name ?? 'N/A',
            optional($article->editor)->name ?? 'N/A',
            optional($article->articleStatus)->name ?? 'N/A',
        ];
    }
}
