<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rubric extends Model
{

     protected $fillable = ['article_type_id', 'title'];

    public function articleType()
    {
        return $this->belongsTo(ArticleType::class);
    }

    public function items()
    {
        return $this->hasMany(RubricItem::class);
    }
}
