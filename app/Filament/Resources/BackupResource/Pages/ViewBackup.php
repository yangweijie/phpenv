<?php

namespace App\Filament\Resources\BackupResource\Pages;

use App\Filament\Resources\BackupResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Illuminate\Support\Facades\File;

class ViewBackup extends ViewRecord
{
    protected static string $resource = BackupResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('restore')
                ->label('恢复')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('确认恢复备份')
                ->modalDescription('您确定要恢复此备份吗？这将覆盖当前的数据。')
                ->modalSubmitActionLabel('确认恢复')
                ->action(function () {
                    try {
                        $this->record->restore();
                        $this->notify('success', '备份恢复成功');
                    } catch (\Exception $e) {
                        $this->notify('danger', '备份恢复失败: ' . $e->getMessage());
                    }
                })
                ->visible(fn (): bool => File::exists($this->record->path) && $this->record->status),
            Actions\Action::make('download')
                ->label('下载')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    try {
                        return $this->record->download();
                    } catch (\Exception $e) {
                        $this->notify('danger', '备份下载失败: ' . $e->getMessage());
                    }
                })
                ->visible(fn (): bool => File::exists($this->record->path)),
            Actions\DeleteAction::make()
                ->action(function () {
                    $this->record->deleteBackup();
                    $this->redirect(BackupResource::getUrl());
                }),
        ];
    }
}
