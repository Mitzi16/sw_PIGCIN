<?php

namespace App\Exports;

use App\Models\ArticleType;
use Maatwebsite\Excel\Concerns\FromCollection;

class ArticleTypeExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return ArticleType::all();
    }
}
