<?php

namespace App\Filament\Resources\VarianteProductoResource\Pages;

use App\Filament\Resources\VarianteProductoResource;
use App\Models\VarianteProducto;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateVarianteProducto extends CreateRecord
{
    protected static string $resource = VarianteProductoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): VarianteProducto
    {
        try {
            return static::getModel()::create($data);
        } catch (ValidationException $e) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body($e->getMessage())
                ->send();

            throw $e;
        }
    }
}
