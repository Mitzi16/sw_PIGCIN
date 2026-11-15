<?php

namespace App\Http\Controllers;

use App\Models\ArticleReview;
use App\Http\Requests\StoreArticleReviewRequest;
use App\Http\Requests\UpdateArticleReviewRequest;
use App\Mail\AssignArticle;
use App\Mail\AssignEditor;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ArticleReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    /**
     * Guarda la asignación de editor y revisores
     */
    public function store(StoreArticleReviewRequest $request)
    {
        $fields = $request->validated();

        // Guardamos las asignaciones y obtenemos el artículo actualizado
        $article = $this->storeReviewers($request, $fields);

        // Obtener info del usuario que realiza la asignación
        $assigner = Auth::user();
        $assignerName = $assigner->name ?? ($assigner->fullname ?? '');

        // Intentamos detectar rol con hasRole (spatie) o con un atributo role
        $assignerRole = 'User';
        if (method_exists($assigner, 'hasRole')) {
            if ($assigner->hasRole('Admin')) $assignerRole = 'Admin';
            elseif ($assigner->hasRole('Editor')) $assignerRole = 'Editor';
        } else {
            $assignerRole = $assigner->role ?? 'User';
        }

        // Enviamos el correo (el artículo ya trae la lista de revisores actualizada)
        $this->sendEmail($article, $assignerName, $assignerRole);

        // Redirecciones
        if (method_exists($assigner, 'hasRole') && $assigner->hasRole('Editor') && $assigner->hasRole('Revisor')) {
            return redirect()->route("article.diffuse")->with('success', 'Artículo asignado correctamente!');
        }

        return redirect()->route("article.index")->with('success', 'Artículo asignado correctamente!');
    }

    public function show(ArticleReview $articleReview)
    {
        //
    }

    public function edit(ArticleReview $articleReview)
    {
        //
    }

    /**
     * Guarda la evaluación del revisor
     */
    public function update(UpdateArticleReviewRequest $request, ArticleReview $articleReview)
    {
        $articleReview->update($request->validated());

        // Verificamos que criteria venga en la petición antes de sincronizar
        if ($request->has('criteria')) {
            $articleReview->criteria()->sync($request->criteria);
        }

        $user = Auth::user();

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('Editor') && $user->hasRole('Revisor')) {
            $article = $articleReview->article;
            if ($article && $article->editor_id == $user->id) {
                return redirect()->route("article.diffuse")->with('success', 'Artículo evaluado correctamente!');
            } else {
                return redirect()->route("article.qualify")->with('success', 'Artículo evaluado correctamente!');
            }
        }
        return redirect()->route("article.index")->with('success', 'Artículo evaluado correctamente!');
    }

    public function destroy(ArticleReview $articleReview)
    {
        //
    }

    /**
     * Lógica central para asignar revisores
     */
    private function storeReviewers(Request $request, $fields)
    {
        // FIX 1: Usar findOrFail para evitar errores si el artículo no existe
        $article = Article::findOrFail($request->article_id);

        // FIX 2: Asegurarnos de que reviewers sea un arreglo, incluso si se desmarcan todos (llega null)
        $reviewerIds = $request->input('reviewers', []);
        if (!is_array($reviewerIds)) $reviewerIds = [];

        // Verifica si el editor asignado está en la lista de nuevos revisores
        if ($article->editor_id && in_array($article->editor_id, $reviewerIds)) {
            throw ValidationException::withMessages([
                'reviewers' => 'El editor asignado a este artículo no puede ser seleccionado como revisor.'
            ]);
        }

        $currentReviewerIds = ArticleReview::where('article_id', $request->article_id)->pluck('reviewer_id')->toArray();
        $reviewerIdsToDelete = array_diff($currentReviewerIds, $reviewerIds);
        $reviewerIdsToAdd = array_diff($reviewerIds, $currentReviewerIds);

        // Eliminamos revisores que ya no están seleccionados
        if (!empty($reviewerIdsToDelete)) {
            ArticleReview::where('article_id', $request->article_id)
                ->whereIn('reviewer_id', $reviewerIdsToDelete)
                ->delete();
        }

        // Agregamos los nuevos revisores
        if (!empty($reviewerIdsToAdd)) {
            foreach ($reviewerIdsToAdd as $reviewerId) {
                ArticleReview::create([
                    'reviewer_id' => $reviewerId,
                    'article_id' => $request->article_id
                ]);
            }
        }

        $hasReviewers = ArticleReview::where('article_id', $request->article_id)->exists();

        if ($hasReviewers) {
            $fields['article_status_id'] = 2; // Cambiar estatus a 'en revisión'
        }

        $article->update($fields);

        // FIX 3: Recargamos la relación para que sendEmail() envíe correos a la lista ACTUALIZADA
        $article->load('articleReviews.reviewer');

        return $article;
    }

    public function sendEmail(Article $article, string $assignerName, string $assignerRole)
    {
        $editor = $article->editor;
        // Usamos articleReviews que acabamos de recargar
        $reviewers = $article->articleReviews->pluck('reviewer');

        // Solo notificar al editor cuando el asignador NO sea un Editor
        if ($editor && $editor->email && $assignerRole !== 'Editor') {
            $articleData = [
                'recipient_name' => $editor->name,
                'articleId' => $article->id,
                'assigner_name' => $assignerName,
                'assigner_role' => $assignerRole,
                'recipient_role' => 'Editor',
            ];
            Mail::to($editor->email)->queue(new AssignArticle($articleData));
        }

        foreach ($reviewers as $reviewer) {
            if (! $reviewer || ! isset($reviewer->email)) continue;
            $articleData = [
                'recipient_name' => $reviewer->name,
                'articleId' => $article->id,
                'assigner_name' => $assignerName,
                'assigner_role' => $assignerRole,
                'recipient_role' => 'Reviewer',
            ];
            Mail::to($reviewer->email)->queue(new AssignArticle($articleData));
        }
    }
}
