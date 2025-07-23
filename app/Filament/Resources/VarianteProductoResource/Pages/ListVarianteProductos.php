<?php

namespace App\Filament\Resources\VarianteProductoResource\Pages;

use App\Filament\Resources\VarianteProductoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVarianteProductos extends ListRecords
{
    protected static string $resource = VarianteProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
