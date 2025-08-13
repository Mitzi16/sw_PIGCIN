<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RubricsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rubrics')->insert([
            [
                'article_type_id' => 1,
                'title' => 'Rubrica para Artículo Congreso (4-6 hojas máximo)',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'article_type_id' => 2,
                'title' => 'Rubrica para Artículo Revista',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'article_type_id' => 3,
                'title' => 'Rubrica para Póster Científico',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'article_type_id' => 4,
                'title' => 'Rubrica para Póster Difusión',
                'created_at' => date('Y-m-d H:m:s')
            ]
        ]);
    }
}
