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
        /* DB::table('calls')->insert([
            [
                'title' => '5to Congreso Internacional de Tecnología y Ciencia Aplicada (CITCA)',
                'description' => 'El Tecnológico Nacional de México (TecNM) a través del Centro Nacional de Investigación y Desarrollo Tecnológico (CENIDET), invita a toda la comunidad académica, científica, profesional y estudiantil a enviar sus contribuciones al 5to. Congreso Internacional de Tecnología y Ciencias Aplicadas (CITCA), que se llevará a cabo de manera HÍBRIDA (presencial o virtual) en la ciudad de Cuernavaca Morelos, del 26 al 28 de noviembre de 2025. El CITCA es un foro multidisciplinario para la presentación de trabajos de investigación, innovaciones tecnológicas y/o desarrollos tecnológicos de vanguardia a nivel Internacional como nacional con muestra del Humanismo Mexicano para presentar avances significativos en la generación y/o aplicación del conocimiento.',
                'start_date' => '2025-11-26',
                'end_date' => '2025-11-28',
                'url' => 'https://youtu.be/XG3VTPE2lLU',
                'status' => true,
                'institution_id' => 1723,
            ],
        ]); */
    }
}
