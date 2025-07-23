<?php
// app/Filament/Resources/ProductoResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Models\Producto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Catálogo de Productos';
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Gestión de Productos';
    protected static ?string $modelLabel = 'producto';
    protected static ?string $pluralModelLabel = 'Productos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Producto')
                    ->description('Configure los datos básicos y características del producto')
                    ->icon('heroicon-o-cube')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre del Producto')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Ej: Camiseta Básica Algodón')
                            ->prefixIcon('heroicon-m-cube')
                            ->live(onBlur: true)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción del Producto')
                            ->maxLength(500)
                            ->rows(4)
                            ->placeholder('Descripción detallada del producto, materiales, características especiales, etc.')
                            ->autosize()
                            ->extraInputAttributes(['style' => 'resize: none'])
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('activo')
                                    ->label('Producto Activo')
                                    ->helperText('Los productos inactivos no aparecerán en el catálogo')
                                    ->default(true)
                                    ->inline(false)
                                    ->columnSpan(1),

                                Forms\Components\DateTimePicker::make('created_at')
                                    ->label('Fecha de Creación')
                                    ->default(now())
                                    ->displayFormat('d/m/Y H:i')
                                    ->native(false)
                                    ->prefixIcon('heroicon-m-calendar')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre del Producto')
                    ->sortable()
                    ->searchable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-m-cube')
                    ->color(Color::Blue)
                    ->copyable()
                    ->copyMessage('Nombre copiado'),

                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->wrap()
                    ->limit(60)
                    ->placeholder('Sin descripción')
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (!$state || strlen($state) <= 60) {
                            return null;
                        }
                        return $state;
                    })
                    ->icon('heroicon-m-document-text')
                    ->color(Color::Gray),

                Tables\Columns\IconColumn::make('activo')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->alignment('center')
                    ->tooltip(fn ($record) => $record->activo ? 'Producto Activo' : 'Producto Inactivo'),

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
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Filtrar por Estado')
                    ->placeholder('Todos los productos')
                    ->trueLabel('Solo productos activos')
                    ->falseLabel('Solo productos inactivos')
                    ->native(false),

                Tables\Filters\Filter::make('productos_recientes')
                    ->label('Productos Recientes (últimos 30 días)')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(30)))
                    ->toggle(),

                Tables\Filters\Filter::make('sin_descripcion')
                    ->label('Sin Descripción')
                    ->query(fn (Builder $query): Builder => $query->whereNull('descripcion')->orWhere('descripcion', ''))
                    ->toggle(),
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
                    Tables\Actions\BulkAction::make('activar')
                        ->label('Activar Seleccionados')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['activo' => true]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Activar productos seleccionados')
                        ->modalDescription('¿Estás seguro de que deseas activar estos productos?')
                        ->modalSubmitActionLabel('Sí, activar'),

                    Tables\Actions\BulkAction::make('desactivar')
                        ->label('Desactivar Seleccionados')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each->update(['activo' => false]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Desactivar productos seleccionados')
                        ->modalDescription('¿Estás seguro de que deseas desactivar estos productos? No aparecerán en el catálogo.')
                        ->modalSubmitActionLabel('Sí, desactivar'),

                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar productos seleccionados')
                        ->modalDescription('¿Estás seguro de que deseas eliminar estos productos? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar'),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Crear Primer Producto')
                    ->icon('heroicon-o-plus'),
            ])
            ->groups([
                Tables\Grouping\Group::make('activo')
                    ->label('Agrupar por Estado')
                    ->getTitleFromRecordUsing(fn ($record) => $record->activo ? 'Productos Activos' : 'Productos Inactivos')
                    ->collapsible(),
                Tables\Grouping\Group::make('created_at')
                    ->label('Agrupar por Fecha de Creación')
                    ->date()
                    ->collapsible(),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('60s')
            ->deferLoading();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'edit' => Pages\EditProducto::route('/{record}/edit'),
        ];
    }

    // Muestra la cantidad de productos en el menú de navegación
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    // Color dinámico del badge según la cantidad y estado de productos
    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();
        $activeCount = static::getModel()::where('activo', true)->count();
        $inactiveCount = $count - $activeCount;
        
        if ($inactiveCount > ($count * 0.3)) return 'warning'; // Si más del 30% están inactivos
        if ($count > 100) return 'success';
        if ($count > 50) return 'primary';
        return 'gray';
    }
}