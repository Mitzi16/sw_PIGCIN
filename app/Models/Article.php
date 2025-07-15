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
        'title',
        'type',
        'abstract',
        'key_works',
        'comment',
        'postulant_id', // postulante
        'editor_id', // editor
        'knowledge_area_id',
        'article_status_id',
        'call_id',
        'payment_voucher_id',
    ];

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
}
