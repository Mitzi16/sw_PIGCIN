<?php

namespace App\Exports;

use App\Models\KnowledgeArea;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KnowledgeAreaExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return KnowledgeArea::with('parent')->get();
    }

    /**
     * Encabezados de las columnas
     */
    public function headings(): array
    {
        return [
            'Área de Conocimiento',
            'Descripción',
            'Área Padre',
        ];
    }

    /**
     * Mapea los datos exportados
     */
    public function map($knowledgeArea): array
    {
        return [
            $knowledgeArea->name,
            $knowledgeArea->description,
            optional($knowledgeArea->parent)->name ?? 'N/A',
        ];
    }
}
