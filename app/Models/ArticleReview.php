<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleReview extends Model
{
    use HasFactory;

    protected $table = 'article_reviews';

    protected $fillable = [
        'comment',
        'reviewer_id',
        'article_status_id',
        'article_id'
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function articleStatus()
    {
        return $this->belongsTo(ArticleStatus::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function criteria()
    {
        return $this->belongsToMany(Criterion::class);
    }
}
