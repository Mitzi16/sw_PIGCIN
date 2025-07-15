<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class ArticlePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Article $article): bool
    {
        return $user->canPermission('article.all') ||
            $user->id === $article->postulant_id ||
            $user->id === $article->editor_id ||
            $article->articleReviews->contains('reviewer_id', $user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Article $article): bool
    {
        return $user->canPermission('article.all') ||
            ($user->id === $article->postulant_id && $article->articleStatus->can_edit);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Article $article): bool
    {
        return $user->canPermission('article.all') || $user->id === $article->postulant_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Article $article): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Article $article): bool
    {
        return true;
    }

    public function evaluate(User $user, Article $article): bool
    {
        return $user->canPermission('article.evaluate') && $user->id === $article->editor_id && $article->article_status_id !== 4;
    }

    public function canStorePaymentVoucher(User $user, Article $article): bool
    {
        return $user->id === $article->postulant_id && $article->article_status_id === 4 && !$article->paymentVoucher;
    }

    public function successPayment(User $user, Article $article): bool
    {
        return $article->article_status_id === 4 && $article?->paymentVoucher?->payment_voucher_status_id === 3; // articulo y voucher aceptado
    }
}
