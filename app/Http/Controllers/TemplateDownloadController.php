<?php 
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;

class TemplateDownloadController extends Controller
{
    public function download($filename)
    {
        $path = public_path("templates/{$filename}");

        if (!file_exists($path)) {
            abort(404, "Archivo no encontrado");
        }

        return response()->download($path, $filename);
    }
}