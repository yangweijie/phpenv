<?php

namespace App\Livewire;

use App\Models\Service;
use Livewire\Component;
use Filament\Notifications\Notification;

class ServiceActions extends Component
{
    protected $listeners = [
        'start-service' => 'startService',
        'stop-service' => 'stopService',
        'restart-service' => 'restartService',
    ];
    
    public function startService($data)
    {
        $service = Service::find($data['id']);
        
        if (!$service) {
            Notification::make()
                ->title('错误')
                ->body('未找到服务')
                ->danger()
                ->send();
            return;
        }
        
        $result = $service->start();
        
        if ($result) {
            Notification::make()
                ->title('成功')
                ->body('服务已启动')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('错误')
                ->body('服务启动失败')
                ->danger()
                ->send();
        }
        
        $this->dispatch('refresh');
    }
    
    public function stopService($data)
    {
        $service = Service::find($data['id']);
        
        if (!$service) {
            Notification::make()
                ->title('错误')
                ->body('未找到服务')
                ->danger()
                ->send();
            return;
        }
        
        $result = $service->stop();
        
        if ($result) {
            Notification::make()
                ->title('成功')
                ->body('服务已停止')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('错误')
                ->body('服务停止失败')
                ->danger()
                ->send();
        }
        
        $this->dispatch('refresh');
    }
    
    public function restartService($data)
    {
        $service = Service::find($data['id']);
        
        if (!$service) {
            Notification::make()
                ->title('错误')
                ->body('未找到服务')
                ->danger()
                ->send();
            return;
        }
        
        $result = $service->restart();
        
        if ($result) {
            Notification::make()
                ->title('成功')
                ->body('服务已重启')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('错误')
                ->body('服务重启失败')
                ->danger()
                ->send();
        }
        
        $this->dispatch('refresh');
    }
    
    public function render()
    {
        return view('livewire.service-actions');
    }
}
