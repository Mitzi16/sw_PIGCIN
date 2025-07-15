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
        $fields = $request->validated();
        $article = $this->storeReviewers($request, $fields);
        $this->sendEmail($article);
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

    public function sendEmail(Article $article)
    {
        $editor = $article->editor;
        $reviewers = $article->articleReviews->pluck('reviewer');

        $articleData = [
            'name' => $editor->name,
            'articleId' => $article->id,
        ];
        Mail::to($editor->email)->queue(new AssignArticle($articleData));

        foreach ($reviewers as $reviewer) {
            $articleData['name'] = $reviewer->name;
            Mail::to($reviewer->email)->queue(new AssignArticle($articleData));
        }
    }
}
