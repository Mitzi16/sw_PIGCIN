<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RubricsItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rubric_items')->insert([
            [
                'rubric_id' => 1,
                'question' => '¿En qué medida el tema del artículo está alineado con los ejes del congreso y aporta impacto a la comunidad científica?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 1,
                'question' => '¿En qué medida el título y el resumen describen con precisión el contenido y cubren problema, objetivos, metodología, resultados y conclusiones?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 1,
                'question' => '¿En qué medida el problema está bien definido y justificado con antecedentes, y los objetivos son específicos y medibles?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 1,
                'question' => '¿En qué medida la metodología está justificada y descrita (procedimientos, herramientas y criterios) de forma que permita replicar el estudio?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 1,
                'question' => '¿En qué medida los resultados están claros y bien analizados (con figuras/tablas pertinentes) y las conclusiones derivan de ellos?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 1,
                'question' => '¿En qué medida las referencias son actuales y pertinentes, y el formato de citación es correcto y consistente?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 1,
                'question' => '¿Qué tan clara, bien estructurada y correcta es la redacción del manuscrito, evitando tecnicismos innecesarios?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 2,
                'question' => '¿En qué medida el tema del artículo está alineado con los ejes del congreso y aporta impacto a la comunidad científica?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 2,
                'question' => '¿En qué medida el título y el resumen describen con precisión el contenido y cubren problema, objetivos, metodología, resultados y conclusiones?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 2,
                'question' => '¿En qué medida el problema está bien definido y justificado con antecedentes, y los objetivos son específicos y medibles?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 2,
                'question' => '¿En qué medida la metodología está justificada y descrita (procedimientos, herramientas y criterios) de forma que permita replicar el estudio?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 2,
                'question' => '¿En qué medida los resultados están claros y bien analizados (con figuras/tablas pertinentes) y las conclusiones derivan de ellos?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 2,
                'question' => '¿En qué medida las referencias son actuales y pertinentes, y el formato de citación es correcto y consistente?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 2,
                'question' => '¿Qué tan clara, bien estructurada y correcta es la redacción del manuscrito, evitando tecnicismos innecesarios?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 3,
                'question' => '¿¿En qué medida el título es claro y preciso y los autores/afiliaciones están correctamente indicados?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 3,
                'question' => '¿Qué tan clara y completa es la introducción para explicar el problema, su importancia, contexto y justificación?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 3,
                'question' => '¿Qué tan claros, específicos y alineados con la investigación están los objetivos?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 3,
                'question' => '¿Qué tan clara y completa es la descripción de la metodología (procedimiento, herramientas y análisis)?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 3,
                'question' => '¿Qué tan claros, estructurados y bien analizados están los resultados, incluido el uso de gráficos?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 3,
                'question' => '¿En qué medida las conclusiones derivan de los resultados, están justificadas y proponen aplicaciones futuras?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 3,
                'question' => '¿En qué medida las referencias son pertinentes y están citadas correctamente según APA 7?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 4,
                'question' => '¿Qué tan claro/atractivo es el título y qué tan completos y correctos están los autores y sus afiliaciones?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 4,
                'question' => '¿Qué tan clara, relevante y accesible es la introducción para conectar con el lector?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 4,
                'question' => '¿Qué tan claro, organizado y accesible es el contenido principal para el lector?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 4,
                'question' => '¿Qué tan claras, relevantes y bien integradas están las imágenes y gráficos para apoyar el contenido?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 4,
                'question' => '¿Qué tan clara, pertinente e impactante es la conclusión o mensaje final del artículo?',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_id' => 4,
                'question' => '¿Qué tan confiables, actuales y accesibles son las referencias “Para saber más”?',
                'created_at' => date('Y-m-d H:m:s')
            ],

        ]);
    }
}
