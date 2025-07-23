<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Filament\Resources\ClienteResource\RelationManagers;
use App\Models\Cliente;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?string $pluralLabel = 'Clientes';
    protected static ?string $navigationGroup = 'Ventas y Clientes';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Cliente')
                    ->description('Configura los datos básicos del cliente')
                    ->icon('heroicon-o-user-circle')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nombre')
                                    ->label('Nombre del Cliente')
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('Ej: Juan Pérez')
                                    ->prefixIcon('heroicon-m-user')
                                    ->live(onBlur: true)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('telefono')
                                    ->label('Teléfono')
                                    ->required()
                                    ->maxLength(11)
                                    ->placeholder('Ej: 7012-3456')
                                    ->prefixIcon('heroicon-m-phone')
                                    ->tel()
                                    ->columnSpan(1),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('direccion')
                                    ->label('Dirección')
                                    ->nullable()
                                    ->placeholder('Dirección del cliente')
                                    ->prefixIcon('heroicon-m-map-pin')
                                    ->columnSpan(1),

                                Forms\Components\Toggle::make('es_frecuente')
                                    ->label('Cliente Frecuente')
                                    ->default(false)
                                    ->helperText('Marca si es un cliente frecuente')
                                    ->inline(false)
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Textarea::make('nota')
                            ->label('Nota Adicional')
                            ->columnSpanFull()
                            ->nullable()
                            ->rows(3)
                            ->placeholder('Notas adicionales sobre el cliente')
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
                    ->label('Nombre del Cliente')
                    ->sortable()
                    ->searchable()
                    ->weight(FontWeight::SemiBold)
                    ->icon('heroicon-m-user')
                    ->copyable()
                    ->copyMessage('Nombre copiado')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->color(Color::Blue)
                    ->copyable()
                    ->copyMessage('Teléfono copiado')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('direccion')
                    ->label('Dirección')
                    ->sortable()
                    ->searchable()
                    ->limit(40)
                    ->wrap()
                    ->color(Color::Gray)
                    ->icon('heroicon-m-map-pin')
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (!$state || strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\IconColumn::make('es_frecuente')
                    ->label('Cliente Frecuente')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-user')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nota')
                    ->label('Nota')
                    ->limit(30)
                    ->wrap()
                    ->color(Color::Slate)
                    ->placeholder('Sin notas')
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (!$state || strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Registro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color(Color::Gray)
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Small),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color(Color::Gray)
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Small),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('es_frecuente')
                    ->label('Tipo de Cliente')
                    ->placeholder('Todos los clientes')
                    ->trueLabel('Solo clientes frecuentes')
                    ->falseLabel('Solo clientes ocasionales')
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
                        ->modalHeading('Eliminar clientes seleccionados')
                        ->modalDescription('¿Estás seguro de que deseas eliminar estos clientes? Esta acción no se puede deshacer.')
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
        ];
    }

    // Para mostrar la cantidad de clientes en el menú de navegación
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    // Color dinámico del badge según la cantidad de clientes
    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();
        if ($count > 50) return 'success';
        if ($count > 20) return 'warning';
        return 'primary';
    }
}