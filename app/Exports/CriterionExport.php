<?php

namespace App\Exports;

use App\Models\Criterion;
use Maatwebsite\Excel\Concerns\FromCollection;

class CriterionExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Criterion::all();
    }
}
