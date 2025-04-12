<?php

namespace App\Filament\Widgets;

use App\Models\Service;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ServicesOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $services = Service::all();
        $runningCount = $services->where('status', true)->count();
        $stoppedCount = $services->where('status', false)->count();
        $totalCount = $services->count();
        
        return [
            Stat::make('服务总数', $totalCount)
                ->description('已配置的服务总数')
                ->descriptionIcon('heroicon-m-server')
                ->color('gray'),
            
            Stat::make('运行中', $runningCount)
                ->description('正在运行的服务')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('已停止', $stoppedCount)
                ->description('已停止的服务')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
