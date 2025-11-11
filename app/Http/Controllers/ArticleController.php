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
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    private Article $model;

    private string $source;

    private string $routeName;

    private string $currentRouteName;

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
            $title = 'Artículos para Evaluar';
            // Filtra para mostrar SOLO los artículos donde el usuario es Revisor.
            $query->whereHas('articleReviews', function ($query) {
                $query->where('reviewer_id', Auth::id());
            });

        } elseif ($currentRouteName === 'article.diffuse') {
            $title = 'Artículos para Distribuir';
            // Filtra para mostrar SOLO los artículos donde el usuario es Editor 
            $query->where(function ($query) {
                $query->where('editor_id', Auth::id());
                      //->orWhere('postulant_id', Auth::id());
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
                Log::warning('Admin-Revista: no se encontraron article_types con nombre similar a Revistas');
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
                Log::warning('Admin: no se encontraron article_types para Congreso/Póster con las keywords proporcionadas');
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
            'currentRouteName' => $currentRouteName,
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
        $this->sendEmail($article->id, $article->article_folio);

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
        //'file',
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

    $assignedEditorId = $article->editor_id;

    $reviewersQuery = User::with('knowledgeArea.parent')
        ->role(3) // Rol ID 3
        ->orderBy('name');

    if ($assignedEditorId) {
        $reviewersQuery->where('id', '!=', $assignedEditorId);
    }

    $reviewers = $reviewersQuery->withCount('articleReviews')
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
    
    $modifiedFile = $article->files()
            ->where('name', 'LIKE', '%_MOD.pdf')
            ->latest() // Obtiene el más reciente en caso de que haya varios
            ->first();
    
    // 2. Si NO existe un archivo modificado, buscamos el original.
        $fileToShow = $modifiedFile; // Asignamos el modificado por defecto

    if (! $fileToShow) {
            // Buscamos el primer archivo que NO sea _MOD (el original)
            $fileToShow = $article->files()
                ->where('name', 'NOT LIKE', '%_MOD.pdf')
                ->oldest() // Obtiene el más antiguo (el original)
                ->first();
        }

        // 3. Generamos la URL firmada solo si encontramos un archivo
        $filePath = null;
        $fileName = null;
        if ($fileToShow) {
            // Usamos tu método privado getFile()
            $filePath = $this->getFile($fileToShow);
            $fileName = $fileToShow->name;
        }

    return Inertia::render("{$this->source}Show", [
        'title' => 'Visualizar Artículo',
        'article' => $article,
        'filePath' => $filePath,
        'fileName' => $fileName,
        'articleStatuses' => ArticleStatus::orderBy('id')->get(),
        'editors' => $editors,
        'reviewers' => $reviewers, // Se envía la lista ya filtrada
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

    $assignedEditorId = $article->editor_id;

    $reviewersQuery = User::with('knowledgeArea.parent')
        ->role(3) 
        ->orderBy('name'); // <- aquí se ordena alfabéticamente

    if ($assignedEditorId) {
        $reviewersQuery->where('id', '!=', $assignedEditorId);
    }

    $reviewers = $reviewersQuery->get()
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
        'reviewers' => $reviewers, // Se envía la lista ya filtrada
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
            $fields['article_status_id'] = 6; // se reinicia el proceso
            $article->update($fields);
            $this->updateFile($request, $article);
            $this->updateAuthors($request, $article->id);

            $this->sendEmail($article->id, $article->article_folio);

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

        $user=request()->user();
        $successMessage = 'Artículo evaluado con éxito!';

        $isDualRole = $user->hasRole('Editor') && $user->hasRole('Revisor');
        $isEditorOfArticle = $article->editor_id === $user->id;

        if ($isDualRole) {
            $routeName = $isEditorOfArticle ? 'article.diffuse' : 'article.qualify';
            return redirect()->route($routeName)->with('success', $successMessage);
        } 
        return redirect()->route("{$this->routeName}index")->with('success', $successMessage);
        //redirect()->back()->with('success', 'Artículo evaluado con éxito!');
        //return redirect()->route("{$this->routeName}index")->with('success', 'Artículo evaluado con éxito');
    }

    /**
     * Obtiene todos los autores de un artículo para el reconocimiento grupal.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\JsonResponse
     */
    public function getArticleAuthors(Article $article)
    {
        // 1. Autorizar que el usuario pueda ver este artículo
        // (Usa la misma política que 'show' o 'view')
        try {
            $this->authorize('view', $article);
        } catch (\Exception $e) {
            // Si no tiene permiso, devolver un error
            return response()->json(['error' => 'No autorizado para ver este artículo.'], 403);
        }

        $article->load('authors', 'articleType', 'knowledgeArea');

        // 2. Obtener los autores usando la relación que ya existe
        //$authors = $article->authors; // Esto carga la colección de autores

        // 3. Formatear los nombres
        // Itera sobre cada objeto 'Author' y crea un string con el nombre completo
        $formattedNames = $article->authors->map(function ($author) {
            // Formato: "Prefijo Nombre Apellido" (ej: "Doctor(a) felipe tr")
            // trim() elimina espacios extra si el prefijo estuviera vacío
            return trim($author->prefix . ' ' . $author->name . ' ' . $author->surname);
        });

        // 4. Devolver la respuesta JSON que el frontend espera
        return response()->json([
            'authors' => $formattedNames,
            'article_type_name'   => optional($article->articleType)->name, 
            'knowledge_area_name' => optional($article->knowledgeArea)->name,
            'article_type_id' => $article->article_type_id,
            'knowledge_area_id' => $article->knowledge_area_id,
        ]);
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

    private function sendEmail($id,$folio)
    {
        $user = User::find(Auth::id());
        $allAdmins = User::role(1)->get();

        $userData = [
            'name' => $user->name,
            'articleId' => $id,
            'folio' => $folio,
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
    if (!$request->hasFile('file')) {
        return;
    }

    $fileStorage = $request->file('file');
    $newPath = null;
    $newPrefixedName = null;
    $newFileRecord = null;

    try {
        DB::beginTransaction();

        // 1. Crear el registro en BD primero (con datos temporales)
        $newFileRecord = $article->files()->create([
            'name' => 'TEMP_STORE',
            'path' => 'TEMP_STORE',
            'size' => $fileStorage->getSize(),
            'mime_type' => $fileStorage->getMimeType(),
        ]);

        // 2. Obtener el ID atómico de la BD
        $newFileId = $newFileRecord->id;
        $newPrefixedName = $newFileId . '-' . $nombre_archivo;
        $newPath = $this->storage_path . '/' . $newPrefixedName;

        // 3. Guardar el archivo físico
        if (!$fileStorage->storeAs($this->storage_path, $newPrefixedName, 'private')) {
            throw new \Exception("Fallo al guardar el archivo en storage (storeFile).");
        }

        // 4. Actualizar el registro de la BD con los datos correctos
        $newFileRecord->name = $newPrefixedName;
        $newFileRecord->path = $newPath;
        $newFileRecord->save();

        // 5. Confirmar transacción
        DB::commit();

    } catch (\Exception $e) {
        // 6. Algo salió mal, revertir todo
        DB::rollBack();

        // Si el archivo se guardó pero la BD falló, borrar el archivo huérfano
        if ($newPath && Storage::disk('private')->exists($newPath)) {
            Storage::disk('private')->delete($newPath);
        }
        Log::error("Error en storeFile (transacción): " . $e->getMessage());
        // Relanzar la excepción para que el controlador 'store' falle
        throw $e; 
    }
}

/**
 * updateFile: MODIFICADO para ser transaccional y evitar 'race conditions'
 */
private function updateFile(Request $request, Article $article)
{
    if (!$request->hasFile('file')) {
        return;
    }

    // --- 1. Obtener nombre base (operación de solo lectura, segura) ---
    $originalFileRecord = $article->files()->orderBy('created_at', 'asc')->first();

    if (!$originalFileRecord) {
        Log::error("UpdateFile: No se encontró NINGÚN registro 'File' para el artículo {$article->id} al intentar actualizar.");
        throw new \Exception("No se encontró el archivo original del artículo {$article->id}.");
    }

    $originalDbName = $originalFileRecord->name;
    $firstDashPos = strpos($originalDbName, '-');
    $baseNameWithExtension = $originalDbName;

    if ($firstDashPos !== false) {
        $baseNameWithExtension = substr($originalDbName, $firstDashPos + 1);
    } else {
        Log::warning("UpdateFile: El nombre '{$originalDbName}' no tiene el prefijo de ID. Se usará el nombre completo como base.");
    }

    $baseName = pathinfo($baseNameWithExtension, PATHINFO_FILENAME);
    
    // Bucle de limpieza para quitar sufijos _MOD
    while (str_ends_with(strtoupper($baseName), '_MOD')) {
        $baseName = substr($baseName, 0, -4);
    }

    $newModBaseName = $baseName . '_MOD.pdf';
    // --- Fin de obtener nombre base ---

    $fileStorage = $request->file('file');
    $newPath = null;
    $newPrefixedName = null;
    $newFileRecord = null;

    try {
        // 2. Iniciar la transacción
        DB::beginTransaction();

        // 3. Buscar y eliminar mods anteriores
        // ¡Importante! Usamos withTrashed() para encontrar también los soft-deleted
        $previousModFiles = $article->files()
            ->withTrashed() // <-- AÑADIR ESTO
            ->where('name', 'LIKE', '%_MOD.pdf')
            ->lockForUpdate() 
            ->get();

        foreach ($previousModFiles as $fileToDelete) {
            Storage::disk('private')->delete($fileToDelete->path);
            $fileToDelete->forceDelete(); // <-- ESTE ES EL CAMBIO CLAVE
        }

        // 4. Crear el *nuevo* registro en la BD (con datos temporales)
        $newFileRecord = $article->files()->create([
            'name' => 'TEMP_UPDATE',
            'path' => 'TEMP_UPDATE',
            'size' => $fileStorage->getSize(),
            'mime_type' => $fileStorage->getMimeType(),
        ]);

        // 5. Obtener el ID atómico de la BD
        $newFileId = $newFileRecord->id;
        $newPrefixedName = $newFileId . '-' . $newModBaseName; 
        $newPath = $this->storage_path . '/' . $newPrefixedName;

        // 6. Guardar el archivo físico
        if (!$fileStorage->storeAs($this->storage_path, $newPrefixedName, 'private')) {
            throw new \Exception("Fallo al guardar el archivo en storage (updateFile).");
        }

        // 7. Actualizar el registro de la BD con los datos correctos
        $newFileRecord->name = $newPrefixedName;
        $newFileRecord->path = $newPath;
        $newFileRecord->save();

        // 8. Confirmar transacción
        DB::commit();

    } catch (\Exception $e) {
        // 9. Algo salió mal, revertir todo
        DB::rollBack();

        // Si el archivo se guardó pero la BD falló, borrar el archivo huérfano
        if ($newPath && Storage::disk('private')->exists($newPath)) {
            Storage::disk('private')->delete($newPath);
        }
        Log::error("Error en updateFile (transacción): " . $e->getMessage());
        // Relanzar la excepción para que el controlador 'update' falle
        throw $e;
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
                Log::warning('Admin-Revista export: no se hallaron tipos de artículo con keywords de Revista; exportará todo como fallback.');
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
                Log::warning('Admin export: no se hallaron tipos de artículo para Congreso/Póster; exportará todo como fallback.');
                $typeIds = [];
            }
        }

        // Usar la clase ArticlesExport con los typeIds
        return Excel::download(new ArticlesExport($typeIds), 'articulos.xlsx');
    }
}
