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

class ArticleReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreArticleReviewRequest $request)
    {
        //dd($request);
        /*  $fields = $request->validated();
        $article = $this->storeReviewers($request, $fields);
        $this->sendEmail($article);
        return redirect()->route("article.index")->with('success', 'Artículo asignado correctamente!'); */
        $fields = $request->validated();
        $article = $this->storeReviewers($request, $fields);

        // Obtener info del usuario que realiza la asignación
        $assigner = Auth::user();
        $assignerName = $assigner->name ?? ($assigner->fullname ?? ''); // fallback si tu modelo usa otro campo
        // Intentamos detectar rol con hasRole (spatie) o con un atributo role
        $assignerRole = null;
        if (method_exists($assigner, 'hasRole')) {
            if ($assigner->hasRole('Admin')) $assignerRole = 'Admin';
            elseif ($assigner->hasRole('Editor')) $assignerRole = 'Editor';
            else $assignerRole = 'User';
        } else {
            // fallback al atributo role (ajusta si usas otro nombre)
            $assignerRole = $assigner->role ?? 'User';
        }

        $this->sendEmail($article, $assignerName, $assignerRole);

        return redirect()->route("article.index")->with('success', 'Artículo asignado correctamente!');
    }

    /**
     * Display the specified resource.
     */
    public function show(ArticleReview $articleReview)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ArticleReview $articleReview)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateArticleReviewRequest $request, ArticleReview $articleReview)
    {
        $articleReview->update($request->validated());

        $articleReview->criteria()->sync($request->criteria);
        return redirect()->route("article.index")->with('success', 'Artículo evaluado correctamente!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ArticleReview $articleReview)
    {
        //
    }

    private function storeReviewers(Request $request, $fields)
    {
        if ($request->has('reviewers')) {
            $reviewerIds = $request->reviewers;
            $currentReviewerIds = ArticleReview::where('article_id', $request->article_id)->pluck('reviewer_id')->toArray();
            $reviewerIdsToDelete = array_diff($currentReviewerIds, $reviewerIds);
            $reviewerIdsToAdd = array_diff($reviewerIds, $currentReviewerIds);

            if (!empty($reviewerIdsToDelete)) {
                ArticleReview::where('article_id', $request->article_id)
                    ->whereIn('reviewer_id', $reviewerIdsToDelete)
                    ->delete();
            }

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
            // else {
            //     $fields['article_status_id'] = 2; // Regresar a 1er estatus CONSULTAR CON VITER
            // }
        }
        $article = Article::find($request->article_id);
        $article->update($fields);
        return $article;
    }

   public function sendEmail(Article $article, string $assignerName, string $assignerRole)
    {
        /* $editor = $article->editor;
        dd($editor);
        $reviewers = $article->articleReviews->pluck('reviewer');

        $articleData = [
            'name' => $editor->name,
            'articleId' => $article->id,
        ];
        Mail::to($editor->email)->queue(new AssignArticle($articleData));

        foreach ($reviewers as $reviewer) {
            $articleData['name'] = $reviewer->name;
            Mail::to($reviewer->email)->queue(new AssignArticle($articleData));
        } */
        // Editor asignado al artículo
        $editor = $article->editor; // asumiendo relación editor()
        // Revisores asignados
        $reviewers = $article->articleReviews->pluck('reviewer');

        // Email para el editor (cuando aplica)
        if ($editor && $editor->email) {
            $articleData = [
                'recipient_name' => $editor->name,
                'articleId' => $article->id,
                'assigner_name' => $assignerName,
                'assigner_role' => $assignerRole,
                'recipient_role' => 'Editor', // opcional, por si la plantilla lo necesita
            ];
            Mail::to($editor->email)->queue(new AssignArticle($articleData));
        }

        // Emails para revisores (si hubo)
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
