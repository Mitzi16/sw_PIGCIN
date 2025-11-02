<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Http;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage; 
use Barryvdh\DomPDF\Facade\Pdf;

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
            ->whereIn('article_status_id', [3, 4]);

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
            ]);

        return Inertia::render('Article/Constancias', [
            'title' => 'Gestión de Constancias',
            'articles' => $articles,
            'filters' => $request->only('search'), 
        ]);
    }

    /**
     * Genera y descarga la constancia en PDF para un artículo.
     * Este método responde a la ruta /articles/{article}/certificate
     */
   public function generateCertificate(Article $article)
    {
        $user = Auth::user();

        // ... (Toda tu lógica de validación de permisos va aquí) ...
        // ... (if (!in_array(...))
        // ... (if ($user->id !== ...))

        $article->load('authors.institution', 'knowledgeArea.parent', 'articleType', 'call', 'postulant');

        $unique_institutions_list = $article->authors
            ->map(fn ($author) => $author->institution)
            ->filter()
            ->unique('id')
            ->pluck('name')
            ->filter()
            ->implode(', ');

        
        // --- INICIO DE LÓGICA DE QR Y SELLO ---

        // 1. Generar el Sello Digital (ahora usa el helper interno)
        $signedText = $this->getSignedTextFromServer($article);

        // 2. Generar el Código QR
        $authorsForQr = $article->authors->map(fn ($a) => trim($a->prefix . ' ' . $a->name . ' ' . $a->surname))->implode(', ');
        $qrContent = "Folio: {$article->article_folio}\nAutores: {$authorsForQr}\nTítulo: {$article->title}";
        
        // Generamos el QR como SVG (opción más compatible) o PNG
        $qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode(
            QrCode::format('svg') // Usamos SVG, no requiere Imagick
                  ->size(150)
                  ->errorCorrection('H')
                  ->generate($qrContent)
        );

        // --- FIN DE LÓGICA DE QR Y SELLO ---

        $data = [
            'article' => $article,
            'authors_list' => $article->authors->map(fn ($a) => trim($a->prefix . ' ' . $a->name . ' ' . $a->surname))->implode(', '),
            'postulant' => $article->postulant,
            'call_name' => $article->call->name ?? 'Convocatoria General',
            'article_type_name' => $article->articleType->name ?? 'No especificado', 
            'institutions_list' => $unique_institutions_list,
            'qrCodeBase64' => $qrCodeBase64,
            'signedText' => $signedText,
        ];

        try {
            $pdf = Pdf::loadView('pdfs.constancia', $data);
            $fileName = 'Constancia_' . $article->article_folio . '.pdf';
            return $pdf->stream($fileName);

        } catch (\Exception $e) {
            Log::error("Error al generar PDF para artículo {$article->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error interno al generar el documento PDF.');
        }
    }

    /**
     * Endpoint de API para obtener el sello (si aún lo necesitas para otras cosas).
     * Ahora solo llama a la función de lógica principal.
     */
    public function signPdf(Article $article)
    {
        try {
            $this->authorize('successPayment', $article);

            // Llama a la lógica refactorizada
            $str_b64 = $this->generateSignature($article);

            return response()->json(['signedText' => $str_b64]);
        } catch (\Exception $e) {
            Log::error("Error en signPdf API para {$article->id}: " . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor: ' . $e->getMessage()], 500); // 500 es mejor para errores
        }
    }

    // --- FUNCIONES HELPER PRIVADAS ---

    /**
     * Helper para obtener el sello. Llama a la lógica de firmado.
     * Es usado por generateCertificate()
     */
    private function getSignedTextFromServer(Article $article): string
    {
        try {
            // ¡Llama directamente a la nueva función de lógica!
            return $this->generateSignature($article);
        } catch (\Exception $e) {
            Log::error("Error al obtener sello para PDF {$article->id}: " . $e->getMessage());
            return "Error al generar el sello de validación.";
        }
    }

    /**
     * ¡NUEVA FUNCIÓN CENTRALIZADA!
     * Lógica principal para generar la firma/sello digital.
     * Es usada por signPdf() y getSignedTextFromServer()
     */
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
            throw new \Exception('No se pudo obtener la clave privada. Verifique la contraseña.');
        }

        // Preparar el string para la firma
        $string = '||' . $article->id . '||' . $article->title . '||';
        $stringMD5 = md5($string);

        // Encriptar el string MD5
        $success = openssl_private_encrypt($stringMD5, $encrypted_string, $resKEY);
        if (!$success) {
            throw new \Exception('Fallo al encriptar (firmar) el string: ' . openssl_error_string());
        }

        // Codificar en Base64
        return base64_encode($encrypted_string);
    }

    
}