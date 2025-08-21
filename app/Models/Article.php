<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'articles';

    protected $fillable = [
        'article_folio',
        'title',
        'abstract',
        'key_works',
        'comment',
        'postulant_id',
        'editor_id',
        'knowledge_area_id',
        'article_status_id',
        'call_id',
        'payment_voucher_id',
        'article_type_id',
        'identifier', // si tienes columna para identificador único
    ];

    // Carga automática de relaciones frecuentes
    protected $with = ['articleStatus', 'authors', 'file'];

    // Casts para fechas y JSON
    protected $casts = [
        'key_works' => 'array',
        'created_at' => 'datetime:d-m-Y H:i',
        'updated_at' => 'datetime:d-m-Y H:i',
    ];

    /* =======================
       RELACIONES
    ======================= */

    public function postulant()
    {
        return $this->belongsTo(User::class, 'postulant_id');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function knowledgeArea()
    {
        return $this->belongsTo(KnowledgeArea::class);
    }

    public function articleStatus()
    {
        return $this->belongsTo(ArticleStatus::class);
    }

    // Alias para status
    public function status()
    {
        return $this->articleStatus();
    }

    public function call()
    {
        return $this->belongsTo(Call::class);
    }

    public function authors()
    {
        return $this->hasMany(Author::class);
    }

    public function file()
    {
        return $this->morphOne(File::class, 'fileable');
    }

    public function articleReviews()
    {
        return $this->hasMany(ArticleReview::class);
    }

    public function paymentVoucher()
    {
        return $this->belongsTo(PaymentVoucher::class);
    }

    public function articleType()
    {
        return $this->belongsTo(ArticleType::class);
    }

    public function evaluations()
    {
        return $this->hasMany(\App\Models\ArticleEvaluation::class, 'article_id')
            ->with(['rubricItem', 'rubricAnswer']);
    }

    /* =======================
       SCOPES
    ======================= */

    /**
     * Scope para buscar artículos por folio, título, autor o identificador
     */
    public function scopeSearch($query, $value, $type)
{
    switch ($type) {
        case 'code':
            $query->where('article_folio', $value);
            break;
        case 'title':
            $query->where('title', 'like', "%{$value}%");
            break;
        case 'author':
            $query->whereHas('authors', fn($q) =>
                $q->where('name', 'like', "%{$value}%")
            );
            break;
        case 'identifier':
            // Búsqueda parcial al inicio del identificador
            $query->where('identifier', 'like', "{$value}%");
            break;
        default:
            $query->where('article_folio', $value);
            break;
    }

    return $query;
}

}
