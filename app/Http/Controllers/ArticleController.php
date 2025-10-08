<?php

namespace App\Http\Controllers;

use App\Exports\ArticlesExport;
use App\Http\Requests\EvaluationEditorRequest;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Mail\EvaluationArticle;
use App\Mail\StoreArticle;
use App\Models\Article;
use App\Models\ArticleStatus;
use App\Models\ArticleType;
use App\Models\Author;
use App\Models\Call;
use App\Models\Criterion;
use App\Models\File;
use App\Models\KnowledgeArea;
use App\Models\User;
use App\Services\ArticleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class ArticleController extends Controller
{
    private Article $model;

    private string $source;

    private string $routeName;

    private string $module = 'article';

    private string $storage_path = 'articles';

    private ArticleService $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->middleware('auth');
        $this->source = 'Article/';
        $this->model = new Article;
        $this->routeName = 'article.';
        $this->articleService = $articleService;

        $this->middleware("permission:{$this->module}.index")->only(['index', 'show']);
        $this->middleware("permission:{$this->module}.store")->only(['store', 'create']);
        $this->middleware("permission:{$this->module}.update")->only(['update', 'edit']);
        $this->middleware("permission:{$this->module}.delete")->only(['destroy']);

        $this->authorizeResource(Article::class, $this->module);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $direction = $request->direction ?? 'desc';
        $order = $request->order ?? 'created_at';
        $query = $this->model::query()->with(['articleReviews', 'articleType', 'knowledgeArea.parent']);
        $user = User::findOrFail(Auth::id());

        $currentRouteName = $request->route()->getName();
        $title = 'Gestión de Artículos';

        if (! $user->canPermission('article.all')) {
           if ($currentRouteName === 'article.qualify') {
            $title = 'Artículos para Calificar';
            // Filtra para mostrar SOLO los artículos donde el usuario es Revisor.
            $query->whereHas('articleReviews', function ($query) {
                $query->where('reviewer_id', Auth::id());
            });

        } elseif ($currentRouteName === 'article.diffuse') {
            $title = 'Artículos para Difundir';
            // Filtra para mostrar SOLO los artículos donde el usuario es Editor o Postulante.
            $query->where(function ($query) {
                $query->where('editor_id', Auth::id())
                      ->orWhere('postulant_id', Auth::id());
            });
            $query->whereDoesntHave('articleReviews', function ($subQuery) {
                $subQuery->where('reviewer_id', Auth::id());
            });

        } else {
            // Comportamiento original si se accede desde article.index (la lista mezclada).
            $query->where(function ($query) {
                $query->where('editor_id', Auth::id())
                      ->orWhere('postulant_id', Auth::id())
                      ->orWhereHas('articleReviews', function ($query) {
                          $query->where('reviewer_id', Auth::id());
                      });
            });
        }
        }

        // --- Filtrado por rol para tipos de artículo (más robusto) ---
        $roleNames = $user->roles->pluck('name')->map(fn($r) => (string) $r)->toArray();

        // Helper: obtiene ids de tipos por palabras clave (LIKE) y devuelve array de ids
        $getTypeIdsByKeywords = function (array $keywords) {
            $q = ArticleType::query();
            foreach ($keywords as $i => $kw) {
                $kw = trim($kw);
                if ($i === 0) {
                    $q->where('name', 'LIKE', "%{$kw}%");
                } else {
                    $q->orWhere('name', 'LIKE', "%{$kw}%");
                }
            }
            return $q->pluck('id')->toArray();
        };

        if (in_array('Admin-Revista', $roleNames, true)) {
            // buscamos cualquier type cuyo nombre contenga 'Revista' (tolerante)
            $ids = $getTypeIdsByKeywords(['Revista', 'Artículo Revista', 'Articulo Revista']);
            if (!empty($ids)) {
                // filtramos por ids encontrados
                $query->whereIn('article_type_id', $ids);
            } else {
                // Si no se encontró ningún tipo coincidente, NO aplicamos filtro para evitar lista vacía.
                // (opcional: registrar log para depurar)
                \Log::warning('Admin-Revista: no se encontraron article_types con nombre similar a Revistas');
            }
        } elseif (in_array('Admin', $roleNames, true)) {
            // buscaremos tipos relacionados con Congreso y Póster (tolerante)
            $keywords = [
                'Congreso', // captura "Artículo Congreso (4 a 6 hojas máximo)" u otras variantes
                'Póster Científico',
                'Póster Difusión',
                'Poster', // sin acento por si acaso
                'Póster', // en general
            ];
            $ids = $getTypeIdsByKeywords($keywords);
            if (!empty($ids)) {
                $query->whereIn('article_type_id', $ids);
            } else {
                // No se encontraron coincidencias: no filtramos para evitar lista vacía y lo registramos
                \Log::warning('Admin: no se encontraron article_types para Congreso/Póster con las keywords proporcionadas');
            }
        }
        // --- fin filtrado por rol ---

        $query->when($request->input('search'), function ($query, $search) {
            if ($search != '') {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('article_folio', 'LIKE', '%' . $search . '%')
                        ->orWhere('title', 'LIKE', '%' . $search . '%')
                        ->orWhere('abstract', 'LIKE', '%' . $search . '%')
                        ->orWhere('key_works', 'LIKE', '%' . $search . '%')
                        ->orWhereHas('knowledgeArea', function ($subQuery) use ($search) {
                            $subQuery->where('name', 'LIKE', '%' . $search . '%');
                        })
                        ->orWhereHas('articleStatus', function ($subQuery) use ($search) {
                            $subQuery->where('name', 'LIKE', '%' . $search . '%');
                        })
                        ->orWhereHas('articleType', function ($subQuery) use ($search) {
                            $subQuery->where('name', 'LIKE', '%' . $search . '%');
                        });
                });
            }
        });

        // IMPORTANT: select articles.* antes de hacer joins (evita ambigüedad).
        // Sólo hacemos select si vamos a unir tablas, lo cual hacemos en varios casos.
        $query->select('articles.*');

        if ($order === 'area') {
            // orden por el nombre del área seleccionada directamente
            $query = $query->join('knowledge_areas', 'knowledge_areas.id', '=', 'articles.knowledge_area_id')
                ->orderBy('knowledge_areas.name', $direction);
        } elseif ($order === 'program') {
            // orden por Programa de Estudio (padre si existe, sino el mismo)
            // unimos la knowledge_areas como "ka" (el área seleccionada) y su parent como "pka"
            $query = $query
                ->leftJoin('knowledge_areas as ka', 'ka.id', '=', 'articles.knowledge_area_id')
                ->leftJoin('knowledge_areas as pka', 'pka.id', '=', 'ka.parent_id')
                ->orderByRaw("COALESCE(pka.name, ka.name) {$direction}");
        } elseif ($order === 'status') {
            $query = $query->join('article_statuses', 'article_statuses.id', '=', 'articles.article_status_id')
                ->orderBy('article_statuses.name', $direction);
        } elseif ($order === 'type') {
            $query = $query->join('article_types', 'article_types.id', '=', 'articles.article_type_id')
                ->orderBy('article_types.name', $direction);
        } elseif ($order === 'folio') {
            $query = $query->orderBy('article_folio', $direction);
        } else {
            // Por seguridad: solo permitir ordenar por columnas válidas en articles
            // si el $order no es columna de articles esto tiraría error; puedes mapear valores permitidos.
            $allowed = ['created_at', 'updated_at', 'title', 'article_folio']; // añade las que quieras permitir
            if (in_array($order, $allowed)) {
                $query = $query->orderBy($order, $direction);
            } else {
                // fallback seguro
                $query = $query->orderBy('created_at', $direction);
            }
        }


        $records = $query->paginate(8)->withQueryString()->through(
            fn($article) => [
                'id' => $article->id,
                'folio' => $article->article_folio,
                'title' => $article->title,
                'article_type' => [
                    'id' => $article->articleType?->id,
                    'name' => $article->articleType?->name,
                ],
                'abstract' => $article->abstract,
                // aquí entregamos programa y área prioritaria
                // si el knowledgeArea tiene parent -> programa = parent.name, priority_area = area.name
                // si no tiene parent -> se asume que el area seleccionado es el mismo programa
                'program' => $article->knowledgeArea
                    ? ($article->knowledgeArea->parent ? $article->knowledgeArea->parent->name : $article->knowledgeArea->name)
                    : null,
                'priority_area' => $article->knowledgeArea
                    ? ($article->knowledgeArea->parent ? $article->knowledgeArea->name : null)
                    : null,
                'area' => $article->knowledgeArea ? $article->knowledgeArea->name : null,
                'status' => $article->articleStatus ?? null,
                'paymentVoucherStatus' => $article?->paymentVoucher?->paymentVoucherStatus ?? null,
                'isPostulant' => $article->postulant_id === Auth::id() ? true : false,
                'statusReviewer' => $article->articleReviews->filter(function ($review) {
                    return $review->reviewer_id === Auth::id() && $review->article_status_id !== null;
                })->isNotEmpty(),
            ]
        );

        return Inertia::render("{$this->source}Index", [
            'articles' => $records,
            'title' => $title,
            'isReviewer' => User::find(Auth::id())->getRole(null, 'Revisor'),
            'routeName' => $this->routeName,
            'loadingResults' => false,
            'search' => $request->search ?? '',
            'direction' => $direction,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return Inertia::render("{$this->source}Create", [
            'title' => 'Agregar Artículo',
            'routeName' => $this->routeName,
            'callId' => $request->input('id'),
            'areas' => KnowledgeArea::orderBy('name')->get(),
            'calls' => Call::orderBy('created_at', 'desc')
                ->where('status', true)
                ->get()
                ->map->transform(), // Transforma cada uno de los resultados
            'articleTypes' => ArticleType::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreArticleRequest $request)
    {

        $fields = $request->validated();
        $fields['article_folio'] = $this->articleService->getUniqueCode(); // se inicia el proceso
        $fields['postulant_id'] = Auth::id();
        $article = Article::create($fields);

        // nombre del archivo
        $nombre_archivo = $article->article_folio . '-' . $request->selectedArea . '-' . $article->knowledge_area_id . '-' . $article->article_type_id . '.pdf';

        $this->storeFile($request, $article, $nombre_archivo);
        $this->storeAuthors($request, $article->id);
        $this->sendEmail($article->id);

        return redirect()->route("{$this->routeName}index")->with('success', 'Artículo creado con éxito!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        $article->load(
            'knowledgeArea',
            'articleStatus',
            'articleType',
            'authors.institution:id,name',
            'file',
            'editor:id,name,email',
            'editor.file',
            'articleReviews.reviewer:id,name,email',
            'articleReviews.reviewer.file',
            'articleReviews.articleStatus',
            'articleReviews.criteria:id',
            'call',
            'paymentVoucher'
        );

        $institutions = $article->authors->map(function ($author) {
            return [
                'id' => $author->institution?->id,
                'name' => $author->institution?->name,
            ];
        })->unique('id')->values();

        // Editores (rol ID = 2)
        $editors = User::with('knowledgeArea.parent')
            ->role(2)
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'area' => $u->mainArea()?->name,
                    'subarea' => $u->subArea()?->name,
                ];
            });

        // Revisores (rol ID = 3)
        $reviewers = User::with('knowledgeArea.parent')
            ->role(3)
            ->orderBy('name')
            ->withCount('articleReviews')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'area' => $u->mainArea()?->name,
                    'subarea' => $u->subArea()?->name,
                    'assigned_count' => $u->article_reviews_count,

                ];
            });
        return Inertia::render("{$this->source}Show", [
            'title' => 'Visualizar Artículo',
            'article' => $article,
            'filePath' => $this->getFile($article->file),
            'articleStatuses' => ArticleStatus::orderBy('id')->get(),
            'editors' => $editors,
            'reviewers' => $reviewers,
            'criteria' => Criterion::orderBy('id')->get(),
            'user' => User::with('roles')->find(Auth::id()),
            'areas' => KnowledgeArea::orderBy('name')->get(),
            'institutions' => $institutions,
            'statusReviewer' => $article->articleReviews->filter(function ($review) {
                return $review->reviewer_id === Auth::id() && $review->article_status_id !== null;
            })->isNotEmpty(),
            'routeName' => $this->routeName,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article)
    {
        $user = User::with('roles')->find(Auth::id());

        $institutions = $article->authors->map(function ($author) {
            return [
                'id' => $author->institution?->id,
                'name' => $author->institution?->name,
            ];
        })->unique('id')->values();

        $editors = User::role('Editor')
            ->orderBy('name')
            ->get()
            ->map(function($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                ];
            });

        // Ordenar los revisores por nombre
        $reviewers = User::with('knowledgeArea.parent')
            ->role(3)  // si usas nombre de rol en Spatie
            ->orderBy('name')   // <- aquí se ordena alfabéticamente
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'area' => $u->mainArea()?->name,
                    'subarea' => $u->subArea()?->name,
                ];
            });

        return Inertia::render("{$this->source}Edit", [
            'title' => 'Editar Artículo',
            'article' => $article->load(
                'knowledgeArea',
                'articleStatus',
                'authors',
                'file',
                'articleReviews',
                'call'
            ),
            'filePath' => $this->getFile($article->file),
            'articleStatuses' => ArticleStatus::orderBy('id')->get(),
            'user' => $user,
            'areas' => KnowledgeArea::orderBy('name')->get(),
            'institutions' => $institutions,
            'editors' => $editors,
            'reviewers' => $reviewers,
            'routeName' => $this->routeName,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateArticleRequest $request, Article $article)
    {
        try {
            $fields = $request->validated();
            $fields['article_status_id'] = 1; // se reinicia el proceso
            $article->update($fields);
            $this->updateFile($request, $article);
            $this->updateAuthors($request, $article->id);

            $this->sendEmail($article->id);

            return redirect()->route("{$this->routeName}index")->with('success', 'Artículo modificado con éxito!');
        } catch (\Exception $e) {
            Log::error('Error al almacenar la articulo: ' . $e->getMessage());

            return redirect()->route("{$this->routeName}index")->with('error', 'Ocurrio un error al modificar el artículo!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        $article->delete();

        return redirect()->route("{$this->routeName}index")->with('success', 'Artículo eliminado con éxito');
    }

    public function evaluate(EvaluationEditorRequest $request, Article $article)
    {
        $this->authorize('evaluate', $article);

        $fields = $request->validated();
        $fields['editor_id'] = Auth::id();
        $article->update($fields);
        if ($request->article_status_id === 3) { // aceptado con observaciones, se reinicia el proceso
            if ($article->articleReviews) {
                foreach ($article->articleReviews as $articleReview) {
                    // $articleReview->update(['comment' => null, 'article_status_id' => null]);
                    // $articleReview->criteria()->detach();
                    $articleReview->delete();
                }
            }
        }
        $postulantData = [
            'name' => $article->postulant->name,
            'title' => $article->title,
            'articleId' => $article->id,
        ];
        Mail::to($article->postulant->email)->queue(new EvaluationArticle($postulantData));

        return redirect()->route("{$this->routeName}index")->with('success', 'Artículo evaluado con éxito');
    }

    public function signPdf(Article $article)
    {
        try {
            $this->authorize('successPayment', $article);
            $varCER = 'VIICyT/VIICyT.cer';
            $varKEY = 'VIICyT/VIICyT.key';
            $keyPasswd = 'viicyt2024';

            // obtener los archivos
            $dataCER = Storage::disk('public')->get($varCER);
            $dataKEY = Storage::disk('public')->get($varKEY);

            // Obtener la clave privada
            $resKEY = openssl_pkey_get_private($dataKEY, $keyPasswd);

            // Preparar el string para la firma
            $string = '||' . $article->id . '||' . $article->title . '||';
            $stringMD5 = md5($string);

            // Encriptar el string MD5
            $success = openssl_private_encrypt($stringMD5, $encrypted_string, $resKEY);

            // Codificar en Base64
            $str_b64 = base64_encode($encrypted_string);

            return response()->json(['signedText' => $str_b64]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor: ' . $e->getMessage()], 200);
        }
    }

    private function sendEmail($id)
    {
        $user = User::find(Auth::id());
        $allAdmins = User::role(1)->get();

        $userData = [
            'name' => $user->name,
            'articleId' => $id,
        ];

        foreach ($allAdmins as $admin) {
            Mail::to($admin)->queue(new StoreArticle($admin->name, $userData));
        }
    }

    private function getFile(File $file)
    {
        return URL::signedRoute('file.serve', $file, now()->addMinutes(60));
    }

    private function storeFile(Request $request, Article $article, $nombre_archivo)
    {
        // Verifica si el archivo ya existe y lo elimina
        /* $existingFile = File::where([
            ['fileable_type', Article::class],
            ['fileable_id', $article->id]
        ])->first();

        if ($existingFile) {
            Storage::disk('private')->delete($existingFile->path);
            $existingFile->delete();
        } */

        if ($request->hasFile('file')) {
            $fileStorage = $request->file('file');
            $fileName = (File::max('id') + 1) . '-' . $nombre_archivo;
            $path = $fileStorage->storeAs($this->storage_path, $fileName, 'private');
            $article->file()->create([
                'name' => $fileName,
                'path' => $path,
                'size' => $fileStorage->getSize(),
                'mime_type' => $fileStorage->getMimeType(),
            ]);
        }
    }

    private function updateFile(Request $request, Article $article)
    {
        if ($request->hasFile('file')) {
            $file = File::where([
                ['fileable_type', Article::class],
                ['fileable_id', $article->id],
            ])->first();
            Storage::disk('private')->delete($file->path);

            $fileStorage = $request->file('file');
            $fileName = (File::max('id') + 1) . '-' . $fileStorage->getClientOriginalName();
            $path = $fileStorage->storeAs($this->storage_path, $fileName, 'private');
            $article->file()->update([
                'name' => $fileName,
                'path' => $path,
                'size' => $fileStorage->getSize(),
                'mime_type' => $fileStorage->getMimeType(),
            ]);
        }
    }

    private function storeAuthors(Request $request, $id)
    {
        if ($request->has('authors')) {
            foreach ($request->authors as $author) {
                Author::create([
                    'prefix' => $author['prefix'],
                    'name' => $author['name'],
                    'surname' => $author['surname'],
                    'email' => $author['email'],
                    'institution_id' => $author['institution_id'],
                    'article_id' => $id,
                    'is_corresponding' => $author['is_corresponding'] ?? false,
                ]);
            }
        }
    }

    private function updateAuthors(Request $request, $id)
    {
        if ($request->has('authors')) {
            foreach ($request->authors as $item) {
                if (isset($item['id'])) { // significa que tiene id, es existente
                    $author = Author::findOrFail($item['id']);
                    Log::error('author editado: ' . $item['id']);
                    $author->update([
                        'prefix' => $item['prefix'],
                        'name' => $item['name'],
                        'surname' => $item['surname'],
                        'email' => $item['email'],
                        'institution_id' => $item['institution_id'],
                        'article_id' => $id,
                        'is_corresponding' => $item['is_corresponding'] ?? false,
                    ]);
                } else { // Es un nuevo autor
                    Log::error('author creado: ' . $item['name']);
                    Author::create([
                        'prefix' => $item['prefix'],
                        'name' => $item['name'],
                        'surname' => $item['surname'],
                        'email' => $item['email'],
                        'institution_id' => $item['institution_id'],
                        'article_id' => $id,
                        'is_corresponding' => $item['is_corresponding'] ?? false,
                    ]);
                }
            }
        }
    }

    public function export()
    {
        $user = User::findOrFail(Auth::id());
        $roleNames = $user->roles->pluck('name')->map(fn($r) => (string) $r)->toArray();

        $getTypeIdsByKeywords = function (array $keywords) {
            $q = ArticleType::query();
            foreach ($keywords as $i => $kw) {
                $kw = trim($kw);
                if ($i === 0) {
                    $q->where('name', 'LIKE', "%{$kw}%");
                } else {
                    $q->orWhere('name', 'LIKE', "%{$kw}%");
                }
            }
            return $q->pluck('id')->toArray();
        };

        $typeIds = [];

        if (in_array('Admin-Revista', $roleNames, true)) {
            $typeIds = $getTypeIdsByKeywords(['Revista', 'Artículo Revista', 'Articulo Revista']);
            if (empty($typeIds)) {
                \Log::warning('Admin-Revista export: no se hallaron tipos de artículo con keywords de Revista; exportará todo como fallback.');
                $typeIds = [];
            }
        } elseif (in_array('Admin', $roleNames, true)) {
            $typeIds = $getTypeIdsByKeywords([
                'Congreso',
                'Artículo Congreso',
                'Póster Científico',
                'Póster Difusión',
                'Poster',
                'Póster',
            ]);
            if (empty($typeIds)) {
                \Log::warning('Admin export: no se hallaron tipos de artículo para Congreso/Póster; exportará todo como fallback.');
                $typeIds = [];
            }
        }

        // Usar la clase ArticlesExport con los typeIds
        return Excel::download(new ArticlesExport($typeIds), 'articulos.xlsx');
    }
}
