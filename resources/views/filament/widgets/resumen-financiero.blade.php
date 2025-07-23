<x-filament-widgets::widget>
    <x-filament::section>
        @php
            $data = $this->getViewData();
        @endphp
        
        {{-- CSS minimalista --}}
        <style>
            .widget-card {
                transition: all 0.2s ease;
            }
            
            .widget-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }
            
            /* Dark mode hover shadow */
            .dark .widget-card:hover {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            }
        </style>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            {{-- Ventas --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-green-100 rounded flex items-center justify-center">
                            <x-heroicon-o-arrow-trending-up class="w-4 h-4 text-green-600" />
                        </div>
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Ventas</span>
                    </div>
                    @if(!$data['ventasCambio']['esNeutral'])
                        <span class="text-xs px-2 py-1 rounded-full {{ $data['ventasCambio']['esPositivo'] ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' }}">
                            {{ $data['ventasCambio']['esPositivo'] ? '+' : '' }}{{ $data['ventasCambio']['porcentaje'] }}%
                        </span>
                    @endif
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    ${{ number_format($data['ventasDelMes'], 0) }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $data['mesActual'] }}</div>
            </div>

            {{-- Gastos --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded flex items-center justify-center">
                            <x-heroicon-o-arrow-trending-down class="w-4 h-4 text-red-600 dark:text-red-400" />
                        </div>
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Gastos</span>
                    </div>
                    @if(!$data['gastosCambio']['esNeutral'])
                        <span class="text-xs px-2 py-1 rounded-full {{ $data['gastosCambio']['esPositivo'] ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' : 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' }}">
                            {{ $data['gastosCambio']['esPositivo'] ? '+' : '-' }}{{ $data['gastosCambio']['porcentaje'] }}%
                        </span>
                    @endif
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    ${{ number_format($data['gastosDelMes'], 0) }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $data['mesActual'] }}</div>
            </div>

            {{-- Balance --}}
            @php
                $balance = $data['balance'];
                $isPositive = $balance >= 0;
            @endphp
            
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 {{ $isPositive ? 'bg-blue-100 dark:bg-blue-900' : 'bg-orange-100 dark:bg-orange-900' }} rounded flex items-center justify-center">
                            @if($isPositive)
                                <x-heroicon-o-banknotes class="w-4 h-4 {{ $isPositive ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400' }}" />
                            @else
                                <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-orange-600 dark:text-orange-400" />
                            @endif
                        </div>
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Balance</span>
                    </div>
                    
                    <span class="text-xs px-2 py-1 rounded-full {{ $isPositive ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' }}">
                        {{ $isPositive ? 'Ganancia' : 'Pérdida' }}
                    </span>
                </div>
                
                <div class="text-2xl font-bold {{ $isPositive ? 'text-gray-900 dark:text-white' : 'text-red-600 dark:text-red-400' }}">
                    @if($balance < 0)
                        -${{ number_format(abs($balance), 0) }}
                    @else
                        ${{ number_format($balance, 0) }}
                    @endif
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $data['mesActual'] }}</div>
            </div>
                
            </div> {{-- Fin del grid --}}
            
        </div> {{-- Fin del contenedor principal --}}
    </x-filament::section>
</x-filament-widgets::widget>
