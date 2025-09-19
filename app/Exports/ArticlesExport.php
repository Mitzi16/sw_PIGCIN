<?php

namespace App\Exports;

use App\Models\Article;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ArticlesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Article::with([
            'postulant',
            'editor',
            'knowledgeArea',
            'articleStatus',
            'call',
            'paymentVoucher',
            'articleType'
        ])->get();
    }

    /**
     * Define los encabezados de las columnas
     */
    public function headings(): array
    {
        return [
            'Folio',
            'Título',
            'Resumen',
            'Palabras clave',
            'Comentario',
            'Postulante',
            'Editor',
            'Área de Conocimiento',
            'Estatus',
            'Convocatoria',
            'Comprobante de Pago',
            'Tipo de Artículo',
        ];
    }

    /**
     * Mapea los datos que se exportarán en cada fila
     */
    public function map($article): array
    {
        return [
            $article->article_folio,
            $article->title,
            $article->abstract,
            $article->key_works,
            $article->comment,
            optional($article->postulant)->name ?? 'N/A',
            optional($article->editor)->name ?? 'N/A',
            optional($article->knowledgeArea)->name ?? 'N/A',
            optional($article->articleStatus)->name ?? 'N/A',
            optional($article->call)->title ?? 'N/A',
            optional($article->paymentVoucher)->reference ?? 'N/A',
            optional($article->articleType)->name ?? 'N/A',
        ];
    }
}
