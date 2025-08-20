<?php

namespace App\Exports;

use App\Models\KnowledgeArea;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KnowledgeSubAreaExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Obtiene solo las subáreas (las que tienen área padre)
     */
    public function collection()
    {
        return KnowledgeArea::whereNotNull('parent_id')
            ->with('parent')
            ->get();
    }

    /**
     * Encabezados de columnas
     */
    public function headings(): array
    {
        return [
            
            'Nombre',
            'Descripción',
            'Área Padre',
        ];
    }

    /**
     * Mapea cada fila exportada
     */
    public function map($subarea): array
    {
        return [
            $subarea->name,
            $subarea->description,
            optional($subarea->parent)->name ?? '—',
        ];
    }
}
