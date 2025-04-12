<?php

namespace App\Filament\Pages;

use App\Services\PluginManager;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class PluginMarketplace extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    
    protected static ?string $navigationLabel = '插件市场';
    
    protected static ?string $navigationGroup = '系统设置';
    
    protected static ?int $navigationSort = 55;

    protected static string $view = 'filament.pages.plugin-marketplace';
    
    public $plugins = [];
    public $loading = true;
    
    public function mount(): void
    {
        $this->loadPlugins();
    }
    
    public function loadPlugins(): void
    {
        $this->loading = true;
        
        try {
            $this->plugins = app(PluginManager::class)->getMarketplacePlugins();
        } catch (\Exception $e) {
            Notification::make()
                ->title('加载插件失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
        
        $this->loading = false;
    }
    
    public function installPlugin($pluginId): void
    {
        $this->loading = true;
        
        try {
            $plugin = app(PluginManager::class)->downloadPluginFromMarketplace($pluginId);
            
            Notification::make()
                ->title('插件安装成功')
                ->body("插件 [{$plugin->name}] 已成功安装")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('插件安装失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
        
        $this->loading = false;
    }
}
