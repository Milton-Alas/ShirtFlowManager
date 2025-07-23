<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VentaResource\Pages;
use App\Models\Venta;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Repeater;
use App\Models\VarianteProducto;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;

class VentaResource extends Resource
{
    protected static ?string $model = Venta::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Ventas y Clientes';
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Gestión de Ventas';
    protected static ?string $modelLabel = 'venta';
    protected static ?string $pluralModelLabel = 'Ventas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información de la Venta')
                    ->description('Complete los datos básicos de la transacción')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('cliente_id')
                                    ->label('Cliente')
                                    ->relationship('cliente', 'nombre')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->placeholder('Seleccione un cliente')
                                    ->prefixIcon('heroicon-m-user')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('numero_venta')
                                    ->label('N° de Venta')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->default(fn() => Venta::generarNumeroVenta())
                                    ->prefixIcon('heroicon-m-hashtag')
                                    ->placeholder('Generado automáticamente')
                                    ->columnSpan(1),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('fecha_venta')
                                    ->label('Fecha de Venta')
                                    ->required()
                                    ->default(now())
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->prefixIcon('heroicon-m-calendar')
                                    ->closeOnDateSelection()
                                    ->columnSpan(1),

                                Forms\Components\Select::make('metodo_pago')
                                    ->label('Método de Pago')
                                    ->options([
                                        'efectivo' => 'Efectivo',
                                        'transferencia' => 'Transferencia'
                                    ])
                                    ->required()
                                    ->default('efectivo')
                                    ->native(false)
                                    ->prefixIcon('heroicon-m-credit-card')
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columns(2),

                Section::make('Productos de la Venta')
                    ->description('Agregue los productos y configure las cantidades')
                    ->icon('heroicon-o-shopping-bag')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->schema([
                                Forms\Components\Select::make('variante_producto_id')
                                    ->label('Producto')
                                    ->placeholder('Seleccione un producto')
                                    ->options(function () {
                                        return VarianteProducto::with(['producto', 'color', 'talla'])
                                            ->get()
                                            ->mapWithKeys(function ($variante) {
                                                return [
                                                    $variante->id => "{$variante->producto->nombre} - {$variante->color->nombre} - {$variante->talla->nombre}"
                                                ];
                                            });
                                    })
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $set, $state) => $set('precio', VarianteProducto::find($state)?->precio ?? 0))
                                    ->searchable()
                                    ->required()
                                    ->native(false)
                                    ->prefixIcon('heroicon-m-cube')
                                    ->columnSpan(2),

                                Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('cantidad')
                                            ->label('Cantidad')
                                            ->numeric()
                                            ->reactive()
                                            ->required()
                                            ->default(1)
                                            ->minValue(1)
                                            ->prefixIcon('heroicon-m-calculator')
                                            ->afterStateUpdated(function (callable $set, $get) {
                                                $precio = (float) ($get('precio') ?? 0);
                                                $cantidad = (float) ($get('cantidad') ?? 0);
                                                $descuento = (float) ($get('descuento_item') ?? 0);
                                                $set('subtotal_item', ($precio * $cantidad) - $descuento);
                                                
                                                // Actualizar totales generales
                                                self::updateTotals($set, $get);
                                            })
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('precio')
                                            ->label('Precio Unitario')
                                            ->numeric()
                                            ->prefix('$')
                                            ->suffix('USD')
                                            ->reactive()
                                            ->required()
                                            ->prefixIcon('heroicon-m-currency-dollar')
                                            ->afterStateUpdated(function (callable $set, $get) {
                                                $precio = (float) ($get('precio') ?? 0);
                                                $cantidad = (float) ($get('cantidad') ?? 0);
                                                $descuento = (float) ($get('descuento_item') ?? 0);
                                                $set('subtotal_item', ($precio * $cantidad) - $descuento);
                                                
                                                // Actualizar totales generales
                                                self::updateTotals($set, $get);
                                            })
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('descuento_item')
                                            ->label('Descuento')
                                            ->numeric()
                                            ->prefix('$')
                                            ->suffix('USD')
                                            ->reactive()
                                            ->default(0)
                                            ->prefixIcon('heroicon-m-receipt-percent')
                                            ->afterStateUpdated(function (callable $set, $get) {
                                                $precio = (float) ($get('precio') ?? 0);
                                                $cantidad = (float) ($get('cantidad') ?? 0);
                                                $descuento = (float) ($get('descuento_item') ?? 0);
                                                $set('subtotal_item', ($precio * $cantidad) - $descuento);
                                                
                                                // Actualizar totales generales
                                                self::updateTotals($set, $get);
                                            })
                                            ->columnSpan(1),
                                    ]),

                                Forms\Components\TextInput::make('subtotal_item')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('$')
                                    ->suffix('USD')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->prefixIcon('heroicon-m-calculator')
                                    ->extraAttributes(['class' => 'font-bold text-primary-600'])
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->addActionLabel('➕ Agregar Producto')
                            ->defaultItems(1)
                            ->collapsible()
                            ->cloneable()
                            ->reorderable()
                            ->itemLabel(fn (array $state): ?string => 
                                $state['variante_producto_id'] 
                                    ? VarianteProducto::find($state['variante_producto_id'])?->producto->nombre ?? 'Producto'
                                    : 'Nuevo Producto'
                            )
                            ->collapsed(false)
                            ->deleteAction(
                                fn ($action) => $action
                                    ->requiresConfirmation()
                                    ->color('danger')
                            ),
                    ]),

                Section::make('Resumen de Totales')
                    ->description('Totales calculados automáticamente')
                    ->icon('heroicon-o-calculator')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('$')
                                    ->suffix('USD')
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefixIcon('heroicon-m-currency-dollar')
                                    ->extraAttributes(['class' => 'font-semibold']),

                                Forms\Components\TextInput::make('descuento')
                                    ->label('Descuento Total')
                                    ->numeric()
                                    ->prefix('$')
                                    ->suffix('USD')
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefixIcon('heroicon-m-receipt-percent')
                                    ->extraAttributes(['class' => 'font-semibold text-warning-600']),

                                Forms\Components\TextInput::make('total')
                                    ->label('Total Final')
                                    ->numeric()
                                    ->prefix('$')
                                    ->suffix('USD')
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefixIcon('heroicon-m-banknotes')
                                    ->extraAttributes(['class' => 'font-bold text-success-600 text-lg']),
                            ]),

                        Forms\Components\Textarea::make('notas')
                            ->label('Notas Adicionales')
                            ->nullable()
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->rows(3)
                            ->placeholder('Observaciones o detalles especiales de esta venta')
                            ->autosize()
                            ->extraInputAttributes(['style' => 'resize: none']),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('fecha_venta', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('numero_venta')
                    ->label('N° Venta')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-hashtag')
                    ->color(Color::Blue)
                    ->weight(FontWeight::Medium)
                    ->copyable()
                    ->copyMessage('Número de venta copiado'),

                Tables\Columns\TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-m-user')
                    ->color(Color::Green)
                    ->weight(FontWeight::Medium)
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('fecha_venta')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-m-calendar')
                    ->color(Color::Purple)
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->icon('heroicon-m-currency-dollar')
                    ->color(fn ($record) => match(true) {
                        $record->total > 1000 => 'success',
                        $record->total > 500 => 'warning',
                        default => 'primary'
                    })
                    ->copyable()
                    ->copyMessage('Total copiado'),

                Tables\Columns\TextColumn::make('metodo_pago')
                    ->label('Método de Pago')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'efectivo' => 'success',
                        'transferencia' => 'info',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'efectivo' => 'heroicon-m-banknotes',
                        'transferencia' => 'heroicon-m-credit-card',
                        default => 'heroicon-m-question-mark-circle',
                    }),

                Tables\Columns\TextColumn::make('notas')
                    ->label('Notas')
                    ->limit(25)
                    ->placeholder('Sin notas')
                    ->color(Color::Slate)
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Small)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (!$state || strlen($state) <= 25) {
                            return null;
                        }
                        return $state;
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cliente_id')
                    ->label('Filtrar por Cliente')
                    ->relationship('cliente', 'nombre')
                    ->searchable()
                    ->preload()
                    ->native(false),

                Tables\Filters\SelectFilter::make('metodo_pago')
                    ->label('Método de Pago')
                    ->options([
                        'efectivo' => 'Efectivo',
                        'transferencia' => 'Transferencia'
                    ])
                    ->native(false),

                Tables\Filters\Filter::make('ventas_altas')
                    ->label('Ventas Altas (>$500)')
                    ->query(fn (Builder $query): Builder => $query->where('total', '>', 500))
                    ->toggle(),

                Tables\Filters\Filter::make('fecha_venta')
                    ->form([
                        Forms\Components\DatePicker::make('desde')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('hasta')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_venta', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_venta', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('info'),
                    Tables\Actions\EditAction::make()
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make()
                        ->color('danger'),
                ])
                ->label('Acciones')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar ventas seleccionadas')
                        ->modalDescription('¿Estás seguro de que deseas eliminar estas ventas? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar'),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Registrar Primera Venta')
                    ->icon('heroicon-o-plus'),
            ])
            ->groups([
                Tables\Grouping\Group::make('fecha_venta')
                    ->label('Agrupar por Fecha')
                    ->date()
                    ->collapsible(),
                Tables\Grouping\Group::make('cliente.nombre')
                    ->label('Agrupar por Cliente')
                    ->collapsible(),
                Tables\Grouping\Group::make('metodo_pago')
                    ->label('Agrupar por Método de Pago')
                    ->collapsible(),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s')
            ->deferLoading();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVentas::route('/'),
            'create' => Pages\CreateVenta::route('/create'),
            'view' => Pages\ViewVenta::route('/{record}'),
            'edit' => Pages\EditVenta::route('/{record}/edit'),
        ];
    }

    // Muestra la cantidad de ventas en el menú de navegación
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    // Color dinámico del badge según la cantidad de ventas
    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();
        if ($count > 100) return 'success';
        if ($count > 50) return 'warning';
        return 'primary';
    }

    // Método para actualizar los totales (mantiene la funcionalidad original)
    protected static function updateTotals(callable $set, callable $get): void
    {
        $items = $get('../../items') ?? [];
        
        $subtotal = 0;
        $descuentoTotal = 0;
        
        foreach ($items as $item) {
            $precio = (float) ($item['precio'] ?? 0);
            $cantidad = (float) ($item['cantidad'] ?? 0);
            $descuentoItem = (float) ($item['descuento_item'] ?? 0);
            
            $subtotal += ($precio * $cantidad);
            $descuentoTotal += $descuentoItem;
        }
        
        $total = $subtotal - $descuentoTotal;
        
        $set('../../subtotal', $subtotal);
        $set('../../descuento', $descuentoTotal);
        $set('../../total', $total);
    }
}