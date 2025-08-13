<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('article_types')->insert([
            [
                'name' => 'Artículo Congreso (4-6 hojas máximo)',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Artículo Revista',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Póster Científico',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Póster Difusión',
                'created_at' => date('Y-m-d H:m:s')
            ],
        ]);
    }
}
