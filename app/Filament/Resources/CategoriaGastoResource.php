<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaGastoResource\Pages;
use App\Models\CategoriaGasto;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Colors\Color;

class CategoriaGastoResource extends Resource
{
    protected static ?string $model = CategoriaGasto::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Categorías de Gastos';

    protected static ?string $pluralLabel = 'Categorías de Gastos';

    protected static ?string $navigationGroup = 'Finanzas';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información de la Categoría')
                    ->description('Configura los datos básicos de la categoría de gasto')
                    ->icon('heroicon-o-tag')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nombre')
                                    ->label('Nombre de la Categoría')
                                    ->required()
                                    ->maxLength(50)
                                    ->placeholder('Ej: Tela, Salarios')
                                    ->prefixIcon('heroicon-m-tag')
                                    ->live(onBlur: true)
                                    ->columnSpan(1),

                                Forms\Components\Toggle::make('activo')
                                    ->label('Categoría Activa')
                                    ->default(true)
                                    ->helperText('Activa o desactiva esta categoría')
                                    ->inline(false)
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción')
                            ->columnSpanFull()
                            ->rows(3)
                            ->placeholder('Descripción detallada de la categoría de gasto')
                            ->autosize()
                            ->extraInputAttributes(['style' => 'resize: none']),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')   
                    ->sortable()
                    ->searchable()
                    ->weight(FontWeight::SemiBold)
                    ->icon('heroicon-m-tag')
                    ->copyable()
                    ->copyMessage('Nombre copiado')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(60)
                    ->wrap()
                    ->color(Color::Gray)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 60) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\IconColumn::make('activo')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color(Color::Gray)
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Small),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Estado de Categoría')
                    ->placeholder('Todos los estados')
                    ->trueLabel('Solo categorías activas')
                    ->falseLabel('Solo categorías inactivas')
                    ->native(false),
            ])
            ->actions([
                ActionGroup::make([
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
                        ->modalHeading('Eliminar categorías seleccionadas')
                        ->modalDescription('¿Estás seguro de que deseas eliminar estas categorías? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s')
            ->deferLoading()
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoriaGastos::route('/'),
            'create' => Pages\CreateCategoriaGasto::route('/create'),
            'edit' => Pages\EditCategoriaGasto::route('/{record}/edit'),
        ];
    }

    // Para mostrar la cantidad de categorías de gastos en el menú de navegación
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    // Personaliza el color del badge en la navegación
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() > 10 ? 'warning' : 'primary';
    }
}