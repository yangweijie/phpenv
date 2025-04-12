<?php

namespace App\Filament\Resources\PhpExtensionResource\Pages;

use App\Filament\Resources\PhpExtensionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPhpExtension extends EditRecord
{
    protected static string $resource = PhpExtensionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
