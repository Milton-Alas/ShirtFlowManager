<?php

namespace App\Filament\Resources\VarianteProductoResource\Pages;

use App\Filament\Resources\VarianteProductoResource;
use App\Models\VarianteProducto;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditVarianteProducto extends EditRecord
{
    protected static string $resource = VarianteProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var VarianteProducto $record */
        try {
            $record->update($data);
            return $record;
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
