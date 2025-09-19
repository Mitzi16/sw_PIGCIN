<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArticleReview;

class ArticleEvaluation extends Model
{
    protected $fillable = [
        'article_id',
        'rubric_item_id',
        'rubric_answer_id',
        'score',
        'article_score', 
        'user_id'
    ];

    public function rubricItem()
    {
    return $this->belongsTo(\App\Models\RubricItem::class);
    }

    public function rubricAnswer()
    {
    return $this->belongsTo(\App\Models\RubricAnswer::class);
    }
   // Un evaluation pertenece a un artículo
    public function article()
    {
        return $this->belongsTo(Article::class);
    }

     public function reviewer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
