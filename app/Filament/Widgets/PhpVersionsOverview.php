<?php

namespace App\Filament\Widgets;

use App\Models\PhpVersion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PhpVersionsOverview extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected function getStats(): array
    {
        $phpVersions = PhpVersion::all();
        $activeVersion = $phpVersions->where('is_active', true)->first();
        $defaultVersion = $phpVersions->where('is_default', true)->first();
        $totalVersions = $phpVersions->count();
        
        return [
            Stat::make('PHP版本总数', $totalVersions)
                ->description('已配置的PHP版本总数')
                ->descriptionIcon('heroicon-m-code-bracket')
                ->color('gray'),
            
            Stat::make('当前活动版本', $activeVersion ? 'PHP ' . $activeVersion->version : '无')
                ->description($activeVersion ? '路径: ' . $activeVersion->path : '未设置活动版本')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('默认版本', $defaultVersion ? 'PHP ' . $defaultVersion->version : '无')
                ->description($defaultVersion ? '路径: ' . $defaultVersion->path : '未设置默认版本')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
        ];
    }
}
