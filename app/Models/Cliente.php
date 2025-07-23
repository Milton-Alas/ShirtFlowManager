<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    //
    protected $fillable = [
        'nombre',
        'telefono',
        'direccion',
        'es_frecuente',
        'nota',
    ];
    protected $casts = [
        'es_frecuente' => 'boolean',
    ];
}
