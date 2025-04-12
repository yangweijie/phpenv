<?php

namespace App\Filament\Resources\PluginResource\Pages;

use App\Filament\Resources\PluginResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPlugin extends ViewRecord
{
    protected static string $resource = PluginResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('activate')
                ->label('激活')
                ->icon('heroic-check')
                ->color('success')
                ->visible(fn () => !$this->record->status)
                ->action(function () {
                    $this->record->activate();
                    $this->notify('success', '插件已激活');
                    $this->redirect(PluginResource::getUrl('view', ['record' => $this->record]));
                }),
            Actions::Action::make('deactivate')
                ->label('停用')
                ->icon('heroic-x-mark')
                ->color('danger')
                ->visible(fn () => $this->record->status)
                ->action(function () {
                    $this->record->deactivate();
                    $this->notify('success', '插件已停用');
                    $this->redirect(PluginResource::getUrl('view', ['record' => $this->record]));
                }),
            Actions\Action::make('settings')
                ->label('设置')
                ->icon('heroicon-o-cog')
                ->url(fn (): string => PluginResource::getUrl('settings', ['record' => $this->record]))
                ->visible(fn () => $this->record->status),
        ];
    }
}
