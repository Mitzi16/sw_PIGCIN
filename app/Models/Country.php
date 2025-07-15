<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'countries';

    protected $fillable = [
        'name',
        'cod_num',
        'cod_alf3',
        'cod_alf2',
    ];

    public function states()
    {
        return $this->hasMany(State::class);
    }

    public function institutions()
    {
        return $this->hasMany(Institution::class);
    }
}
