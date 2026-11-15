<?php

namespace App\Exports;

use App\Models\Institution;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InstitutionExport implements FromQuery, WithMapping, WithEvents, WithColumnWidths, WithDrawings
{
    private $row = 0;

    /**
     * Usamos Query para que sea más rápido con miles de registros.
     */
    public function query()
    {
        return Institution::with(['country', 'state']);
    }

    /**
     * Mapeamos los datos. Añadimos el contador.
     */
    public function map($institution): array
    {
        $this->row++;
        return [
            $this->row, // #
            $institution->name, // Nombre
            optional($institution->state)->name ?? 'N/A', // Estado
            optional($institution->country)->name ?? 'N/A', // País
            $institution->status ? 'Activa' : 'Inactiva', // Estatus
        ];
    }

    /**
     * Ancho de las columnas
     */
    public function columnWidths(): array
    {
        return [
            'A' => 8,   // #
            'B' => 60,  // Nombre
            'C' => 30,  // Estado
            'D' => 25,  // País
            'E' => 15,  // Estatus
        ];
    }

    /**
     * Agregar los logos (Ajusta las rutas a tus imágenes reales)
     */
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo TecNM');
        $drawing->setDescription('TecNM');
        // IMPORTANTE: Cambia esta ruta por la ruta real de tu logo en el servidor
        // $drawing->setPath(public_path('img/logo-tecnm.png')); 
        $drawing->setHeight(60);
        $drawing->setCoordinates('A1');

        return [$drawing];
    }

    /**
     * Aquí ocurre la magia del diseño (Colores, fuentes, celdas combinadas)
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Insertamos 3 filas en la parte superior para nuestra cabecera personalizada
                $sheet->insertNewRowBefore(1, 3);

                // --- 1. CABECERA PRINCIPAL (Fila 1) ---
                $sheet->mergeCells('A1:B1'); // Espacio para Logo y texto izquierdo
                $sheet->setCellValue('A1', 'TECNOLÓGICO NACIONAL DE MÉXICO');
                
                $sheet->mergeCells('C1:D1');
                $sheet->setCellValue('C1', "CENIDET\nCentro Nacional de Investigación y Desarrollo Tecnológico");
                
                $sheet->mergeCells('E1:E1');
                $sheet->setCellValue('E1', "Reporte de Información\nSistema Institucional");

                // Estilos de la Fila 1
                $sheet->getStyle('A1:E1')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '0F2946'], // Azul oscuro
                    ],
                    'font' => [
                        'color' => ['rgb' => 'FFFFFF'],
                        'bold' => true,
                        'size' => 12,
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'wrapText' => true,
                    ]
                ]);
                $sheet->getRowDimension(1)->setRowHeight(60); // Altura para el logo

                // --- 2. SUB-CABECERA (Fila 2) ---
                $sheet->mergeCells('A2:E2');
                $sheet->setCellValue('A2', '📄 Fecha de exportación: ' . now()->format('d/m/Y') . '   |   Total de registros: ' . ($highestRow - 1));
                
                $sheet->getStyle('A2:E2')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F0F4F8'], // Azul muy claro
                    ],
                    'font' => [
                        'color' => ['rgb' => '333333'],
                        'bold' => true,
                        'size' => 11,
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'indent' => 1,
                    ]
                ]);
                $sheet->getRowDimension(2)->setRowHeight(30);

                // --- 3. CABECERA DE LA TABLA (Fila 3) ---
                $headers = ['', '#', '👥 Nombre', '📍 Estado', '🌎 País', '📊 Estatus']; // Añadimos una columna vacía A para alinear el diseño
                // Ojo: Como combinamos A1:B1, la columna A y B son compartidas. Para la tabla, haremos que A sea el índice y B el nombre.
                
                $sheet->setCellValue('A3', '#');
                $sheet->setCellValue('B3', '👥 Nombre');
                $sheet->setCellValue('C3', '📍 Estado');
                $sheet->setCellValue('D3', '🌎 País');
                $sheet->setCellValue('E3', '📊 Estatus');

                $sheet->getStyle('A3:E3')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '0F2946'], // Azul oscuro
                    ],
                    'font' => [
                        'color' => ['rgb' => 'FFFFFF'],
                        'bold' => true,
                        'size' => 11,
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '0F2946'],
                        ],
                    ]
                ]);
                $sheet->getRowDimension(3)->setRowHeight(35);

                // --- 4. ESTILOS DE LAS FILAS DE DATOS (Fila 4 en adelante) ---
                $startRow = 4;
                $endRow = $highestRow + 2; // +2 porque insertamos 3 filas arriba

                for ($row = $startRow; $row <= $endRow; $row++) {
                    // Alternar colores de fondo (Cebra)
                    $bgColor = ($row % 2 == 0) ? 'FFFFFF' : 'F9FAFB'; 
                    
                    $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $bgColor],
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'E5E7EB'], // Gris claro
                            ],
                        ],
                    ]);

                    // Centrar el número (Columna A) y el estatus (Columna E)
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E{$row}")->getFont()->setBold(true);

                    // Colorear la celda de Estatus (Verde o Rojo)
                    $status = $sheet->getCell("E{$row}")->getValue();
                    if ($status === 'Activa') {
                        $sheet->getStyle("E{$row}")->applyFromArray([
                            'font' => ['color' => ['rgb' => '166534']],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'DCFCE7'], // Verde claro
                            ],
                        ]);
                    } else if ($status === 'Inactiva') {
                        $sheet->getStyle("E{$row}")->applyFromArray([
                            'font' => ['color' => ['rgb' => '991B1B']],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FEE2E2'], // Rojo claro
                            ],
                        ]);
                    }
                }

                // --- 5. PIE DE PÁGINA (Última fila) ---
                $footerRow = $endRow + 2;
                $sheet->mergeCells("A{$footerRow}:E{$footerRow}");
                $sheet->setCellValue("A{$footerRow}", '"Innovación y Tecnología para un México Mejor" | TecNM | CENIDET');
                
                $sheet->getStyle("A{$footerRow}:E{$footerRow}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '0F2946'], // Azul oscuro
                    ],
                    'font' => [
                        'color' => ['rgb' => 'FFFFFF'],
                        'italic' => true,
                        'bold' => true,
                        'size' => 10,
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ]
                ]);
                $sheet->getRowDimension($footerRow)->setRowHeight(40);
            },
        ];
    }
}