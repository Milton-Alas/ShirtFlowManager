<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VarianteProductoResource\Pages;
use App\Models\VarianteProducto;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;

class VarianteProductoResource extends Resource
{
    protected static ?string $model = VarianteProducto::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Catálogo de Productos';
    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Gestión de Variantes';
    protected static ?string $modelLabel = 'variante';
    protected static ?string $pluralModelLabel = 'Variantes de Producto';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Configuración de Variante')
                    ->description('Complete los detalles específicos para cada variante de producto')
                    ->icon('heroicon-o-rectangle-stack')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Forms\Components\Select::make('producto_id')
                            ->label('Producto Base')
                            ->relationship('producto', 'nombre')
                            ->placeholder('Seleccione el producto principal')
                            ->required()
                            ->live()
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-m-cube')
                            ->native(false)
                            ->columnSpanFull()
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('producto_id', $state)),

                        Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('talla_id')
                                    ->label('Talla')
                                    ->placeholder('Seleccione una talla')
                                    ->relationship('talla', 'nombre', fn ($query) => $query->where('activo', true)->orderBy('orden'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->prefixIcon('heroicon-m-squares-2x2')
                                    ->native(false)
                                    ->live()
                                    ->columnSpan(1),

                                Forms\Components\Select::make('color_id')
                                    ->label('Color')
                                    ->relationship('color', 'nombre', fn ($query) => $query->where('activo', true))
                                    ->placeholder('Seleccione un color')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->prefixIcon('heroicon-m-swatch')
                                    ->native(false)
                                    ->live()
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('precio')
                                    ->label('Precio Unitario')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.01)
                                    ->step(0.01)
                                    ->placeholder('0.00')
                                    ->prefix('$')
                                    ->suffix('USD')
                                    ->prefixIcon('heroicon-m-currency-dollar')
                                    ->live(onBlur: true)
                                    ->columnSpan(1),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('stock_docenas')
                                    ->label('Stock en Docenas')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->minValue(0)
                                    ->step(1)
                                    ->prefixIcon('heroicon-m-archive-box')
                                    ->suffix('docenas')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        $unidades = ($state ?? 0) * 12;
                                        $set('stock_unidades_calculado', $unidades);
                                    })
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('stock_unidades_calculado')
                                    ->label('Stock en Unidades (Calculado)')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->default(0)
                                    ->prefixIcon('heroicon-m-cube-transparent')
                                    ->suffix('unidades')
                                    ->helperText('Se calcula automáticamente: docenas × 12')
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->placeholder('Notas adicionales sobre esta variante específica')
                            ->maxLength(500)
                            ->rows(3)
                            ->autosize()
                            ->extraInputAttributes(['style' => 'resize: none'])
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->sortable()
                    ->searchable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-m-cube')
                    ->color(Color::Blue)
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('talla.nombre')
                    ->label('Talla')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-m-squares-2x2')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('color.nombre')
                    ->label('Color')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-m-swatch')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('precio')
                    ->label('Precio')
                    ->sortable()
                    ->money('USD')
                    ->weight(FontWeight::Bold)
                    ->icon('heroicon-m-currency-dollar')
                    ->color(fn ($record) => match(true) {
                        $record->precio > 50 => 'success',
                        $record->precio > 20 => 'warning',
                        default => 'danger'
                    })
                    ->copyable()
                    ->copyMessage('Precio copiado'),

                Tables\Columns\TextColumn::make('stock_docenas')
                    ->label('Stock (Docenas)')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state == 0 => 'danger',
                        $state <= 5 => 'warning',
                        $state <= 20 => 'success',
                        default => 'info'
                    })
                    ->icon('heroicon-m-archive-box')
                    ->formatStateUsing(fn ($state) => $state . ' doc.')
                    ->alignCenter()
                    ->tooltip(fn ($record) => ($record->stock_docenas * 12) . ' unidades totales'),

                Tables\Columns\TextColumn::make('stock_unidades')
                    ->label('Stock (Unidades)')
                    ->state(fn ($record) => $record->stock_docenas * 12)
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state == 0 => 'danger',
                        $state <= 60 => 'warning',
                        $state <= 240 => 'success',
                        default => 'info'
                    })
                    ->icon('heroicon-m-cube-transparent')
                    ->formatStateUsing(fn ($state) => number_format($state) . ' uni.')
                    ->alignCenter()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("(stock_docenas * 12) {$direction}");
                    }),

                Tables\Columns\TextColumn::make('valor_inventario')
                    ->label('Valor Total')
                    ->state(fn ($record) => $record->stock_docenas * 12 * $record->precio)
                    ->money('USD')
                    ->color(Color::Emerald)
                    ->icon('heroicon-m-banknotes')
                    ->weight(FontWeight::Bold)
                    ->tooltip('Valor total del inventario (unidades × precio)')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("(stock_docenas * 12 * precio) {$direction}");
                    }),

                Tables\Columns\TextColumn::make('observaciones')
                    ->label('Observaciones')
                    ->limit(25)
                    ->placeholder('Sin observaciones')
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

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-m-calendar')
                    ->color(Color::Slate)
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Small)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Modificación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-m-pencil')
                    ->color(Color::Amber)
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Small)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('producto_id')
                    ->label('Filtrar por Producto')
                    ->relationship('producto', 'nombre')
                    ->searchable()
                    ->preload()
                    ->native(false),

                Tables\Filters\SelectFilter::make('talla_id')
                    ->label('Filtrar por Talla')
                    ->relationship('talla', 'nombre')
                    ->searchable()
                    ->preload()
                    ->native(false),

                Tables\Filters\SelectFilter::make('color_id')
                    ->label('Filtrar por Color')
                    ->relationship('color', 'nombre')
                    ->searchable()
                    ->preload()
                    ->native(false),

                Tables\Filters\Filter::make('sin_stock')
                    ->label('Sin Stock')
                    ->query(fn (Builder $query): Builder => $query->where('stock_docenas', 0))
                    ->toggle(),

                Tables\Filters\Filter::make('stock_bajo')
                    ->label('Stock Bajo (≤ 5 docenas)')
                    ->query(fn (Builder $query): Builder => $query->where('stock_docenas', '>', 0)->where('stock_docenas', '<=', 5))
                    ->toggle(),

                Tables\Filters\Filter::make('stock_alto')
                    ->label('Stock Alto (> 20 docenas)')
                    ->query(fn (Builder $query): Builder => $query->where('stock_docenas', '>', 20))
                    ->toggle(),

                Tables\Filters\Filter::make('precio_alto')
                    ->label('Precio Premium (> $50)')
                    ->query(fn (Builder $query): Builder => $query->where('precio', '>', 50))
                    ->toggle(),

                Tables\Filters\Filter::make('variantes_recientes')
                    ->label('Variantes Recientes (últimos 30 días)')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(30)))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('info'),
                    Tables\Actions\EditAction::make()
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make()
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar Variante')
                        ->modalDescription(fn ($record) => "¿Estás seguro de que deseas eliminar la variante {$record->producto->nombre} - {$record->talla->nombre} - {$record->color->nombre}?"),
                ])
                ->label('Acciones')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('actualizar_precio')
                        ->label('Actualizar Precios')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('warning')
                        ->form([
                            Forms\Components\Radio::make('tipo_actualizacion')
                                ->label('Tipo de Actualización')
                                ->options([
                                    'fijo' => 'Precio Fijo',
                                    'porcentaje' => 'Porcentaje de Aumento/Descuento',
                                ])
                                ->default('porcentaje')
                                ->live(),
                            Forms\Components\TextInput::make('precio_fijo')
                                ->label('Nuevo Precio')
                                ->numeric()
                                ->prefix('$')
                                ->visible(fn (Forms\Get $get) => $get('tipo_actualizacion') === 'fijo'),
                            Forms\Components\TextInput::make('porcentaje')
                                ->label('Porcentaje')
                                ->numeric()
                                ->suffix('%')
                                ->helperText('Positivo para aumento, negativo para descuento')
                                ->visible(fn (Forms\Get $get) => $get('tipo_actualizacion') === 'porcentaje'),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                if ($data['tipo_actualizacion'] === 'fijo') {
                                    $record->update(['precio' => $data['precio_fijo']]);
                                } else {
                                    $nuevoPrecio = $record->precio * (1 + $data['porcentaje'] / 100);
                                    $record->update(['precio' => $nuevoPrecio]);
                                }
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Actualizar precios de variantes seleccionadas')
                        ->modalSubmitActionLabel('Actualizar precios'),

                    Tables\Actions\BulkAction::make('actualizar_stock')
                        ->label('Actualizar Stock')
                        ->icon('heroicon-o-archive-box')
                        ->color('info')
                        ->form([
                            Forms\Components\Radio::make('tipo_stock')
                                ->label('Tipo de Actualización')
                                ->options([
                                    'establecer' => 'Establecer Cantidad Específica',
                                    'agregar' => 'Agregar al Stock Actual',
                                    'reducir' => 'Reducir del Stock Actual',
                                ])
                                ->default('agregar')
                                ->live(),
                            Forms\Components\TextInput::make('cantidad_docenas')
                                ->label('Cantidad (Docenas)')
                                ->numeric()
                                ->required()
                                ->minValue(0),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                switch ($data['tipo_stock']) {
                                    case 'establecer':
                                        $record->update(['stock_docenas' => $data['cantidad_docenas']]);
                                        break;
                                    case 'agregar':
                                        $record->update(['stock_docenas' => $record->stock_docenas + $data['cantidad_docenas']]);
                                        break;
                                    case 'reducir':
                                        $nuevoStock = max(0, $record->stock_docenas - $data['cantidad_docenas']);
                                        $record->update(['stock_docenas' => $nuevoStock]);
                                        break;
                                }
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Actualizar stock de variantes seleccionadas')
                        ->modalSubmitActionLabel('Actualizar stock'),

                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar variantes seleccionadas')
                        ->modalDescription('¿Estás seguro de que deseas eliminar estas variantes? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar'),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Crear Primera Variante')
                    ->icon('heroicon-o-plus'),
            ])
            ->groups([
                Tables\Grouping\Group::make('producto.nombre')
                    ->label('Agrupar por Producto')
                    ->collapsible(),
                Tables\Grouping\Group::make('stock_status')
                    ->label('Agrupar por Estado de Stock')
                    ->getTitleFromRecordUsing(fn ($record) => match(true) {
                        $record->stock_docenas == 0 => 'Sin Stock',
                        $record->stock_docenas <= 5 => 'Stock Bajo',
                        $record->stock_docenas <= 20 => 'Stock Normal',
                        default => 'Stock Alto'
                    })
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
            'index' => Pages\ListVarianteProductos::route('/'),
            'create' => Pages\CreateVarianteProducto::route('/create'),
            'edit' => Pages\EditVarianteProducto::route('/{record}/edit'),
        ];
    }

    // Muestra la cantidad de variantes en el menú de navegación
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    // Color dinámico del badge según el estado del inventario
    public static function getNavigationBadgeColor(): ?string
    {
        $sinStock = static::getModel()::where('stock_docenas', 0)->count();
        $stockBajo = static::getModel()::where('stock_docenas', '>', 0)->where('stock_docenas', '<=', 5)->count();
        $total = static::getModel()::count();
        
        if ($sinStock > ($total * 0.3)) return 'danger'; // Más del 30% sin stock
        if ($stockBajo > ($total * 0.2)) return 'warning'; // Más del 20% con stock bajo
        if ($total > 100) return 'success'; // Buen inventario
        if ($total > 50) return 'primary'; // Inventario moderado
        return 'gray'; // Inventario inicial
    }
}