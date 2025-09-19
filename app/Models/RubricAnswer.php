<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RubricAnswer extends Model
{
    protected $fillable = ['rubric_item_id', 'text', 'score'];
    protected $casts = [
        'score' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(RubricItem::class, 'rubric_item_id');
    }
      public function evaluations()
    {
        return $this->hasMany(ArticleEvaluation::class);
    }
}
