<?php

namespace App\Filament\Widgets;

use App\Models\Website;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WebsitesOverview extends BaseWidget
{
    protected static ?int $sort = 3;
    
    protected function getStats(): array
    {
        $websites = Website::all();
        $activeCount = $websites->where('status', true)->count();
        $apacheCount = $websites->where('server_type', Website::SERVER_TYPE_APACHE)->count();
        $nginxCount = $websites->where('server_type', Website::SERVER_TYPE_NGINX)->count();
        
        return [
            Stat::make('网站总数', $websites->count())
                ->description('已配置的网站总数')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('gray'),
            
            Stat::make('Apache网站', $apacheCount)
                ->description('使用Apache的网站数量')
                ->descriptionIcon('heroicon-m-server')
                ->color('success'),
            
            Stat::make('Nginx网站', $nginxCount)
                ->description('使用Nginx的网站数量')
                ->descriptionIcon('heroicon-m-server')
                ->color('info'),
        ];
    }
}
