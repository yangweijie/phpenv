<?php

namespace App\Filament\Resources\EnvironmentVariableResource\Pages;

use App\Filament\Resources\EnvironmentVariableResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnvironmentVariable extends EditRecord
{
    protected static string $resource = EnvironmentVariableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
