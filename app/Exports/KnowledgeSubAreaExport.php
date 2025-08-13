<?php

namespace App\Exports;

use App\Models\KnowledgeArea;
use Maatwebsite\Excel\Concerns\FromCollection;

class KnowledgeSubAreaExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return KnowledgeArea::whereNotNull('parent_id')
            ->with('parent')
            ->get()
            ->map(function ($item) {
                return [
                    'ID' => $item->id,
                    'Nombre' => $item->name,
                    'Descripción' => $item->description,
                    'Área Padre' => $item->parent?->name ?? '—',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Descripción',
            'Área Padre'
        ];
    }
}
