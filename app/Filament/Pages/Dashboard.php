<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardOverview;
use App\Models\Service;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\Action;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

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

    public function getWidgets(): array
    {
        return [
            DashboardOverview::class,
        ];
    }
}
