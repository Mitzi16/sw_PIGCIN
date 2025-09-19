<?php

namespace App\Exports;

use App\Models\Institution;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InstitutionExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Institution::with(['country', 'state'])->get();
    }

    /**
     * Define los encabezados de las columnas
     */
    public function headings(): array
    {
        return [
            'Nombre de la Institución',
            'Estado',
            'País',
            'Estatus',
        ];
    }

    /**
     * Mapea los datos que se exportarán en cada fila
     */
    public function map($institution): array
    {
        return [
            $institution->name,
            optional($institution->state)->name ?? 'N/A',
            optional($institution->country)->name ?? 'N/A',
            $institution->status ? 'Activa' : 'Inactiva',
        ];
    }
}
