<?php

namespace App\Exports;

use App\Models\Call;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CallsExport implements FromCollection, WithEvents, WithColumnWidths
{
    protected int $totalCount;

    public function __construct()
    {
        // Contamos las convocatorias totales
        $this->totalCount = Call::count();
    }

    /**
     * Datos de las convocatorias con sus 7 columnas
     */
    public function collection()
    {
        return Call::with('institution')
            ->latest()
            ->get()
            ->map(fn ($call) => [
                $call->title,
                $call->description,
                $call->start_date ? \Carbon\Carbon::parse($call->start_date)->format('d/m/Y') : 'N/A',
                $call->end_date ? \Carbon\Carbon::parse($call->end_date)->format('d/m/Y') : 'N/A',
                $call->status ? 'Activo' : 'Inactivo',
                $call->url ?? 'Sin URL',
                $call->institution ? $call->institution->name : 'N/A',
            ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40, // Título
            'B' => 60, // Descripción
            'C' => 15, // Fecha Inicio
            'D' => 15, // Fecha Fin
            'E' => 12, // Estatus
            'F' => 35, // URL
            'G' => 35, // Institución
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Última fila de datos (6 filas de encabezado + registros)
                $lastDataRow = 6 + $this->totalCount;

                // Insertamos 6 filas arriba para el bloque de encabezado
                $sheet->insertNewRowBefore(1, 6);

                // Fila 1: marca institucional (Diseño igual al ejemplo)
                $sheet->setCellValue('A1', 'TECNOLÓGICO NACIONAL DE MÉXICO');
                $sheet->setCellValue('B1', 'CENIDET');

                // Fila 3: título del reporte (Adaptado a Convocatorias)
                $sheet->setCellValue('A3', 'REPORTE DE CONVOCATORIAS');

                // Fila 4: metadatos (Fecha y Total)
                $sheet->setCellValue('A4', 'Fecha de exportación: ' . now()->format('d/m/Y'));
                $sheet->setCellValue('F4', 'Total de registros: ' . number_format($this->totalCount));

                // Fila 6: cabeceras de columnas de las Convocatorias
                $sheet->setCellValue('A6', 'Título');
                $sheet->setCellValue('B6', 'Descripción');
                $sheet->setCellValue('C6', 'Fecha de Inicio');
                $sheet->setCellValue('D6', 'Fecha de Fin');
                $sheet->setCellValue('E6', 'Estatus');
                $sheet->setCellValue('F6', 'URL');
                $sheet->setCellValue('G6', 'Institución');

                // ---------- Estilos ----------

                // Marca (A1:B1)
                $sheet->getStyle('A1:B1')->getFont()->setBold(true)->setSize(11);

                // Título (A3)
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);

                // Metadatos (A4, F4)
                $sheet->getStyle('A4')->getFont()->setSize(10);
                $sheet->getStyle('F4')->getFont()->setSize(10)->setBold(true);

                // Cabecera de columnas (fila 6)
                $sheet->getStyle('A6:G6')->applyFromArray([
                    'font' => [
                        'bold'   => true,
                        'color'  => ['argb' => 'FFFFFFFF'],
                        'size'   => 11,
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF15396F'], // Azul oscuro del ejemplo
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Bordes y alineación vertical de los datos
                $sheet->getStyle('A7:G' . $lastDataRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FFD1D5DB'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_TOP,
                    ],
                ]);

                // Hacer que la columna de Descripción (B) haga salto de línea automático
                $sheet->getStyle('B7:B' . $lastDataRow)->getAlignment()->setWrapText(true);

                // Centrar fechas y estatus
                $sheet->getStyle('C7:E' . $lastDataRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Colorear estatus (verde/rojo)
                for ($row = 7; $row <= $lastDataRow; $row++) {
                    $status = $sheet->getCell('E' . $row)->getValue();
                    if ($status === 'Activo') {
                        $sheet->getStyle('E' . $row)->getFont()->getColor()->setARGB('FF059669');
                        $sheet->getStyle('E' . $row)->getFont()->setBold(true);
                    } elseif ($status === 'Inactivo') {
                        $sheet->getStyle('E' . $row)->getFont()->getColor()->setARGB('FFDC2626');
                        $sheet->getStyle('E' . $row)->getFont()->setBold(true);
                    }
                }

                // Congelar cabecera de columnas
                $sheet->freezePane('A7');
            },
        ];
    }
}