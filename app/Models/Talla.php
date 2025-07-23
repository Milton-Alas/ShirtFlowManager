<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Talla extends Model
{
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($talla) {
            $maxOrden = static::max('orden') ?? 0;
            $talla->orden = $maxOrden + 1;
        });
    }

    protected $table = 'tallas';

    protected $fillable = [
        'nombre',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    // Relación con VarianteProducto
    public function varianteProductos()
    {
        return $this->hasMany(VarianteProducto::class);
    }

    // Relación con Productos a través de VarianteProducto
    public function productos()
    {
        return $this->hasManyThrough(Producto::class, VarianteProducto::class, 'talla_id', 'id', 'id', 'producto_id');
    }
}
