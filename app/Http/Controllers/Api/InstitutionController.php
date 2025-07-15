<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use Illuminate\Http\Request;

class InstitutionController extends Controller
{
    public function filter(String $search){
        $institutions = Institution::whereLike('name', '%' . $search . '%')->limit(50)->get();
        return response($institutions, 200);
    }
}
