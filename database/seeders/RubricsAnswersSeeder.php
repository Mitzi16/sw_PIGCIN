<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RubricsAnswersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rubric_answers')->insert([
            [
                'rubric_item_id' => 1,
                'text' => '5 – Totalmente alineado y de alto impacto.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 1,
                'text' => '4 – Alineado y relevante; el impacto podría ser mayor.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 1,
                'text' => '3 – Tiene relación, pero el aporte no es significativo.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 1,
                'text' => '2 – Se menciona en el contexto del congreso; relación poco clara.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 1,
                'text' => '1 – Poco relevante o sin conexión con el congreso.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 2,
                'text' => '5 – Título claro y representativo; resumen cubre todos los elementos con claridad.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 2,
                'text' => '4 – Título y resumen adecuados; cubren lo esencial, podrían ser más claros.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 2,
                'text' => '3 – Cumplen lo mínimo; faltan detalles o el título podría ser más descriptivo.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 2,
                'text' => '2 – Título poco preciso; resumen omite información clave o está mal estructurado.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 2,
                'text' => '1 – Título confuso; resumen no explica correctamente el contenido.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 3,
                'text' => '5 – Problema claramente definido y sólidamente justificado; objetivos específicos, medibles y coherentes.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 3,
                'text' => '4 – Problema descrito con justificación suficiente pero mejorable; objetivos claros con margen de mejora.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 3,
                'text' => '3 – Problema presentado con soporte limitado; objetivos generales o parcialmente estructurados.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 3,
                'text' => '2 – Problema poco definido o con justificación insuficiente; objetivos vagos o imprecisos.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 3,
                'text' => '1 – No se justifica la relevancia del problema; objetivos no claros ni bien formulados.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 4,
                'text' => '5 – Detallada y bien justificada; procedimientos, herramientas y criterios claros y replicables.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 4,
                'text' => '4 – Adecuada y bien explicada; faltan pocos detalles o justificaciones menores.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 4,
                'text' => '3 – Válida, pero con información incompleta sobre procedimientos o justificación parcial.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 4,
                'text' => '2 – Omisiones importantes o poca claridad en la descripción de procedimientos.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 4,
                'text' => '1 – Deficiente; no permite comprender cómo se realizó el estudio.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 5,
                'text' => '5 – Resultados organizados y analizados; figuras/tablas relevantes y bien explicadas; conclusiones coherentes y con proyección a trabajos futuros.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 5,
                'text' => '4 – Resultados bien presentados; análisis suficiente pero mejorable; conclusiones claras y basadas en los resultados.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 5,
                'text' => '3 – Resultados presentados con poco análisis; algunas figuras/tablas confusas; conclusiones presentes pero poco relacionadas con los resultados.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 5,
                'text' => '2 – Errores de interpretación y análisis insuficiente; conclusiones débiles o poco sustentadas.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 5,
                'text' => '1 – Resultados confusos o no relacionados con el objetivo; conclusiones sin sustento.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 6,
                'text' => '5 – Actualizadas y muy pertinentes; formato correcto y consistente (incluye DOI cuando aplica).',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 6,
                'text' => '4 – Adecuadas; algunas desactualizadas o con pequeños errores de formato.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 6,
                'text' => '3 – Suficientes, pero con varios errores de formato o fuentes poco actualizadas.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 6,
                'text' => '2 – Insuficientes o mal organizadas; errores notables de formato.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 6,
                'text' => '1 – Inadecuadas o con numerosos errores de citación.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 7,
                'text' => '5 – Muy clara y precisa; estructura lógica; sin errores gramaticales.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 7,
                'text' => '4 – Clara y bien organizada; mejoras puntuales; pocos errores que no afectan la comprensión.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 7,
                'text' => '3 – Comprensible en general; algunas partes confusas o con tecnicismos; algunos errores menores.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 7,
                'text' => '2 – Lectura difícil por estructura deficiente o tecnicismos innecesarios; errores frecuentes.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 7,
                'text' => '1 – Redacción confusa o desorganizada, con numerosos errores que afectan la comprensión.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

             [
                'rubric_item_id' => 8,
                'text' => '5 – Totalmente alineado y de alto impacto.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 8,
                'text' => '4 – Alineado y relevante; el impacto podría ser mayor.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 8,
                'text' => '3 – Tiene relación, pero el aporte no es significativo.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 8,
                'text' => '2 – Se menciona en el contexto del congreso; relación poco clara.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 8,
                'text' => '1 – Poco relevante o sin conexión con el congreso.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 9,
                'text' => '5 – Título claro y representativo; resumen cubre todos los elementos con claridad.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 9,
                'text' => '4 – Título y resumen adecuados; cubren lo esencial, podrían ser más claros.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 9,
                'text' => '3 – Cumplen lo mínimo; faltan detalles o el título podría ser más descriptivo.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 9,
                'text' => '2 – Título poco preciso; resumen omite información clave o está mal estructurado.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 9,
                'text' => '1 – Título confuso; resumen no explica correctamente el contenido.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 10,
                'text' => '5 – Problema claramente definido y sólidamente justificado; objetivos específicos, medibles y coherentes.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 10,
                'text' => '4 – Problema descrito con justificación suficiente pero mejorable; objetivos claros con margen de mejora.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 10,
                'text' => '3 – Problema presentado con soporte limitado; objetivos generales o parcialmente estructurados.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 10,
                'text' => '2 – Problema poco definido o con justificación insuficiente; objetivos vagos o imprecisos.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 10,
                'text' => '1 – No se justifica la relevancia del problema; objetivos no claros ni bien formulados.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 11,
                'text' => '5 – Detallada y bien justificada; procedimientos, herramientas y criterios claros y replicables.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 11,
                'text' => '4 – Adecuada y bien explicada; faltan pocos detalles o justificaciones menores.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 11,
                'text' => '3 – Válida, pero con información incompleta sobre procedimientos o justificación parcial.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 11,
                'text' => '2 – Omisiones importantes o poca claridad en la descripción de procedimientos.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 11,
                'text' => '1 – Deficiente; no permite comprender cómo se realizó el estudio.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 12,
                'text' => '5 – Resultados organizados y analizados; figuras/tablas relevantes y bien explicadas; conclusiones coherentes y con proyección a trabajos futuros.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 12,
                'text' => '4 – Resultados bien presentados; análisis suficiente pero mejorable; conclusiones claras y basadas en los resultados.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 12,
                'text' => '3 – Resultados presentados con poco análisis; algunas figuras/tablas confusas; conclusiones presentes pero poco relacionadas con los resultados.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 12,
                'text' => '2 – Errores de interpretación y análisis insuficiente; conclusiones débiles o poco sustentadas.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 12,
                'text' => '1 – Resultados confusos o no relacionados con el objetivo; conclusiones sin sustento.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 13,
                'text' => '5 – Actualizadas y muy pertinentes; formato correcto y consistente (incluye DOI cuando aplica).',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 13,
                'text' => '4 – Adecuadas; algunas desactualizadas o con pequeños errores de formato.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 13,
                'text' => '3 – Suficientes, pero con varios errores de formato o fuentes poco actualizadas.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 13,
                'text' => '2 – Insuficientes o mal organizadas; errores notables de formato.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 13,
                'text' => '1 – Inadecuadas o con numerosos errores de citación.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 14,
                'text' => '5 – Muy clara y precisa; estructura lógica; sin errores gramaticales.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 14,
                'text' => '4 – Clara y bien organizada; mejoras puntuales; pocos errores que no afectan la comprensión.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 14,
                'text' => '3 – Comprensible en general; algunas partes confusas o con tecnicismos; algunos errores menores.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 14,
                'text' => '2 – Lectura difícil por estructura deficiente o tecnicismos innecesarios; errores frecuentes.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 14,
                'text' => '1 – Redacción confusa o desorganizada, con numerosos errores que afectan la comprensión.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 15,
                'text' => '5 – Título claro y preciso; autores con afiliaciones completas y correctas.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 15,
                'text' => '4 – Título adecuado pero mejorable; autores y afiliaciones bien indicados.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 15,
                'text' => '3 – Cumple lo básico; el título podría ser más preciso.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 15,
                'text' => '2 – Título poco claro o afiliaciones incompletas.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 15,
                'text' => '1 – Título confuso o faltan autores/afiliaciones.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 16,
                'text' => '5 – Explica claramente el problema, su importancia y contexto.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 16,
                'text' => '4 – Justifica bien el problema, con detalles mejorables.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 16,
                'text' => '3 – Presenta introducción, pero con falta de claridad o profundidad.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 16,
                'text' => '2 – Explicación poco clara o con información insuficiente.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 16,
                'text' => '1 – Introducción confusa o sin justificación clara o no presenta introducción.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 17,
                'text' => '5 – Claros, específicos y totalmente alineados.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 17,
                'text' => '4 – Bien formulados; podrían ser más precisos.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 17,
                'text' => '3 – Presentes, pero poco detallados.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 17,
                'text' => '2 – Vagos o sin relación clara con la investigación.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 17,
                'text' => '1 – Mal definidos o incompletos o no se presentan.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 18,
                'text' => '5 – Explica procedimiento, herramientas y análisis con claridad.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 18,
                'text' => '4 – Bien presentada, con pocos detalles faltantes.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 18,
                'text' => '3 – Metodología presente, pero con explicación limitada.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 18,
                'text' => '2 – Explicación poco clara o con errores.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 18,
                'text' => '1 – Información insuficiente, metodología confusa o no descrita.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 19,
                'text' => '5 – Datos claros, bien organizados y explicados; uso adecuado de gráficos.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 19,
                'text' => '4 – Resultados bien presentados; análisis mejorable.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 19,
                'text' => '3 – Resultados presentes pero poco estructurados.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 19,
                'text' => '2 – Datos insuficientes o confusos.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 19,
                'text' => '1 – Resultados sin relación con la investigación o no se presentan.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 20,
                'text' => '5 – Derivan claramente de los resultados, bien justificadas y con aplicaciones/implicaciones futuras.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 20,
                'text' => '4 – Presentes y justificadas, aunque podrían desarrollarse más o ampliar aplicaciones.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 20,
                'text' => '3 – Presentes, pero con poca claridad o justificación; no discuten aplicaciones.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 20,
                'text' => '2 – Poco desarrolladas y débilmente conectadas con los resultados.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 20,
                'text' => '1 – Confusas o irrelevantes o no se presentan.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 21,
                'text' => '5 – Formato APA 7 correcto; referencias relevantes y bien citadas.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 21,
                'text' => '4 – Referencias adecuadas con errores menores de formato.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 21,
                'text' => '3 – Referencias suficientes, pero con errores de formato apreciables.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 21,
                'text' => '2 – Pocas referencias o formato mayoritariamente incorrecto.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 21,
                'text' => '1 – Referencias mal organizadas o inadecuadas, o no presenta referencias.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 22,
                'text' => '5 – Título atractivo, claro y conciso; autores y afiliaciones bien indicados.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 22,
                'text' => '4 – Título adecuado, podría ser más atractivo; autores/afiliaciones correctos.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 22,
                'text' => '3 – Cumple lo básico; título poco llamativo o menos preciso.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 22,
                'text' => '2 – Título poco claro o sin impacto; afiliaciones incompletas.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 22,
                'text' => '1 – Título confuso o mal formulado; autores sin afiliaciones claras.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 23,
                'text' => '5 – Explica el tema de forma sencilla, relevante y accesible; usa ejemplos que conectan.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 23,
                'text' => '4 – Clara y accesible; mejorable en estructura o conexión con el lector.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 23,
                'text' => '3 – Entendible, pero con poco impacto o conexión con la audiencia.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 23,
                'text' => '2 – Poco clara o con información insuficiente.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 23,
                'text' => '1 – Confusa o difícil de comprender para el público general.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 24,
                'text' => '5 – Información clara, bien organizada y fácil de entender; uso adecuado de analogías y lenguaje accesible.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 24,
                'text' => '4 – Información comprensible, aunque algunos puntos podrían explicarse mejor.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 24,
                'text' => '3 – Contenido adecuado, pero con detalles técnicos o secciones menos accesibles.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 24,
                'text' => '2 – Dificultades de comprensión o términos técnicos poco explicados.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 24,
                'text' => '1 – Contenido confuso o no adaptado para público no especializado.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 25,
                'text' => '5 – Imágenes claras, relevantes y bien integradas; uso adecuado de iconos y esquemas.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 25,
                'text' => '4 – Uso correcto; algunas podrían mejorar en impacto visual.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 25,
                'text' => '3 – Útiles, pero con oportunidades de mejora en claridad o disposición.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 25,
                'text' => '2 – Poco claras o de baja calidad visual.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 25,
                'text' => '1 – Uso deficiente; gráficos confusos o mal organizados.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 26,
                'text' => '5 – Resumen claro y efectivo; destaca la importancia del tema de manera impactante.',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 26,
                'text' => '4 – Buena conclusión; podría reforzar más la relevancia del mensaje.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 26,
                'text' => '3 – Aceptable, pero algo genérica o poco impactante.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 26,
                'text' => '2 – Débil o con poca relación con el contenido.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 26,
                'text' => '1 – Confusa o sin mensaje claro.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

            [
                'rubric_item_id' => 27,
                'text' => '5 – Lista de fuentes confiables, actuales y de fácil acceso (enlaces/indicaciones claras).',
                'score' => '5',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 27,
                'text' => '4 – Referencias adecuadas; podrían incluir más opciones de consulta.',
                'score' => '4',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 27,
                'text' => '3 – Referencias presentes con errores menores de formato o accesibilidad.',
                'score' => '3',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 27,
                'text' => '2 – Pocas referencias o sin indicar cómo acceder a más información.',
                'score' => '2',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'rubric_item_id' => 27,
                'text' => '1 – Referencias mal organizadas o poco útiles.',
                'score' => '1',
                'created_at' => date('Y-m-d H:m:s')
            ],

        ]);
    }
}
