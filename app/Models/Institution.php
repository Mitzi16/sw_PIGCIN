<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'institutions';

    protected $fillable = [
        'name',
        'status',
        'state_id',
        'country_id', //mala practica
    ];

    public function calls()
    {
        return $this->hasMany(Call::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function authors()
    {
        return $this->hasMany(Author::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
