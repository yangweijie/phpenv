<?php

namespace App\Filament\Resources\BackupResource\Pages;

use App\Filament\Resources\BackupResource;
use App\Models\Backup;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms;
use Filament\Notifications\Notification;

class ListBackups extends ListRecords
{
    protected static string $resource = BackupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_backup')
                ->label('创建备份')
                ->icon('heroicon-o-plus')
                ->form([
                    Forms\Components\Select::make('type')
                        ->label('备份类型')
                        ->options(Backup::getTypes())
                        ->required(),
                    Forms\Components\Textarea::make('description')
                        ->label('备份描述')
                        ->maxLength(65535),
                ])
                ->action(function (array $data) {
                    try {
                        $backup = Backup::createBackup($data['type'], $data['description'] ?? null);
                        
                        Notification::make()
                            ->title('备份创建成功')
                            ->success()
                            ->send();
                            
                        return redirect()->route('filament.admin.resources.backups.index');
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('备份创建失败')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('cleanup_backups')
                ->label('清理备份')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->form([
                    Forms\Components\TextInput::make('days')
                        ->label('保留天数')
                        ->helperText('将删除指定天数之前的备份')
                        ->numeric()
                        ->default(30)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $count = Backup::cleanupExpiredBackups($data['days']);
                    
                    Notification::make()
                        ->title('备份清理完成')
                        ->body("已删除 {$count} 个过期备份")
                        ->success()
                        ->send();
                        
                    return redirect()->route('filament.admin.resources.backups.index');
                }),
        ];
    }
}
