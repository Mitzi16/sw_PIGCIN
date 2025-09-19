<?php

namespace App\Exports;

use App\Models\Criterion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CriterionExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Criterion::all();
    }

    /**
     * Encabezados de las columnas
     */
    public function headings(): array
    {
        return [
            'Criterio',
            'Descripción',
        ];
    }

    /**
     * Mapea los datos exportados
     */
    public function map($criterion): array
    {
        return [
            $criterion->name,
            $criterion->description,
        ];
    }
}
