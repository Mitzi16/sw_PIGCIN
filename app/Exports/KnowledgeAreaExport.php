<?php

namespace App\Exports;

use App\Models\KnowledgeArea;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\KnowledgeSubArea;


class KnowledgeAreaExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return KnowledgeArea::all();
    }
}
