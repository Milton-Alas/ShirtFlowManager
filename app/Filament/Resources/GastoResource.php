<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GastoResource\Pages;
use App\Models\Gasto;
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

class GastoResource extends Resource
{
    protected static ?string $model = Gasto::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finanzas';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Gestión de Gastos';
    protected static ?string $modelLabel = 'gasto';
    protected static ?string $pluralModelLabel = 'Gastos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detalles del Gasto')
                    ->description('Registre todos los gastos operativos del negocio')
                    ->icon('heroicon-o-banknotes')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Forms\Components\Select::make('categoria_gasto_id')
                            ->label('Categoría de Gasto')
                            ->relationship('categoria', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->placeholder('Seleccione una categoría')
                            ->prefixIcon('heroicon-m-tag')
                            ->live()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('descripcion')
                            ->label('Descripción del Gasto')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Compra de tela para camisetas blancas')
                            ->prefixIcon('heroicon-m-document-text')
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('monto')
                                    ->label('Monto')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.01)
                                    ->step(0.01)
                                    ->prefix('$')
                                    ->suffix('USD')
                                    ->prefixIcon('heroicon-m-currency-dollar')
                                    ->placeholder('0.00')
                                    ->live(onBlur: true)
                                    ->columnSpan(1),

                                Forms\Components\DatePicker::make('fecha')
                                    ->label('Fecha del Gasto')
                                    ->required()
                                    ->default(now())
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->prefixIcon('heroicon-m-calendar')
                                    ->closeOnDateSelection()
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Textarea::make('nota')
                            ->label('Notas Adicionales')
                            ->nullable()
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->rows(3)
                            ->placeholder('Detalles adicionales sobre este gasto')
                            ->autosize()
                            ->extraInputAttributes(['style' => 'resize: none']),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('fecha', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-m-calendar')
                    ->color(Color::Blue)
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-m-tag'),

                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->wrap()
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    })
                    ->icon('heroicon-m-document-text')
                    ->color(Color::Gray),

                Tables\Columns\TextColumn::make('monto')
                    ->label('Monto')
                    ->money('USD')
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->icon('heroicon-m-currency-dollar')
                    ->color(fn ($record) => match(true) {
                        $record->monto > 500 => 'danger',
                        $record->monto > 100 => 'warning',
                        default => 'success'
                    })
                    ->copyable()
                    ->copyMessage('Monto copiado'),

                Tables\Columns\TextColumn::make('nota')
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
                Tables\Filters\SelectFilter::make('categoria_gasto_id')
                    ->label('Filtrar por Categoría')
                    ->relationship('categoria', 'nombre')
                    ->searchable()
                    ->preload()
                    ->native(false),

                Tables\Filters\Filter::make('monto_alto')
                    ->label('Gastos Altos (>$100)')
                    ->query(fn (Builder $query): Builder => $query->where('monto', '>', 100))
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
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar gastos seleccionados')
                        ->modalDescription('¿Estás seguro de que deseas eliminar estos gastos? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar'),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Registrar Primer Gasto')
                    ->icon('heroicon-o-plus'),
            ])
            ->groups([
                Tables\Grouping\Group::make('fecha')
                    ->label('Agrupar por Fecha')
                    ->date()
                    ->collapsible(),
                Tables\Grouping\Group::make('categoria.nombre')
                    ->label('Agrupar por Categoría')
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
            'index' => Pages\ListGastos::route('/'),
            'create' => Pages\CreateGasto::route('/create'),
            'edit' => Pages\EditGasto::route('/{record}/edit'),
        ];
    }

    // Muestra la cantidad de gastos en el menú de navegación
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    // Color dinámico del badge según la cantidad de gastos
    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();
        if ($count > 100) return 'danger';
        if ($count > 50) return 'warning';
        return 'primary';
    }
}