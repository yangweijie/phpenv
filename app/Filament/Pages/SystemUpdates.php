<?php

namespace App\Filament\Pages;

use App\Models\Update;
use App\Models\Setting;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\File;

class SystemUpdates extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-circle';
    
    protected static ?string $navigationLabel = '系统更新';
    
    protected static ?string $navigationGroup = '系统设置';
    
    protected static ?int $navigationSort = 80;

    protected static string $view = 'filament.pages.system-updates';
    
    public $currentVersion;
    public $availableUpdates = [];
    public $updateHistory = [];
    public $loading = false;
    public $installing = false;
    public $selectedVersion = null;
    
    public function mount(): void
    {
        $this->currentVersion = Update::getCurrentVersion();
        $this->loadUpdates();
        $this->loadUpdateHistory();
    }
    
    public function loadUpdates(): void
    {
        $this->loading = true;
        
        try {
            $this->availableUpdates = Update::checkForUpdates();
        } catch (\Exception $e) {
            Notification::make()
                ->title('检查更新失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
        
        $this->loading = false;
    }
    
    public function loadUpdateHistory(): void
    {
        $this->updateHistory = Update::getUpdateHistory();
    }
    
    public function downloadUpdate($version): void
    {
        $this->loading = true;
        
        try {
            $update = Update::downloadUpdate($version);
            
            Notification::make()
                ->title('下载更新成功')
                ->body("更新 [{$version}] 已下载完成")
                ->success()
                ->send();
                
            $this->loadUpdates();
        } catch (\Exception $e) {
            Notification::make()
                ->title('下载更新失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
        
        $this->loading = false;
    }
    
    public function installUpdate($version): void
    {
        $this->installing = true;
        $this->selectedVersion = $version;
        
        try {
            $update = Update::where('version', $version)->first();
            
            if (!$update) {
                throw new \Exception('更新不存在');
            }
            
            $update->install();
            
            Notification::make()
                ->title('安装更新成功')
                ->body("更新 [{$version}] 已安装完成")
                ->success()
                ->send();
                
            $this->currentVersion = Update::getCurrentVersion();
            $this->loadUpdates();
            $this->loadUpdateHistory();
        } catch (\Exception $e) {
            Notification::make()
                ->title('安装更新失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
        
        $this->installing = false;
        $this->selectedVersion = null;
    }
    
    public function rollbackUpdate($version): void
    {
        $this->installing = true;
        $this->selectedVersion = $version;
        
        try {
            $update = Update::where('version', $version)->first();
            
            if (!$update) {
                throw new \Exception('更新不存在');
            }
            
            $update->rollback();
            
            Notification::make()
                ->title('回滚更新成功')
                ->body("更新 [{$version}] 已回滚")
                ->success()
                ->send();
                
            $this->currentVersion = Update::getCurrentVersion();
            $this->loadUpdates();
            $this->loadUpdateHistory();
        } catch (\Exception $e) {
            Notification::make()
                ->title('回滚更新失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
        
        $this->installing = false;
        $this->selectedVersion = null;
    }
    
    public function cleanupUpdateFiles(): void
    {
        try {
            $count = Update::cleanupUpdateFiles();
            
            Notification::make()
                ->title('清理更新文件成功')
                ->body("已清理 {$count} 个更新文件")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('清理更新文件失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
