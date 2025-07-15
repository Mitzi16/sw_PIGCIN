<?php

namespace Database\Seeders;

use App\Models\Institution;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\SimpleExcel\SimpleExcelReader;

class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reader = SimpleExcelReader::create(database_path('seeders/files/country-instituciones.xlsx'))
            ->fromSheetName("universidades")
            ->trimHeaderRow();

        $reader->getRows()
            ->each(function (array $rowProperties) {
                if ($rowProperties['id_state'] > 32) {
                    $rowProperties['id_state'] = null;
                }
                $model = new Institution();
                $model->name = $rowProperties['name'];
                $model->status = true;
                $model->state_id = $rowProperties['id_state'];
                $model->country_id = $rowProperties['id_country'];
                $model->save();
            });
    }
}
