<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; 
use App\Models\Article;
use App\Http\Controllers\Traits\SignatureTrait; 

class VerificationController extends Controller
{
    use SignatureTrait; 

    public function verifyConstancia(string $folio)
    {
        // 1. Buscamos el artículo por su folio
        $article = Article::where('article_folio', $folio)
                            ->whereIn('article_status_id', [3, 4]) 
                            ->with('authors', 'articleStatus', 'postulant.roles') 
                            ->first();

        // 2. Preparamos los datos
        $data = null;
        $signedText = null;
        $selloDigital = null; 
        $participant_name = null;
        $participant_role = null;

        if ($article) {
            $data = $this->prepareVerificationData($article); // Lógica refactorizada
            $signedText = $data['signedText'];
            $selloDigital = $data['selloDigital']; 
            $participant_name = $data['participant_name'];
            $participant_role = $data['participant_role'];
        } 

        // 3. Devuelve la VISTA ANTIGUA
        return view('verification', [
            'data' => $data,
            'folio' => $folio,
            'signedText' => $signedText,
            'selloDigital' => $selloDigital, 
            'participant_name' => $participant_name,
            'participant_role' => $participant_role, 
        ]);
    }

    public function verifyReconocimiento(string $id)
    {
        $article = Article::where('id', $id) 
                            ->whereIn('article_status_id', [3, 4]) 
                            ->with('authors', 'articleStatus', 'postulant.roles') 
                            ->first();
        
        // 2. Preparamos los datos
        $data = null;
        $signedText = null;
        $selloDigital = null; 
        $participant_name = null;
        $participant_role = null;
        $folio_para_mostrar = $id; // Folio por defecto si no se encuentra

        if ($article) {
            $data = $this->prepareVerificationData($article); 
            $signedText = $data['signedText'];
            // Extraer la nueva variable del array $data
            $selloDigital = $data['selloDigital']; 
            $participant_name = $data['participant_name'];
            $participant_role = $data['participant_role'];
            $folio_para_mostrar = $data['folio'];
        }

        // 3. Devuelve la VISTA NUEVA
        return view('Reconocimientoverification', [
            'data' => $data,
            'folio' => $folio_para_mostrar, // Pasamos el folio que encontramos (o el ID si falló)
            'signedText' => $signedText,
            'selloDigital' => $selloDigital, 
            'participant_name' => $participant_name,
            'participant_role' => $participant_role, 
        ]);
    }

    private function prepareVerificationData(Article $article)
    {
        // Datos Básicos 
        $authors_list = $article->authors
            ->map(fn ($a) => trim($a->prefix . ' ' . $a->name . ' ' . $a->surname))
            ->implode(', ');
        
        $data = [
            'folio' => $article->article_folio,
            'title' => $article->title,
            'status' => $article->articleStatus->name ?? 'Aceptado',
            'authors' => $authors_list,
        ];
        

        // Sello Digital 
       try {
        // 1. Generamos la firma digital completa (como antes)
        $fullSignature = $this->generateSignature($article);
        
        // 2. Creamos el hash SHA-256 (un identificador único de 64 caracteres)
        $hash = hash('sha256', $fullSignature);

        // 3. Formateamos el hash para que coincida con tu estilo (16-16-16-16)
        $signedText = strtoupper(implode('-', str_split($hash, 16)));
        // Esto crea algo como: 46229C8C880AA306-51951373C6669B24-584E485B3D481350-4BB385402ED30922

    } catch (\Exception $e) {
        Log::error("Error al generar firma para VERIFICACIÓN (art. {$article->id}): " . $e->getMessage());
        $signedText = "Error al generar sello";
        // Si hay error, asegúrate de que $fullSignature esté definida para evitar un error diferente
        $fullSignature = "Error al generar firma";
    }

    // Datos del Postulante
    $participant_name = null;
    $participant_role = null;
    if ($article->postulant) {
        $participant_name = $article->postulant->name;
        if ($article->postulant->roles->isNotEmpty()) {
            $participant_role = $article->postulant->roles->first()->name;
        }
    }

    // Asignar valores por defecto y devolver todo
    // 4. Asignamos la firma completa y el hash al array $data
    $data['selloDigital'] = $fullSignature; // Firma completa (opcional) <-- ¡Mantenida aquí!
    $data['signedText'] = $signedText;
    $data['participant_name'] = mb_strtoupper($participant_name ?? 'No especificado', 'UTF-8');
    $data['participant_role'] = mb_strtoupper($participant_role ?? 'No especificado', 'UTF-8');

    return $data;
    }
}