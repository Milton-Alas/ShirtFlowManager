<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    protected $fillable = [
        'categoria_gasto_id',
        'descripcion',
        'monto',
        'fecha',
        'nota',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaGasto::class, 'categoria_gasto_id');
    }

    public function getFormattedMontoAttribute()
    {
        return number_format($this->monto, 2, '.', ',');
    }
}
