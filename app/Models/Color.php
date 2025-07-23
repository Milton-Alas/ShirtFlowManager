<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $table = 'colores';

    protected $fillable = [
        'nombre',
        'codigo_hex',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public $timestamps = true;

    /**
     * Get the color's name.
     */
    public function getNombreAttribute($value)
    {
        return ucfirst($value);
    }
}
