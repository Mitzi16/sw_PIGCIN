<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Expr\Cast\Bool_;

class Call extends Model
{
    use HasFactory;

    protected $table = 'calls';

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'url',
        'status',
        'institution_id',

        // Nuevos campos
        'publication_start',
        'publication_end',
        'registration_start',
        'registration_end',
        'application_start',
        'application_end',
        'evaluation_start',
        'evaluation_end',
        'results_start',
        'results_end',
        'institution_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'publication_start' => 'date',
        'publication_end' => 'date',
        'registration_start' => 'date',
        'registration_end' => 'date',
        'application_start' => 'date',
        'application_end' => 'date',
        'evaluation_start' => 'date',
        'evaluation_end' => 'date',
        'results_start' => 'date',
        'results_end' => 'date',
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

    // En app/Models/Call.php
    public function transform()
    {
        // Buscamos cualquier archivo que contenga 'pdf' en su mime_type
        $pdf = $this->files->first(function ($file) {
            return str_contains($file->mime_type, 'pdf');
        });

        // Buscamos cualquier archivo que contenga 'image' en su mime_type
        $image = $this->files->first(function ($file) {
            return str_contains($file->mime_type, 'image');
        });

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date ? \Carbon\Carbon::parse($this->start_date)->format('Y-m-d') : null,
            'end_date' => $this->end_date ? \Carbon\Carbon::parse($this->end_date)->format('Y-m-d') : null,
            'status' => $this->status,
            'url' => $this->url,
            'type' => 'CONVOCATORIA', // O el tipo que uses

            // Enviamos los objetos completos
            'file' => $pdf ? [
                'id' => $pdf->id,
                'name' => $pdf->name,
                'path' => $pdf->path,
                'size' => $pdf->size,
            ] : null,

            'image' => $image ? [
                'id' => $image->id,
                'name' => $image->name,
                'path' => $image->path,
            ] : null,
        ];
    }
}
