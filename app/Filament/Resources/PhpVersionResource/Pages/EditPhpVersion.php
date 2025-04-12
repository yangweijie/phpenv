<?php

namespace App\Filament\Resources\PhpVersionResource\Pages;

use App\Filament\Resources\PhpVersionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPhpVersion extends EditRecord
{
    protected static string $resource = PhpVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
