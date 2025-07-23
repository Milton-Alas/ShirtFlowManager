<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VentasWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 1;
    protected static ?string $heading = 'Ventas Últimos 7 Días';

    protected function getData(): array
    {
        // Get sales data for the last 7 days
        $salesData = Venta::select(
            DB::raw('DATE(fecha_venta) as date'),
            DB::raw('SUM(total) as total_sales')
        )
        ->where('fecha_venta', '>=', Carbon::now()->subDays(6)->startOfDay())
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // Create arrays for the last 7 days
        $labels = [];
        $data = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateString = $date->format('Y-m-d');
            $labels[] = $date->format('d/m');
            
            // Find sales for this date
            $dailySales = $salesData->firstWhere('date', $dateString);
            $data[] = $dailySales ? (float) $dailySales->total_sales : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ventas ($)',
                    'data' => $data,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "$" + value.toLocaleString(); }',
                    ],
                ],
            ],
        ];
    }
}
