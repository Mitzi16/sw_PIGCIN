<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use App\Models\File;
use App\Models\ArticleType; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class DiapositivaController extends Controller
{
    private string $routeName;
    private string $storage_path;

    public function __construct()
    {
        $this->middleware('auth');
        $this->routeName = 'diapositivas.';
        $this->storage_path = 'diapositivas'; 
    }

    public function index(Request $request): Response
    {
        $user = User::findOrFail(Auth::id());

        $query = Article::query()
            ->with(['correspondingAuthor', 'articleType'])
            ->withExists(['files as has_diapositiva' => function ($query) {
                $query->where('category', 'diapositiva');
            }])
            ->where('article_status_id', 4); // Solo aceptados
        
        // Si NO es Admin ni Admin-Revista (es decir, es Postulante), filtramos por su ID.
        // Si ES Admin o Admin-Revista, no aplicamos filtro (ven todos los tipos).
        if (!$user->hasRole('Admin') && !$user->hasRole('Admin-Revista')) {
            $query->where('postulant_id', $user->id);
        }
        // -----------------------------------------------------

        $query->when($request->input('search'), function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('article_folio', 'like', "%{$search}%")
                ->orWhereHas('correspondingAuthor', function ($authorQuery) use ($search) {
                    $authorQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('surname', 'like', "%{$search}%");
                });
            });
        });

        $articles = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Article $article) => [
                'id' => $article->id,
                'article_folio' => $article->article_folio,
                'title' => $article->title,
                'status_id' => $article->article_status_id,
                'expo_type' => $article->expo_type,
                'author_name' => $article->correspondingAuthor
                    ? trim($article->correspondingAuthor->prefix . ' ' . $article->correspondingAuthor->name . ' ' . $article->correspondingAuthor->surname)
                    : 'No especificado',
                'has_diapositiva' => $article->has_diapositiva,
            ]);

        $canUpload = !$user->hasRole('Admin') && !$user->hasRole('Admin-Revista');

        return Inertia::render('Article/Diapositivas/DiapositivaIndex', [
            'title' => 'Gestión de Diapositivas',
            'articles' => $articles,
            'filters' => $request->only('search'),
            'routeName' => $this->routeName,
            'canUpload' => $canUpload, 
        ]);
    }

    public function create(Article $article): Response
    {
        $user = Auth::user();

        if ($user->hasRole('Admin') || $user->hasRole('Admin-Revista')) {
            abort(403, 'Los administradores no tienen permiso para cargar diapositivas.');
        }

        if ($article->postulant_id !== $user->id && !$user->canPermission('article.all')) {
            abort(403, 'No autorizado para este artículo.');
        }

        $article->load([
            'postulant.institution.country',
            'postulant.institution.state',
            'articleType',
            'editor',
            'articleStatus'
        ]);

        return Inertia::render('Article/Diapositivas/DiapositivaCreate', [
            'title' => 'Subir Diapositivas o Video: ' . $article->article_folio,
            'article' => $article,
            'diapositiva' => null,
            'diapositivaUrl' => null,
            'routeName' => $this->routeName,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Validación Actualizada
        $validated = $request->validate([
            'article_id' => 'required|exists:articles,id',
            'expo_type' => ['required', 'string', Rule::in(['presencial', 'virtual'])],
            'diapositiva' => [
                'required', 
                'file', 
                // AQUI AGREGAMOS 'mp4'
                'mimes:pdf,ppt,pptx,mp4', 
                'max:2097152' 
            ],
        ]);

        $article = Article::findOrFail($validated['article_id']);
        $user = Auth::user();

        if ($user->hasRole('Admin') || $user->hasRole('Admin-Revista')) {
            return redirect()->back()->with('error', 'Los administradores no tienen permiso para cargar diapositivas.');
        }

        if ($article->postulant_id !== $user->id && !$user->canPermission('article.all')) {
            return redirect()->back()->with('error', 'No tienes permisos para realizar esta acción.');
        }

        $file = $request->file('diapositiva');
        $category = 'diapositiva';
        $safeFolio = str_replace(['/', '\\'], '-', $article->article_folio);
        $extension = $file->getClientOriginalExtension();
        $customFileName = $safeFolio . '_' . $category . '.' . $extension;

        $existingFile = $article->files()->where('category', $category)->first();
        if ($existingFile) {
            Storage::disk('private')->delete($existingFile->path);
        }
        $path = $file->storeAs($this->storage_path, $customFileName, 'private');

        $article->files()->updateOrCreate(
            ['category' => $category],
            [
                'user_id' => $user->id,
                'name' => $customFileName,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]
        );

        $article->expo_type = $validated['expo_type'];
        $article->save();

        return redirect()->route("{$this->routeName}index")
            ->with('success', 'Archivo subido correctamente.');
    }

    public function show(Article $article): Response
    {
    $user = Auth::user();

    $article->load(['articleType', 'postulant']);

    // Mantenemos esta variable para controlar el botón de 'canUpload' al final
    $isPostulant = !$user->hasRole('Admin') && !$user->hasRole('Admin-Revista');
    
    $canView = false;

    // 1. Lógica unificada: Admin y Admin-Revista ven TODO
    if ($user->hasRole('Admin') || $user->hasRole('Admin-Revista')) {
        $canView = true;
    }

    // 2. Lógica para postulante: Solo ve lo suyo
    if ($isPostulant && $article->postulant_id === $user->id) {
        $canView = true;
    }

    if (!$canView) {
        abort(403, 'No autorizado para ver este artículo.');
    }

    $article->load([
        'postulant.institution.country',
        'postulant.institution.state',
        'editor',
        'articleStatus'
    ]);

    $diapositivaFile = $article->files()->where('category', 'diapositiva')->first();
    $diapositivaUrl = null;
    
    if ($diapositivaFile) {
        try {
            $diapositivaUrl = URL::signedRoute('file.serve', $diapositivaFile, now()->addMinutes(60));
        } catch (\Exception $e) {
            report($e);
            $diapositivaUrl = null; 
        }
    }

    return Inertia::render('Article/Diapositivas/DiapositivaShow', [
        'title' => 'Ver Diapositivas: ' . $article->article_folio,
        'article' => $article,
        // Inertia pasará este objeto al frontend, incluyendo 'mime_type'
        'diapositiva' => $diapositivaFile, 
        'diapositivaUrl' => $diapositivaUrl,
        'routeName' => $this->routeName,
        'canUpload' => $isPostulant, 
    ]);
    }
}