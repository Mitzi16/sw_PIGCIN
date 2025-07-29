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
                'name' => 'Artículo (4-6 hojas máximo)',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Póster',
                'created_at' => date('Y-m-d H:m:s')
            ],
        ]);
    }
}
