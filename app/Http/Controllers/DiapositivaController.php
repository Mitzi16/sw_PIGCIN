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
        $this->storage_path = 'diapositivas'; // Carpeta dentro de 'storage/app/private/'
    }

    /**
     * Muestra la lista de artículos según el ROL del usuario.
     */
    public function index(Request $request): Response
    {
        $user = User::findOrFail(Auth::id());

        $query = Article::query()
            ->with(['correspondingAuthor', 'articleType'])
            ->withExists(['files as has_diapositiva' => function ($query) {
                $query->where('category', 'diapositiva');
            }])
            ->where('article_status_id', 4); // Aceptado
        
        // Rol Admin: Ve todo MENOS 'Articulo Revista'
        if ($user->hasRole('Admin')) {
            // Obtenemos el ID del tipo de artículo a excluir
            $revistaTypeId = ArticleType::where('name', 'Articulo Revista')->value('id');
            if ($revistaTypeId) {
                $query->where('article_type_id', '!=', $revistaTypeId);
            }

        // Rol Admin-Revista: Ve SOLO 'Articulo Revista'
        } elseif ($user->hasRole('Admin-Revista')) {
            $revistaTypeId = ArticleType::where('name', 'Articulo Revista')->value('id');
            if ($revistaTypeId) {
                $query->where('article_type_id', $revistaTypeId);
            } else {
                // Si no existe el tipo, no muestra nada
                $query->where('id', 0); 
            }
        
        // Rol Postulante  Ve solo lo suyo
        } else {
            $query->where('postulant_id', $user->id);
        }

        // Aplicar filtro de búsqueda
        $query->when($request->input('search'), function ($query, $search) {
            // Agrupamos las condiciones 'OR' con una clausura
            $query->where(function ($q) use ($search) {
                // 1. Buscar por título
                $q->where('title', 'like', "%{$search}%")
                // 2. Buscar por folio
                ->orWhere('article_folio', 'like', "%{$search}%")
                // 3. Buscar por nombre o apellido del autor en la relación
                ->orWhereHas('correspondingAuthor', function ($authorQuery) use ($search) {
                    $authorQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('surname', 'like', "%{$search}%");
                });
            });
        });

        // Ordenar, paginar y transformar
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

        // Solo los Postulantes (que no son ni Admin ni Admin-Revista) pueden cargar
        $canUpload = !$user->hasRole('Admin') && !$user->hasRole('Admin-Revista');

        return Inertia::render('Article/Diapositivas/DiapositivaIndex', [
            'title' => 'Gestión de Diapositivas',
            'articles' => $articles,
            'filters' => $request->only('search'),
            'routeName' => $this->routeName,
            'canUpload' => $canUpload, 
        ]);
    }

    /**
     * Muestra el formulario para subir diapositivas (SOLO POSTULANTES).
     */
    public function create(Article $article): Response
    {
        $user = Auth::user();

        if ($user->hasRole('Admin') || $user->hasRole('Admin-Revista')) {
            abort(403, 'Los administradores no tienen permiso para cargar diapositivas.');
        }

        // Lógica de permisos original para el Postulante
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
            'title' => 'Subir Diapositivas: ' . $article->article_folio,
            'article' => $article,
            'diapositiva' => null,
            'diapositivaUrl' => null,
            'routeName' => $this->routeName,
        ]);
    }

    /**
     * Almacena (o reemplaza) la diapositiva (SOLO POSTULANTES).
     */
    public function store(Request $request): RedirectResponse
    {
        // Validación
        $validated = $request->validate([
            'article_id' => 'required|exists:articles,id',
            'expo_type' => ['required', 'string', Rule::in(['presencial', 'virtual'])],
            'diapositiva' => [
                'required', 'file', 'mimes:pdf,ppt,pptx', 'max:25600'
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
            ->with('success', 'Diapositivas subidas y tipo de exposición guardado correctamente.');
    }

    /**
     * Muestra el detalle del artículo según la lógica de roles.
     */
    public function show(Article $article): Response
    {
        $user = Auth::user();

        $article->load(['articleType', 'postulant']);

        $isPostulant = !$user->hasRole('Admin') && !$user->hasRole('Admin-Revista');
        $isRevista = $article->articleType->name === 'Articulo Revista';

        $canView = false;
        
        if ($isPostulant && $article->postulant_id === $user->id) {
            $canView = true;
        }

        if ($user->hasRole('Admin') && !$isRevista) {
            $canView = true;
        }

        if ($user->hasRole('Admin-Revista') && $isRevista) {
            $canView = true;
        }

        if (!$canView) {
            // Si ninguna regla se cumple, se deniega el acceso
            abort(403, 'No autorizado para ver este artículo.');
        }

        $article->load([
            'postulant.institution.country',
            'postulant.institution.state',
            'editor',
            'articleStatus'
        ]);

        // Buscar el archivo de diapositiva
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
            'diapositiva' => $diapositivaFile,
            'diapositivaUrl' => $diapositivaUrl,
            'routeName' => $this->routeName,
            'canUpload' => $isPostulant, 
        ]);
    }
}