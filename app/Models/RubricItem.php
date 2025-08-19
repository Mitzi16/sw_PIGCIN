<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class RubricItem extends Model
{
    protected $fillable = ['rubric_id', 'question'];

    public function rubric()
    {
        return $this->belongsTo(Rubric::class);
    }

    public function answers()
    {
        return $this->hasMany(RubricAnswer::class);
    }
    public function evaluations()
    {
        return $this->hasMany(ArticleEvaluation::class);
    }
}
