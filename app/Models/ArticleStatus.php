<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArticleStatus extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'article_statuses';

    protected $fillable = [
        'name',
        'description',
        'class',
        'is_evaluation',
        'can_edit',
    ];

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
