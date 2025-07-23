<?php

namespace App\Filament\Resources\TallaResource\Pages;

use App\Filament\Resources\TallaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTalla extends CreateRecord
{
    protected static string $resource = TallaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
