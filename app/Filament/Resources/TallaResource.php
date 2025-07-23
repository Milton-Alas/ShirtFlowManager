<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TallaResource\Pages;
use App\Models\Talla;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;

class TallaResource extends Resource
{
    protected static ?string $model = Talla::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Catálogo de Productos';
    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Gestión de Tallas';
    protected static ?string $modelLabel = 'talla';
    protected static ?string $pluralModelLabel = 'Tallas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Configuración de Talla')
                    ->description('Configure las tallas disponibles para sus productos')
                    ->icon('heroicon-o-squares-2x2')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nombre')
                                    ->label('Nombre de la Talla')
                                    ->required()
                                    ->maxLength(10)
                                    ->placeholder('Ej: XS, S, M, L, XL, XXL, 38, 40')
                                    ->prefixIcon('heroicon-m-tag')
                                    ->live(onBlur: true)
                                    ->unique(ignoreRecord: true)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('orden')
                                    ->label('Orden de Visualización')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->step(1)
                                    ->placeholder('0')
                                    ->prefixIcon('heroicon-m-list-bullet')
                                    ->helperText('Orden en que aparecerá en las listas (menor número = primera posición)')
                                    ->columnSpan(1),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('activo')
                                    ->label('Talla Activa')
                                    ->helperText('Las tallas inactivas no aparecerán en las opciones de selección')
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
            ->defaultSort('orden', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre de la Talla')
                    ->sortable()
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-m-tag')
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Large)
                    ->copyable()
                    ->copyMessage('Talla copiada'),

                Tables\Columns\TextColumn::make('orden')
                    ->label('Orden')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($record) => match(true) {
                        $record->orden == 0 => 'gray',
                        $record->orden <= 5 => 'success',
                        $record->orden <= 10 => 'warning',
                        default => 'danger'
                    })
                    ->icon('heroicon-m-list-bullet')
                    ->tooltip('Posición en el ordenamiento'),

                Tables\Columns\IconColumn::make('activo')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->alignment('center')
                    ->tooltip(fn ($record) => $record->activo ? 'Talla Activa' : 'Talla Inactiva'),

                Tables\Columns\TextColumn::make('productos_count')
                    ->label('Productos')
                    ->counts('productos')
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state == 0 => 'gray',
                        $state <= 5 => 'warning',
                        default => 'success'
                    })
                    ->icon('heroicon-m-cube')
                    ->tooltip('Cantidad de productos que usan esta talla')
                    ->sortable(),

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
                    ->placeholder('Todas las tallas')
                    ->trueLabel('Solo tallas activas')
                    ->falseLabel('Solo tallas inactivas')
                    ->native(false),

                Tables\Filters\Filter::make('sin_productos')
                    ->label('Tallas sin Productos')
                    ->query(fn (Builder $query): Builder => $query->whereDoesntHave('productos'))
                    ->toggle(),

                Tables\Filters\Filter::make('orden_bajo')
                    ->label('Orden Prioritario (≤ 5)')
                    ->query(fn (Builder $query): Builder => $query->where('orden', '<=', 5))
                    ->toggle(),

                Tables\Filters\Filter::make('tallas_recientes')
                    ->label('Tallas Recientes (últimos 30 días)')
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
                        ->modalHeading('Eliminar Talla')
                        ->modalDescription(fn ($record) => "¿Estás seguro de que deseas eliminar la talla '{$record->nombre}'? Esta acción no se puede deshacer."),
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
                        ->label('Activar Seleccionadas')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['activo' => true]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Activar tallas seleccionadas')
                        ->modalDescription('¿Estás seguro de que deseas activar estas tallas?')
                        ->modalSubmitActionLabel('Sí, activar'),

                    Tables\Actions\BulkAction::make('desactivar')
                        ->label('Desactivar Seleccionadas')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each->update(['activo' => false]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Desactivar tallas seleccionadas')
                        ->modalDescription('¿Estás seguro de que deseas desactivar estas tallas? No aparecerán en las opciones de selección.')
                        ->modalSubmitActionLabel('Sí, desactivar'),

                    Tables\Actions\BulkAction::make('reordenar')
                        ->label('Reordenar Seleccionadas')
                        ->icon('heroicon-o-list-bullet')
                        ->color('info')
                        ->form([
                            Forms\Components\TextInput::make('orden_inicial')
                                ->label('Orden Inicial')
                                ->numeric()
                                ->required()
                                ->default(1)
                                ->helperText('Las tallas seleccionadas se ordenarán consecutivamente desde este número'),
                        ])
                        ->action(function ($records, array $data) {
                            $orden = $data['orden_inicial'];
                            $records->each(function ($record) use (&$orden) {
                                $record->update(['orden' => $orden++]);
                            });
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Reordenar tallas seleccionadas')
                        ->modalDescription('Se asignará un orden consecutivo a las tallas seleccionadas')
                        ->modalSubmitActionLabel('Reordenar'),

                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar tallas seleccionadas')
                        ->modalDescription('¿Estás seguro de que deseas eliminar estas tallas? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar'),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Crear Primera Talla')
                    ->icon('heroicon-o-plus'),
            ])
            ->groups([
                Tables\Grouping\Group::make('activo')
                    ->label('Agrupar por Estado')
                    ->getTitleFromRecordUsing(fn ($record) => $record->activo ? 'Tallas Activas' : 'Tallas Inactivas')
                    ->collapsible(),
                Tables\Grouping\Group::make('orden')
                    ->label('Agrupar por Orden')
                    ->getTitleFromRecordUsing(fn ($record) => 'Orden: ' . $record->orden)
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
            'index' => Pages\ListTallas::route('/'),
            'create' => Pages\CreateTalla::route('/create'),
            'edit' => Pages\EditTalla::route('/{record}/edit'),
        ];
    }

    // Muestra la cantidad de tallas en el menú de navegación
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    // Color dinámico del badge según la cantidad y estado de tallas
    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();
        $activeCount = static::getModel()::where('activo', true)->count();
        $inactiveCount = $count - $activeCount;
        
        if ($count == 0) return 'danger'; // Sin tallas es crítico
        if ($inactiveCount > ($count * 0.5)) return 'warning'; // Si más del 50% están inactivas
        if ($activeCount >= 5) return 'success'; // Buena cantidad de tallas activas
        if ($activeCount >= 3) return 'primary'; // Cantidad mínima aceptable
        return 'warning'; // Pocas tallas activas
    }
}