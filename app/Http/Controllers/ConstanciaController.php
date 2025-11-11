<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage; 

class ConstanciaController extends Controller
{
    /**
     * Muestra la página de gestión de constancias.
     */
    public function constancia(Request $request): Response
    {
        $user = User::findOrFail(Auth::id());

        $query = Article::query()
            ->with('correspondingAuthor')
            ->whereIn('article_status_id', [3, 4, 6]); // Aceptados

        if (! $user->canPermission('article.all')) {
            $query->where('postulant_id', $user->id);
        }

        $query->when($request->input('search'), function ($query, $search) {
            $query->where('title', 'like', "%{$search}%");
        });

        $articles = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString() 
            ->through(fn (Article $article) => [
                'id' => $article->id,
                'article_folio' => $article->article_folio,
                'title' => $article->title,
                'status_id' => $article->article_status_id,
                'author_name' => $article->correspondingAuthor
                ? trim($article->correspondingAuthor->prefix . ' ' . $article->correspondingAuthor->name . ' ' . $article->correspondingAuthor->surname)
                : 'No especificado',
                // Preparamos la URL para el nuevo endpoint
                'letter_data_url' => route('article.acceptanceLetterData', $article->id),
            ]);

        return Inertia::render('Article/Constancias', [
            'title' => 'Gestión de Constancias',
            'articles' => $articles,
            'filters' => $request->only('search'), 
        ]);
    }

    public function getAcceptanceLetterData(Article $article)
    {
        $user = Auth::user();

        // --- Validación de permisos (Existente) ---
        if (!in_array($article->article_status_id, [3, 4, 6])) {
            abort(403, 'El artículo no tiene un estado de aceptación válido.');
        }
        if (!$user->canPermission('article.all') && $user->id !== $article->postulant_id) {
            abort(403, 'No tiene permiso para ver este documento.');
        }
        // $this->authorize('successPayment', $article);
        // --- Fin de Validación ---


        //  Cargar 'postulant.roles' para obtener nombre y rol
        $article->load('postulant.roles', 'authors.institution', 'articleType', 'articleStatus');

        // --- Preparar listas (Existente) ---
        $unique_institutions_list = $article->authors
            ->map(fn ($author) => $author->institution)
            ->filter()
            ->unique('id')
            ->pluck('name')
            ->filter()
            ->implode(', ');

        $authors_list = $article->authors
            ->map(fn ($a) => trim($a->name . ' ' . $a->surname))
            ->implode(' | ');

        // Obtener el sello digital 
        $signedText = "Error al generar sello"; // Valor por defecto
        try {
            $signedText = $this->generateSignature($article);
        } catch (\Exception $e) {
            Log::error("Error al generar firma para carta (art. {$article->id}): " . $e->getMessage());
        }

        
        // ¡CAMBIO 2: Preparar los nuevos datos para el QR!
        
        // Obtener el nombre del participante (Corregido según tu modelo User)
        $participant_name = 'No especificado';
        if ($article->postulant) {
            $participant_name = $article->postulant->name; // Usamos el campo 'name'
        }
        $participant_name = mb_strtoupper($participant_name, 'UTF-8');

        // Obtener el Rol de Participación (Corregido según tu modelo User)
        $participant_role = 'No especificado'; // Valor por defecto
        if ($article->postulant && $article->postulant->roles->isNotEmpty()) {
            // Obtenemos el nombre del primer rol que tenga el usuario
            $participant_role = $article->postulant->roles->first()->name;
        }
        $participant_role = mb_strtoupper($participant_role, 'UTF-8');


        // ¡CAMBIO 3: Construir el objeto de datos con los nuevos campos!
        $data = [
            'id' => $article->id,
            'article_folio' => $article->article_folio,
            'article_title' => $article->title,
            'authors_list' => $authors_list,
            'institutions_list' => $unique_institutions_list,
            'article_type_name' => $article->articleType->name ?? 'No especificado',
            'article_status_name' => $article->articleStatus->name ?? 'Aceptada',
            'signedText' => $signedText, // Se usa para el 'Identificador de Constancia' en el QR
            
            // --- Campos nuevos añadidos ---
            'participant_name' => $participant_name,
            'participant_role' => $participant_role,

            'verification_url' => route('constancia.verify', $article->article_folio),
        ];

        // Devolver como JSON
        return response()->json($data);
    }

    public function generateCertificate(Article $article)
    {
        Log::warning("Se llamó al método obsoleto generateCertificate para el artículo {$article->id}.");
        
        // Redirigir o mostrar un error, ya que este flujo no debe usarse.
        return redirect()->back()->with('error', 'Este método de generación de PDF está obsoleto. Utilice la nueva función de script.');
    }

    public function signPdf(Article $article)
    {
        try {
            // $this->authorize('successPayment', $article); // Asegúrate que la autorización es correcta

            // Llama a la lógica refactorizada
            $str_b64 = $this->generateSignature($article); // Corregido $str_b664

            return response()->json(['signedText' => $str_b64]); // Corregido $str_b664
        } catch (\Exception $e) {
            Log::error("Error en signPdf API para {$article->id}: " . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor: ' . $e->getMessage()], 500); 
        }
    }

    //  FUNCIONES HELPER PRIVADAS 

    private function getSignedTextFromServer(Article $article): string
    {
        try {
            return $this->generateSignature($article);
        } catch (\Exception $e) {
            Log::error("Error al obtener sello para PDF {$article->id}: " . $e->getMessage());
            return "Error al generar el sello de validación.";
        }
    }

    private function generateSignature(Article $article): string
    {
        $varCER = 'VIICyT/VIICyT.cer';
        $varKEY = 'VIICyT/VIICyT.key';
        $keyPasswd = 'viicyt2024';

        // obtener los archivos
        $dataCER = Storage::disk('public')->get($varCER);
        $dataKEY = Storage::disk('public')->get($varKEY);

        if (!$dataCER || !$dataKEY) {
            throw new \Exception('No se encontraron los archivos .cer o .key en public storage.');
        }

        // Obtener la clave privada
        $resKEY = openssl_pkey_get_private($dataKEY, $keyPasswd);
        if ($resKEY === false) {
            Log::error("openssl_pkey_get_private falló. ¿Contraseña incorrecta o clave corrupta?");
            throw new \Exception('No se pudo obtener la clave privada. Verifique la contraseña.');
        }

        // Preparar el string para la firma
        $string = '||' . $article->id . '||' . $article->title . '||';
        $stringMD5 = md5($string);

        // Encriptar el string MD5
        $success = openssl_private_encrypt($stringMD5, $encrypted_string, $resKEY);
        
        // Liberar la clave de memoria
        openssl_pkey_free($resKEY);

        if (!$success) {
            throw new \Exception('Fallo al encriptar (firmar) el string: ' . openssl_error_string());
        }

        // Codificar en Base64 (Corregido el typo de 'base664')
        return base64_encode($encrypted_string);
    }

    
}