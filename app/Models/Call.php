<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Expr\Cast\Bool_;

class Call extends Model
{
    use HasFactory;

    protected $table = 'calls';

    protected $fillable = ['title', 'description', 'start_date', 'end_date', 
        'url', 'status', 'institution_id'
    ];

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function transform()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'url' => $this->url,
            'image' => $this->files->first(function ($file) {
                return str_starts_with($file->mime_type, 'image/');
            }),
            'file' => $this->files->firstWhere('mime_type', 'application/pdf'),
            'institution_id' => $this->institution_id,
            'institution' =>  $this->institution ? $this->institution->only('id', 'name') : null
        ];
    }
}
