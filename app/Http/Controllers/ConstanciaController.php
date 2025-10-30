<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
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

        if (!in_array($article->article_status_id, [3, 4])) {
            Log::warning("Intento de descarga de constancia para artículo {$article->id} con estatus no válido ({$article->article_status_id}) por usuario {$user->id}.");
            return redirect()->back()->with('error', 'El artículo no tiene un estatus válido para generar una constancia.');
        }

        if ($user->id !== $article->postulant_id && !$user->canPermission('article.all')) {
            Log::warning("Intento no autorizado de descarga de constancia para artículo {$article->id} por usuario {$user->id}.");
            return redirect()->back()->with('error', 'No tiene permisos para descargar esta constancia.');
        }

        $article->load('authors.institution', 'knowledgeArea.parent', 'articleType', 'call', 'postulant');

        $unique_institutions_list = $article->authors
            ->map(fn ($author) => $author->institution) // Obtener el modelo Institution de cada autor
            ->filter()                                 // Quitar autores sin institución (nulls)
            ->unique('id')                             // Obtener instituciones únicas basadas en su ID
            ->pluck('name')                            // Extraer solo el nombre de esas instituciones
            ->filter()                                 // Quitar nombres vacíos o null
            ->implode(', ');                           // Unir los nombres con ", "

        $data = [
            'article' => $article,
            'authors_list' => $article->authors->map(fn ($a) => trim($a->prefix . ' ' . $a->name . ' ' . $a->surname))->implode(', '),
            'postulant' => $article->postulant,
            'call_name' => $article->call->name ?? 'Convocatoria General',
            'article_type_name' => $article->articleType->name ?? 'No especificado', 
            'institutions_list' => $unique_institutions_list,
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
}