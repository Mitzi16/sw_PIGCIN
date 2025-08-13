<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArticleType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];


    public function rubrics()
    {
        return $this->hasMany(Rubric::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }


    protected static function booted()
    {
        static::deleting(function ($articleType) {
            // Borrar rúbricas, items y respuestas relacionados
            foreach ($articleType->rubrics as $rubric) {
                $rubric->items()->each(function ($item) {
                    $item->answers()->delete();
                    $item->delete();
                });
                $rubric->delete();
            }
        });
    }
}
