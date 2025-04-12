<?php

namespace App\Filament\Resources\ComposerPackageResource\Pages;

use App\Filament\Resources\ComposerPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComposerPackage extends EditRecord
{
    protected static string $resource = ComposerPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
