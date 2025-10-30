<?php

namespace Database\Seeders;

use App\Models\ArticleStatus;
use App\Models\Criterion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        ArticleStatus::updateOrCreate(['id' => 1], [
            'name' => 'Enviado para revisión',
            'description' => 'El artículo fue enviado al editor en espera de asignar revisores.',
            'class' => 'border border-yellow-400 dark:bg-yellow-800 dark:text-yellow-200 bg-yellow-50 text-yellow-500 rounded-xl px-2 dark:opacity-95 text-center w-max',
            'is_evaluation' => false,
            'can_edit' => false,
        ]);

        ArticleStatus::updateOrCreate(['id' => 2], [
            'name' => 'En revisión',
            'description' => 'Se asignaron revisores al artículo.',
            'class' => 'border border-blue-500 dark:bg-blue-200 dark:text-blue-800 bg-blue-50 text-blue-500 rounded-xl px-2 dark:opacity-95 text-center w-max',
            'is_evaluation' => false,
            'can_edit' => false,
        ]);

        ArticleStatus::updateOrCreate(['id' => 3], [
            'name' => 'Aceptado con observaciones',
            'description' => 'Aceptado con observaciones, es necesario atenderlas para poder ser aceptado.',
            'class' => 'border border-green-600 dark:bg-green-800 dark:text-green-200 bg-green-100 text-green-500 rounded-xl px-2 dark:opacity-95 text-center w-max',
            'is_evaluation' => true,
            'can_edit' => true,
        ]);

        ArticleStatus::updateOrCreate(['id' => 4], [
            'name' => 'Aceptado',
            'description' => 'Proceso concluido, artículo aceptado.',
            'class' => 'border border-green-600 dark:bg-green-800 dark:text-green-200 bg-green-100 text-green-500 rounded-xl px-2 dark:opacity-95 text-center w-max',
            'is_evaluation' => true,
            'can_edit' => false,
        ]);

        ArticleStatus::updateOrCreate(['id' => 5], [
            'name' => 'Rechazado',
            'description' => 'Proceso concluido, artículo rechazado.',
            'class' => 'border border-red-600 dark:bg-red-800 dark:text-red-200 bg-red-100 text-red-500 rounded-xl px-2 dark:opacity-95 opacity-85 text-center w-max',
            'is_evaluation' => true,
            'can_edit' => false,
        ]);

        ArticleStatus::updateOrCreate(['id' => 6], [
            'name' => 'Artículo Corregido',
            'description' => 'Fue aceptado con modificaciones y el postulante acaba de reenviar su artículo corregido.',
            'class' => 'border border-green-600 dark:bg-green-800 dark:text-green-200 bg-green-100 text-green-500 rounded-xl px-2 dark:opacity-95 text-center w-max',
            'is_evaluation' => true,
            'can_edit' => false,
        ]);
        
        Criterion::updateOrCreate(['name' => 'Convocatoria'], [
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam orci ante, dignissim non viverra vel, feugiat in tellus. Sed non mauris at velit tincidunt sagittis. Nullam a ullamcorper nunc.',
        ]);

        Criterion::updateOrCreate(['name' => 'Contenido'], [
            'description' => 'Praesent tincidunt consectetur lacus, non laoreet est fringilla non. Morbi dictum leo at purus laoreet, in tincidunt tortor lobortis.',
        ]);

        Criterion::updateOrCreate(['name' => 'Metodología'], [
            'description' => 'Nunc consequat justo et lacus faucibus, vitae aliquet magna tempor. Nunc ornare vitae dui sit amet viverra. ',
        ]);

        Criterion::updateOrCreate(['name' => 'Desarrollo'], [
            'description' => 'Cras a eleifend elit, sit amet pharetra enim. Lorem ipsum dolor sit amet, consectetur adipiscing elit. ',
        ]);

        Criterion::updateOrCreate(['name' => 'Referencias'], [
            'description' => ' Etiam eu enim nibh. Ut sit amet commodo purus. Nunc ultricies fringilla quam, fermentum laoreet eros molestie non.',
        ]);
    }
}