<?php

namespace App\Filament\Resources\PluginResource\Pages;

use App\Filament\Resources\PluginResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlugin extends EditRecord
{
    protected static string $resource = PluginResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->action(function () {
                    try {
                        $this->record->uninstall();
                        $this->redirect(PluginResource::getUrl());
                    } catch (\Exception $e) {
                        $this->notify('danger', '卸载插件失败: ' . $e->getMessage());
                    }
                }),
        ];
    }
}
