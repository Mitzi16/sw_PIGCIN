<?php

namespace App\Exports;

use App\Models\Call;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CallsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Call::with(['institution'])->get();
    }

    /**
     * Define los encabezados de las columnas
     */
    public function headings(): array
    {
        return [
            'Título',
            'Descripción',
            'Fecha de Inicio',
            'Fecha de Fin',
            'Estatus',
            'URL',
            'Institución',
        ];
    }

    /**
     * Mapea los datos que se exportarán en cada fila
     */
    public function map($call): array
    {
        return [
            $call->title,
            $call->description,
            $call->start_date,
            $call->end_date,
            $call->status ? 'Activo' : 'Inactivo',
            $call->url,
            optional($call->institution)->name ?? 'N/A',
        ];
    }
}
