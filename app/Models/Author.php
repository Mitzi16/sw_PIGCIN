<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Author extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'authors';

    protected $fillable = [
        'prefix',
        'name',
        'surname',
        'email',
        'institution_id',
        'article_id',
    ];
    // relacion 1 a m
    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
