<?php

namespace App\Filament\Resources\PluginResource\Pages;

use App\Filament\Resources\PluginResource;
use App\Models\Plugin;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ListPlugins extends ListRecords
{
    protected static string $resource = PluginResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('upload_plugin')
                ->label('上传插件')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    Forms\Components\FileUpload::make('plugin_file')
                        ->label('插件文件')
                        ->acceptedFileTypes(['application/zip'])
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        $file = $data['plugin_file'];
                        $path = Storage::disk('local')->path($file);
                        
                        $plugin = Plugin::installPlugin($path);
                        
                        Notification::make()
                            ->title('插件上传成功')
                            ->body("插件 [{$plugin->name}] 已成功安装")
                            ->success()
                            ->send();
                            
                        return redirect()->route('filament.admin.resources.plugins.index');
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('插件上传失败')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('sync_plugins')
                ->label('同步插件')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    try {
                        $result = Plugin::syncPlugins();
                        
                        Notification::make()
                            ->title('插件同步成功')
                            ->body("添加: {$result['added']}, 更新: {$result['updated']}, 移除: {$result['removed']}")
                            ->success()
                            ->send();
                            
                        return redirect()->route('filament.admin.resources.plugins.index');
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('插件同步失败')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
