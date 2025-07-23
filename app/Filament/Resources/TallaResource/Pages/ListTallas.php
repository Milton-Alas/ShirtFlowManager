<?php

namespace App\Filament\Resources\TallaResource\Pages;

use App\Filament\Resources\TallaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTallas extends ListRecords
{
    protected static string $resource = TallaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
