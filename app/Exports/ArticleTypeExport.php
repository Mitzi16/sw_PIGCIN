<?php

namespace App\Exports;

use App\Models\ArticleType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ArticleTypeExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return ArticleType::all();
    }

    /**
     * Encabezados de las columnas
     */
    public function headings(): array
    {
        return [
            'Tipo de Artículo',
        ];
    }

    /**
     * Mapea cada fila exportada
     */
    public function map($articleType): array
    {
        return [
            $articleType->name,
        ];
    }
}
