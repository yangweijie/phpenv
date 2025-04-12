<?php

namespace App\Filament\Resources\PhpVersionResource\Pages;

use App\Filament\Resources\PhpVersionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPhpVersions extends ListRecords
{
    protected static string $resource = PhpVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
