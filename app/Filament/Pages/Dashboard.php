<?php

namespace App\Filament\Pages;

use App\Models\Service;
use App\Models\PhpVersion;
use App\Models\Website;
use Filament\Pages\Page;
use Filament\Actions\Action;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';
    
    protected static ?int $navigationSort = 0;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('刷新状态')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    // 刷新所有服务状态
                    $services = Service::all();
                    foreach ($services as $service) {
                        $service->checkStatus();
                    }
                    
                    $this->notify('success', '状态已刷新');
                }),
        ];
    }
    
    public function getServices()
    {
        return Service::all();
    }
    
    public function getActivePhpVersion()
    {
        return PhpVersion::where('is_active', true)->first();
    }
    
    public function getWebsites()
    {
        return Website::all();
    }
}
