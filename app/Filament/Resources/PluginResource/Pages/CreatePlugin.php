<?php

namespace App\Filament\Resources\PluginResource\Pages;

use App\Filament\Resources\PluginResource;
use App\Services\PluginManager;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePlugin extends CreateRecord
{
    protected static string $resource = PluginResource::class;
    
    protected function afterCreate(): void
    {
        // 创建插件目录和文件
        try {
            app(PluginManager::class)->createPlugin($this->record->toArray());
            
            Notification::make()
                ->title('插件创建成功')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('插件创建失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
