<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Models\Article;
use App\Models\Author;
use App\Models\ArticleStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    protected string $routeName;
    protected string $source;

    public function __construct()
    {
        $this->routeName = "welcome.";
        $this->source    = "Welcome/";
    }

    public function welcome(Request $request): Response
    {
        $query = Call::query()->with('files');
        $query = $query->orderBy('created_at', 'desc');
        $query->where('status', true);
        $query->when($request->input('search'), function ($query, $search) {
            if ($search != '') {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('title', 'LIKE', '%' . $search . '%')
                        ->orWhere('description', 'LIKE', '%' . $search . '%')
                        ->orWhere('start_date', 'LIKE', '%' . $search . '%')
                        ->orWhere('end_date', 'LIKE', '%' . $search . '%')
                        ->orWhereHas('institution', function ($subQuery) use ($search) {
                            $subQuery->where('name', 'LIKE', '%' . $search . '%');
                        });
                });
            }
        });
        $calls = $query->paginate(3)->through(
            function ($call) {
                return $call->transform();
            }
        );
        return Inertia::render("{$this->source}Home/Index", [
            'title'   => 'Bienvenido',
            'routeName' => $this->routeName,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'calls' => $calls,
            'search' => $request->search ?? '',
        ]);
    }

    public function committee()
    {
        return Inertia::render("{$this->source}Committee/Index", [
            'title'   => 'Comité',
            'routeName' => $this->routeName,
        ]);
    }

    public function place()
    {
        return Inertia::render("{$this->source}Place/Index", [
            'title'   => 'Lugar',
            'routeName' => $this->routeName,
        ]);
    }

    public function program()
    {
        return Inertia::render("{$this->source}Program/Index", [
            'title'   => 'Programa',
            'routeName' => $this->routeName,
        ]);
    }
    public function searchArticle(Request $request, string $code = null)
    {
        // Si viene un "code" directo desde la URL
        if ($code) {
            $article = Article::where('article_folio', $code)->first();

            if ($article) {
                return redirect()->route('welcome.show.article', ['code' => $article->article_folio]);
            }

            return Inertia::render("{$this->source}Article/Search", [
                'title'     => 'Buscador de Artículos',
                'code'      => $code,
                'notFound'  => true,
                'routeName' => $this->routeName,
            ]);
        }

        // Búsqueda desde formulario
        $searchType  = $request->input('searchType');
        $searchValue = $request->input($searchType);

        if ($searchValue) {
            $query = Article::query();

            switch ($searchType) {
                case 'title':
                    $query->where('title', 'like', "%{$searchValue}%");
                    break;

                case 'author':
                    $query->whereHas(
                        'authors',
                        fn($q) =>
                        $q->where('name', 'like', "%{$searchValue}%")
                    );
                    break;

                case 'code':
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('article_folio', $searchValue)            // coincidencia exacta
                            ->orWhere('article_folio', 'like', $searchValue . '%'); // prefijo hasta consecutivo
                    });
                    break;
                case 'identifier':
                    $query->where('article_folio', 'like', "{$searchValue}%"); // 🔹 aquí reemplaza identifier por article_folio
                    break;
            }

            $article = $query->first();

            if ($article) {
                // 🔹 Redirige siempre usando el folio completo
                return redirect()->route('welcome.show.article', ['code' => $article->article_folio]);
            }

            return Inertia::render("{$this->source}Article/Search", [
                'title'     => 'Buscador de Artículos',
                'notFound'  => true,
                'routeName' => $this->routeName,
                'code'      => $searchType === 'code' ? $searchValue : null,
            ]);
        }

        // Vista inicial sin búsqueda
        return Inertia::render("{$this->source}Article/Search", [
            'title'     => 'Buscador de Artículos',
            'routeName' => $this->routeName,
        ]);
    }




    /* public function showArticle(string $code)
    {
        $incident = Incident::with([
            'location.neighborhood.city',
            'incidentType',
            'incidentStatus',
            'evidence.files',
            'files',
            'reviewer' 

        ])
            ->where('unique_code', $code)
            ->first();

        return Inertia::render("{$this->source}Article/Show", [
            'title' => 'Ver documento',
            'incident' => $incident
        ]);
    }  */
    public function showArticle(string $code)
    {
        $article = Article::with(['file', 'status']) // Asegúrate de tener relación con status
            ->where('article_folio', $code)
            ->first();

        if (!$article) {
            return redirect()->route('welcome.search.article', ['code' => $code])
                ->with([
                    'alert' => [
                        'title' => 'No encontrado',
                        'text' => 'No se encontró ningún artículo con ese folio.',
                        'icon' => 'warning',
                        'redirectTo' => route('welcome.search.article')
                    ]
                ]);
        }

        $statuses = ArticleStatus::select('id', 'name', 'description')->get();

        return Inertia::render("{$this->source}Article/Show", [
            'title'            => 'Detalle del Artículo',
            'article'          => $article,
            'articleStatuses'  => $statuses,
            'routeName'        => $this->routeName,
        ]);
    }

    public function searchArticleLive(Request $request)
{
    $q = $request->input('q');

    if (!$q) {
        return response()->json([]);
    }

    $articles = Article::query()
        ->where('article_folio', 'like', "{$q}%")
        ->orWhere('title', 'like', "%{$q}%")
        ->orWhereHas('authors', fn($a) => $a->where('name', 'like', "%{$q}%"))
        ->limit(10)
        ->get(['article_folio', 'title']);

    return response()->json($articles);
}

}
