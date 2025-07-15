<?php

namespace Database\Seeders;

use App\Models\KnowledgeArea;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\SimpleExcel\SimpleExcelReader;

class KnowledgeAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reader = SimpleExcelReader::create(database_path('seeders/files/areas.xlsx'))
            ->fromSheetName("areas") // Primera hoja (index 0)
            ->trimHeaderRow();

        $reader->getRows()
            ->each(function (array $rowProperties) {
                if ($rowProperties['parent_id'] == '') {
                    $rowProperties['parent_id'] = null;
                }
                $model = new KnowledgeArea();
                $model->name = $rowProperties['name_area'];
                $model->description = $rowProperties['name_area'];

                $model->parent_id = $rowProperties['parent_id'];
                $model->save();
            });
    }
}
