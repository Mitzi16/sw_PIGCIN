<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use App\Models\File; // Asegúrate de importar tu modelo File
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DiapositivaController extends Controller
{
    /**
     * Muestra la página de gestión de diapositivas.
     */
    public function index(Request $request): Response
    {
        $user = User::findOrFail(Auth::id());

        // Iniciar la consulta para artículos con los estatus 3 o 4
        $query = Article::query()
            ->with('correspondingAuthor')
            ->whereIn('article_status_id', [3, 4]); // Aceptado o Aceptado con Observaciones

        // Aplicar filtro de permisos
        if (!$user->canPermission('article.all')) {
            $query->where('postulant_id', $user->id);
        }

        // Aplicar filtro de búsqueda
        $query->when($request->input('search'), function ($query, $search) {
            $query->where('title', 'like', "%{$search}%");
        });

        // Ordenar, paginar y transformar los resultados
        $articles = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Article $article) => [
                'id' => $article->id,
                'article_folio' => $article->article_folio,
                'title' => $article->title,
                'status_id' => $article->article_status_id,
                'expo_type' => $article->expo_type, // Puedes añadir esto para verlo en el index
                'author_name' => $article->correspondingAuthor
                    ? trim($article->correspondingAuthor->prefix . ' ' . $article->correspondingAuthor->name . ' ' . $article->correspondingAuthor->surname)
                    : 'No especificado',
                // Deberías añadir una forma de saber si ya tiene diapositiva
                // 'has_diapositiva' => $article->files()->where('category', 'diapositiva')->exists(),
            ]);

        // Renderizar la vista de Inertia
        return Inertia::render('Article/Diapositivas/Index', [
            'title' => 'Gestión de Diapositivas',
            'articles' => $articles,
            'filters' => $request->only('search'),
            'routeName' => 'diapositivas.' // Pasamos el routeName para los enlaces
        ]);
    }

    /**
     * Muestra el formulario para subir diapositivas para un artículo específico.
     * Reemplaza tu método upload() vacío.
     *
     * @param  Article $article
     * @return Response
     */
    public function create(Article $article): Response
    {
        // Verificar permisos
        $user = Auth::user();
        if ($article->postulant_id !== $user->id && !$user->canPermission('article.all')) {
            abort(403, 'No autorizado para este artículo.');
        }

        // Cargar las relaciones necesarias para el panel lateral
        $article->load([
            'postulant.institution.country',
            'postulant.institution.state',
            'article_type',
            'editor',
            'article_status'
        ]);

        return Inertia::render('Article/Diapositivas/Create', [
            'title' => 'Subir Diapositivas: ' . $article->article_folio,
            'article' => $article,
            'routeName' => 'diapositivas.', // Prefijo de ruta para el formulario
        ]);
    }


    /**
     * Almacena la diapositiva y actualiza el tipo de exposición.
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Validación
        $validated = $request->validate([
            'article_id' => 'required|exists:articles,id',
            'expo_type' => ['required', 'string', Rule::in(['presencial', 'virtual'])],
            'diapositiva' => [
                'required',
                'file',
                'mimes:ppt,pptx', // Acepta solo .ppt y .pptx
                'max:20480' // Máximo 20MB
            ],
        ]);

        $article = Article::findOrFail($validated['article_id']);
        $user = Auth::user();

        // Verificar permisos (importante)
        if ($article->postulant_id !== $user->id && !$user->canPermission('article.all')) {
            return redirect()->back()->with('error', 'No tienes permisos para realizar esta acción.');
        }

        // --- Lógica de Archivo ---

        $file = $request->file('diapositiva');
        $category = 'diapositiva';

        // 1. Revisar si ya existe una diapositiva anterior para este artículo
        $existingFile = $article->files()->where('category', $category)->first();

        if ($existingFile) {
            // 2. Si existe, eliminar el archivo físico
            Storage::delete($existingFile->path);
            // 3. Eliminar el registro de la BD
            $existingFile->delete();
        }

        // 4. Guardar el nuevo archivo en la ubicación especificada
        $path = $file->store('private/diapositivas');

        // 5. Crear el nuevo registro en la tabla 'files'
        $article->files()->create([
            'user_id' => $user->id,
            'category' => $category,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        // --- Lógica de Artículo ---
        
        // 6. Actualizar el tipo de exposición en el artículo
        $article->expo_type = $validated['expo_type'];
        $article->save();

        // 7. Redireccionar al index con mensaje de éxito
        return redirect()->route('diapositivas.index')
            ->with('success', 'Diapositivas subidas y tipo de exposición guardado correctamente.');
    }

    /**
     * Lógica para eliminar diapositivas (opcional, si lo necesitas)
     */
    public function destroy(Article $article, File $file)
    {
        // Lógica de permisos similar a store...
        
        // Storage::delete($file->path);
        // $file->delete();

        // return redirect()->route('diapositivas.index')
        //     ->with('success', 'Diapositiva eliminada.');
    }
}