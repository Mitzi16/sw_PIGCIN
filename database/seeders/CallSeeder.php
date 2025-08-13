<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('calls')->insert([
            [
                'title' => '5to Congreso Internacional de Tecnología y Ciencia Aplicada (CITCA)',
                'description' => 'El Tecnológico Nacional de México (TecNM) a través del Centro Nacional de Investigación y Desarrollo Tecnológico (CENIDET), invita a toda la comunidad académica, científica, profesional y estudiantil a enviar sus contribuciones al 5to. Congreso Internacional de Tecnología y Ciencias Aplicadas (CITCA), que se llevará a cabo de manera HÍBRIDA (presencial o virtual) en la ciudad de Cuernavaca Morelos, del 26 al 28 de noviembre de 2025. El CITCA es un foro multidisciplinario para la presentación de trabajos de investigación, innovaciones tecnológicas y/o desarrollos tecnológicos de vanguardia a nivel Internacional como nacional con muestra del Humanismo Mexicano para presentar avances significativos en la generación y/o aplicación del conocimiento. Las contribuciones deberán desarrollarse en concordancia con los siguientes programas:
- Ingeniería Electrónica
- Ciencias Computacionales
- Ingeniería Mecánica
- Ciencias de la Ingeniería
- Ingeniería Mecatrónica
- Otras Ingenierías.

Cubriendo las siguientes áreas prioritarias nacionales:
- Cambio Climático
- Economía Circular
- Desarrollo Sostenible
- Educación y Cultura
- Electromovilidad
- Inteligencia Artificial
- Tecnología 5.0 y Ciencias de Datos	- Sistemas Socioecológicos
- Vivienda
- Sistemas agroalimentarios sustentables
- Salud
- Gestión del Agua
- Eficiencia Energética/Energías Renovables
- Agentes Tóxicos y Procesos Contaminantes',
                'start_date' => '2025-07-21',
                'end_date' => '2025-09-26',
                'url' => 'https://youtube.com/shorts/nzoluqR7JLU?feature=share',
                'status' => true,
                'institution_id' => 1723,
            ],
        ]);
    }
}
