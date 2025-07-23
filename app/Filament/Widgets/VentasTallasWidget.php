<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Venta;
use App\Models\VarianteProducto;
use App\Models\Talla;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VentasTallasWidget extends ChartWidget
{
    protected static ?string $heading = 'Ventas por Talla (Docenas)';
    
    protected static ?int $sort = 5;
    
    protected static string $color = 'info';
    
    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        // Get recent sales to process items
        $ventas = Venta::where('created_at', '>=', Carbon::now()->subMonths(3))
            ->get();
        
        // Collect all variante_producto_ids from all sales items
        $varianteProductoIds = [];
        $cantidadesPorVariante = [];
        
        foreach ($ventas as $venta) {
            // items is cast to array in the Venta model
            $items = $venta->items ?? [];
            
            foreach ($items as $item) {
                $varianteProductoId = $item['variante_producto_id'] ?? null;
                $cantidad = $item['cantidad'] ?? 0;
                
                if ($varianteProductoId) {
                    $varianteProductoIds[] = $varianteProductoId;
                    
                    if (!isset($cantidadesPorVariante[$varianteProductoId])) {
                        $cantidadesPorVariante[$varianteProductoId] = 0;
                    }
                    $cantidadesPorVariante[$varianteProductoId] += $cantidad;
                }
            }
        }
        
        // Get unique variante producto IDs
        $varianteProductoIds = array_unique($varianteProductoIds);
        
        if (empty($varianteProductoIds)) {
            return [
                'datasets' => [
                    [
                        'label' => 'Docenas Vendidas',
                        'data' => [],
                        'backgroundColor' => [],
                        'borderColor' => [],
                        'borderWidth' => 2,
                    ],
                ],
                'labels' => [],
            ];
        }
        
        // Get variante productos with their tallas
        $varianteProductos = VarianteProducto::whereIn('id', $varianteProductoIds)
            ->with('talla')
            ->get()
            ->keyBy('id');
        
        // Aggregate quantities by talla
        $cantidadesPorTalla = [];
        
        foreach ($cantidadesPorVariante as $varianteProductoId => $cantidad) {
            $varianteProducto = $varianteProductos->get($varianteProductoId);
            
            if ($varianteProducto && $varianteProducto->talla) {
                $tallaNombre = $varianteProducto->talla->nombre;
                
                if (!isset($cantidadesPorTalla[$tallaNombre])) {
                    $cantidadesPorTalla[$tallaNombre] = 0;
                }
                $cantidadesPorTalla[$tallaNombre] += $cantidad;
            }
        }
        
        // Sort by talla name
        ksort($cantidadesPorTalla);
        
        $labels = [];
        $data = [];
        
        foreach ($cantidadesPorTalla as $talla => $totalCantidad) {
            $labels[] = 'Talla ' . $talla;
            // Convert units to dozens (12 units = 1 dozen)
            $docenas = round($totalCantidad / 12, 2);
            $data[] = $docenas;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Docenas Vendidas',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.7)',   // Blue
                        'rgba(16, 185, 129, 0.7)',   // Green
                        'rgba(245, 158, 11, 0.7)',   // Yellow
                        'rgba(239, 68, 68, 0.7)',    // Red
                        'rgba(139, 92, 246, 0.7)',   // Purple
                        'rgba(236, 72, 153, 0.7)',   // Pink
                        'rgba(14, 165, 233, 0.7)',   // Sky
                        'rgba(34, 197, 94, 0.7)',    // Emerald
                    ],
                    'borderColor' => [
                        'rgba(59, 130, 246, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(139, 92, 246, 1)',
                        'rgba(236, 72, 153, 1)',
                        'rgba(14, 165, 233, 1)',
                        'rgba(34, 197, 94, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut'; //aqui puedo cambiar el tipo de gráfico
    }
    
    
    protected function getOptions(): array
    {
        return [
        'plugins' => [
            'legend' => [
                'display' => true,
                'position' => 'top',
            ],
        ],
        // No se necesita la sección 'scales' para gráficos de tipo 'pie'
        'responsive' => true,
        'maintainAspectRatio' => false,
    ];
    }
}
