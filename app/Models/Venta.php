<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $fillable = [
        'cliente_id',
        'numero_venta',
        'fecha_venta',
        'items',
        'subtotal',
        'descuento',
        'total',
        'metodo_pago',
        'notas',
    ];

    protected $table = 'ventas';

    protected $casts = [
        'fecha_venta' => 'date',
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    //Funcion para generar el número de venta
    public static function generarNumeroVenta()
    {
        // Fecha actual en formato YYYYMMDD
        $fecha = now()->format('Ymd');

        // Prefijo completo con 'V-YYYYMMDD-'
        $prefijo = "V-$fecha-";

        // Buscar la última venta del día ordenada por el número correlativo
        $ultimo = static::where('numero_venta', 'like', "$prefijo%")
            ->orderBy('numero_venta', 'desc')
            ->first();

        // Si hay una venta previa, extraer el número (los últimos 4 dígitos); si no, empezar desde 1
        $numero = $ultimo
            ? (int) substr($ultimo->numero_venta, strlen($prefijo)) + 1
            : 1;

        // Formatear el número con ceros a la izquierda para que tenga 4 dígitos
        return $prefijo . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }



    // Relación con Cliente

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }


}
