<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ColorResource\Pages;
use App\Models\Color;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Navigation\NavigationGroup;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Colors\Color as FilamentColor;

class ColorResource extends Resource
{
    protected static ?string $model = Color::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationLabel = 'Colores';

    protected static ?string $navigationGroup = 'Catálogo de Productos';

    protected static ?string $pluralLabel = 'Colores';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Color')
                    ->description('Configura los datos básicos del color')
                    ->icon('heroicon-o-swatch')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nombre')
                                    ->label('Nombre del Color')
                                    ->required()
                                    ->maxLength(50)
                                    ->placeholder('Ej: Rojo Vibrante, Azul Cielo')
                                    ->prefixIcon('heroicon-m-tag')
                                    ->live(onBlur: true)
                                    ->columnSpan(1),

                                Forms\Components\Toggle::make('activo')
                                    ->label('Color Activo')
                                    ->default(true)
                                    ->helperText('Activa o desactiva este color')
                                    ->inline(false)
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\TextInput::make('codigo_hex')
                            ->label('Código Hexadecimal')
                            ->placeholder('#FFFFFF')
                            ->maxLength(7)
                            ->prefixIcon('heroicon-m-hashtag')
                            ->helperText('Código de color en formato hexadecimal (ej: #FF5733)')
                            ->regex('/^#[a-fA-F0-9]{6}$/')
                            ->validationMessages([
                                'regex' => 'El código debe tener formato #RRGGBB (ej: #FF5733)',
                            ])
                            ->live(onBlur: true)
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('preview')
                                    ->icon('heroicon-m-eye')
                                    ->color('gray')
                                    ->tooltip('Vista previa del color')
                            )
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre del Color')
                    ->sortable()
                    ->searchable()
                    ->weight(FontWeight::SemiBold)
                    ->icon('heroicon-m-swatch')
                    ->copyable()
                    ->copyMessage('Nombre copiado')
                    ->copyMessageDuration(1500),

                Tables\Columns\ColorColumn::make('codigo_hex')
                    ->label('Color')
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Código copiado')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('codigo_hex')
                    ->label('Código HEX')
                    ->color(FilamentColor::Gray)
                    ->fontFamily('mono')
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Small)
                    ->copyable()
                    ->copyMessage('Código copiado'),

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
                    ->color(FilamentColor::Gray)
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Small),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Estado del Color')
                    ->placeholder('Todos los estados')
                    ->trueLabel('Solo colores activos')
                    ->falseLabel('Solo colores inactivos')
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
                        ->modalHeading('Eliminar colores seleccionados')
                        ->modalDescription('¿Estás seguro de que deseas eliminar estos colores? Esta acción no se puede deshacer.')
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
            'index' => Pages\ListColors::route('/'),
            'create' => Pages\CreateColor::route('/create'),
            'edit' => Pages\EditColor::route('/{record}/edit'),
        ];
    }

    // Para mostrar la cantidad de colores en el menú de navegación
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    // Color dinámico del badge según la cantidad de colores
    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();
        if ($count > 30) return 'success';
        if ($count > 15) return 'warning';
        return 'primary';
    }
}