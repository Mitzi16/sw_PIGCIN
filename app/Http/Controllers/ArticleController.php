<?php

namespace App\Http\Controllers;

use App\Http\Requests\EvaluationEditorRequest;
use App\Models\Article;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Mail\EvaluationArticle;
use App\Mail\StoreArticle;
use App\Models\ArticleReview;
use App\Models\ArticleStatus;
use App\Models\ArticleType;
use App\Models\Author;
use App\Models\Call;
use App\Models\Criterion;
use App\Models\File;
use App\Models\Institution;
use App\Models\KnowledgeArea;
use App\Models\User;
use Database\Seeders\KnowledgeAreaSeeder;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use App\Services\ArticleService;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArticlesExport;
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
        $this->model = new Article();
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
        $query = $this->model::query()->with(['articleReviews', 'articleType']);
        $user = User::findOrFail(Auth::id());

        if (!$user->canPermission('article.all')) {
            $query->where(function ($query) {
                $query->where('editor_id', Auth::id())
                    ->orWhere('postulant_id', Auth::id())
                    ->orWhereHas('articleReviews', function ($query) {
                        $query->where('reviewer_id', Auth::id());
                    });
            });
        }

        $query->when($request->input('search'), function ($query, $search) {
            if ($search != '') {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('article_folio', 'LIKE', '%' . $search . '%')
                        ->orWhere('title', 'LIKE', '%' . $search . '%')
                        ->orWhere('type', 'LIKE', '%' . $search . '%')
                        ->orWhere('abstract', 'LIKE', '%' . $search . '%')
                        ->orWhere('key_works', 'LIKE', '%' . $search . '%')
                        ->orWhereHas('knowledgeArea', function ($subQuery) use ($search) {
                            $subQuery->where('name', 'LIKE', '%' . $search . '%');
                        })
                        ->orWhereHas('articleStatus', function ($subQuery) use ($search) {
                            $subQuery->where('name', 'LIKE', '%' . $search . '%');
                        });
                });
            }
        });

        if ($order === 'area') {
            $query = $query->join('knowledge_areas', 'knowledge_areas.id', '=', 'articles.knowledge_area_id')
                ->orderBy('knowledge_areas.name', $direction);
        } else if ($order === 'status') {
            $query = $query->join('article_statuses', 'article_statuses.id', '=', 'articles.article_status_id')
                ->orderBy('article_statuses.name', $direction);
        } else {
            $query = $query->orderBy($order, $direction);
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
                'area' => $article->knowledgeArea ? $article->knowledgeArea->name : null,
                'status' => $article->articleStatus ?? null,
                'paymentVoucherStatus' => $article?->paymentVoucher?->paymentVoucherStatus ?? null,
                'isPostulant' => $article->postulant_id === Auth::id() ? true : false,
                'statusReviewer' => $article->articleReviews->filter(function ($review) {
                    return $review->reviewer_id === Auth::id() && $review->article_status_id !== null;
                })->isNotEmpty() // Verifica si el usuario es revisor
            ]
        );

        return Inertia::render("{$this->source}Index", [
            'articles'       =>  $records,
            'title'          => 'Gestión de Artículos',
            'isReviewer'      => User::find(Auth::id())->getRole(null, 'Revisor'),
            'routeName'      => $this->routeName,
            'loadingResults' => false,
            'search'         => $request->search ?? '',
            'direction'      => $direction,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return Inertia::render("{$this->source}Create", [
            'title'         => 'Agregar Artículo',
            'routeName'     => $this->routeName,
            'callId'        => $request->input('id'),
            'areas'         => KnowledgeArea::orderBy('name')->get(),
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

        //nombre del archivo
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
        return Inertia::render("{$this->source}Show", [
            'title'             => 'Visualizar Artículo',
            'article'           => $article,
            'filePath'          => $this->getFile($article->file),
            'articleStatuses'   => ArticleStatus::orderBy('id')->get(),
            'editors'           => User::role(2)->get(),
            'reviewers'         => User::role(3)->get(),
            'criteria'          => Criterion::orderBy('id')->get(),
            'user'              => User::with('roles')->find(Auth::id()),
            'areas'             => KnowledgeArea::orderBy('name')->get(),
            'institutions'      => $institutions,
            'statusReviewer' => $article->articleReviews->filter(function ($review) {
                return $review->reviewer_id === Auth::id() && $review->article_status_id !== null;
            })->isNotEmpty(), // Verifica si el usuario es revisor
            'routeName'         => $this->routeName,
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

    
    $editors = User::role('Editor')->get()->map(fn($u) => [
        'id'   => $u->id,
        'name' => $u->name,
    ]);

    
    $reviewers = User::with('knowledgeArea.parent')
        ->role('Reviewer') // si usas nombre de rol en Spatie
        ->get()
        ->map(function ($u) {
            return [
                'id'      => $u->id,
                'name'    => $u->name,
                'area'    => $u->mainArea()?->name,
                'subarea' => $u->subArea()?->name,
            ];
        });

    return Inertia::render("{$this->source}Edit", [
        'title'           => 'Editar Artículo',
        'article'         => $article->load(
            'knowledgeArea',
            'articleStatus',
            'authors',
            'file',
            'articleReviews',
            'call'
        ),
        'filePath'        => $this->getFile($article->file),
        'articleStatuses' => ArticleStatus::orderBy('id')->get(),
        'user'            => $user,
        'areas'           => KnowledgeArea::orderBy('name')->get(),
        'institutions'    => $institutions,
        'editors'         => $editors,    
        'reviewers'       => $reviewers,  
        'routeName'       => $this->routeName,
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
            $keyPasswd = "viicyt2024";

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

            return response()->json(["signedText" => $str_b64]);
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
            'articleId' => $id
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
                ['fileable_id', $article->id]
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
        return Excel::download(new ArticlesExport, 'articulos.xlsx');
    }
}
