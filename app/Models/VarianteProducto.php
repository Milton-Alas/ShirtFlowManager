<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class VarianteProducto extends Model
{
    protected $table = 'variante_productos';

    protected $fillable = [
        'producto_id',
        'talla_id',
        'color_id',
        'precio',
        'stock_docenas',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function talla()
    {
        return $this->belongsTo(Talla::class);
    }



    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    //para validar que solo se pueda crear una variante de producto si el producto tiene tallas y colores activos
    public function save(array $options = [])
    {
        try {
            return parent::save($options);
        } catch (QueryException $e) {
            // Verifica si es una violación de unicidad (código 23505 en PostgreSQL o 1062 en MySQL)
            if ($e->getCode() == 23505 || $e->getCode() == 1062) {
                throw ValidationException::withMessages([
                    'unique' => 'Ya existe una variante con esta combinación de producto, talla y color.'
                ]);
            }
            throw $e;
        }

    }

}
