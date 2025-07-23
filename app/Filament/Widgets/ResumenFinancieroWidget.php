<?php

namespace App\Filament\Widgets;

use App\Models\Venta;
use App\Models\Gasto;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class ResumenFinancieroWidget extends Widget
{
    protected static string $view = 'filament.widgets.resumen-financiero';
    
    protected static ?string $heading = 'Resumen Financiero';
    
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 2;
    
    // Cache para evitar consultas repetidas
    protected array $dataCache = [];
    
    public function getViewData(): array
    {
        if (empty($this->dataCache)) {
            $this->loadFinancialData();
        }
        
        return $this->dataCache;
    }
    
    protected function loadFinancialData(): void
    {
        $currentMonth = now();
        $previousMonth = now()->copy()->subMonth();
        
        // Datos del mes actual
        $ventasDelMes = $this->getVentasDelMes($currentMonth);
        $gastosDelMes = $this->getGastosDelMes($currentMonth);
        $balance = $ventasDelMes - $gastosDelMes;
        
        // Datos del mes anterior para comparación
        $ventasMesAnterior = $this->getVentasDelMes($previousMonth);
        $gastosMesAnterior = $this->getGastosDelMes($previousMonth);
        $balanceMesAnterior = $ventasMesAnterior - $gastosMesAnterior;
        
        // Calcular porcentajes de cambio
        $ventasCambio = $this->calcularPorcentajeCambio($ventasMesAnterior, $ventasDelMes);
        $gastosCambio = $this->calcularPorcentajeCambio($gastosMesAnterior, $gastosDelMes);
        $balanceCambio = $this->calcularPorcentajeCambio($balanceMesAnterior, $balance);
        
        // Datos de tendencia (últimos 6 meses)
        $tendenciaVentas = $this->getTendenciaVentas();
        $tendenciaGastos = $this->getTendenciaGastos();
        $tendenciaBalance = $this->getTendenciaBalance($tendenciaVentas, $tendenciaGastos);
        
        $this->dataCache = [
            // Datos actuales
            'ventasDelMes' => $ventasDelMes,
            'gastosDelMes' => $gastosDelMes,
            'balance' => $balance,
            
            // Cambios porcentuales
            'ventasCambio' => $ventasCambio,
            'gastosCambio' => $gastosCambio,
            'balanceCambio' => $balanceCambio,
            
            // Datos de tendencia
            'tendenciaVentas' => $tendenciaVentas,
            'tendenciaGastos' => $tendenciaGastos,
            'tendenciaBalance' => $tendenciaBalance,
            
            // Meta información
            'mesActual' => $currentMonth->format('F Y'),
            'mesAnterior' => $previousMonth->format('F Y'),
        ];
    }
    
    private function getVentasDelMes(Carbon $fecha): float
    {
        return Venta::whereMonth('fecha_venta', $fecha->month)
            ->whereYear('fecha_venta', $fecha->year)
            ->sum('total') ?? 0;
    }
    
    private function getGastosDelMes(Carbon $fecha): float
    {
        return Gasto::whereMonth('fecha', $fecha->month)
            ->whereYear('fecha', $fecha->year)
            ->sum('monto') ?? 0;
    }
    
    private function calcularPorcentajeCambio(float $valorAnterior, float $valorActual): array
    {
        if ($valorAnterior == 0) {
            return [
                'porcentaje' => $valorActual > 0 ? 100 : 0,
                'esPositivo' => $valorActual >= 0,
                'esNeutral' => $valorActual == 0,
            ];
        }
        
        $cambio = (($valorActual - $valorAnterior) / abs($valorAnterior)) * 100;
        
        return [
            'porcentaje' => round(abs($cambio), 1),
            'esPositivo' => $cambio >= 0,
            'esNeutral' => abs($cambio) < 0.1,
        ];
    }
    
    private function getTendenciaVentas(): Collection
    {
        return collect(range(5, 0))->map(function ($monthsBack) {
            $fecha = now()->copy()->subMonths($monthsBack);
            return [
                'mes' => $fecha->format('M'),
                'valor' => $this->getVentasDelMes($fecha),
                'fecha_completa' => $fecha->format('Y-m'),
            ];
        });
    }
    
    private function getTendenciaGastos(): Collection
    {
        return collect(range(5, 0))->map(function ($monthsBack) {
            $fecha = now()->copy()->subMonths($monthsBack);
            return [
                'mes' => $fecha->format('M'),
                'valor' => $this->getGastosDelMes($fecha),
                'fecha_completa' => $fecha->format('Y-m'),
            ];
        });
    }
    
    private function getTendenciaBalance(Collection $ventas, Collection $gastos): Collection
    {
        return $ventas->map(function ($venta, $index) use ($gastos) {
            $gasto = $gastos->get($index);
            return [
                'mes' => $venta['mes'],
                'valor' => $venta['valor'] - $gasto['valor'],
                'fecha_completa' => $venta['fecha_completa'],
            ];
        });
    }
}
