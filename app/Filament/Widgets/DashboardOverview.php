<?php

namespace App\Filament\Widgets;

use App\Models\Service;
use App\Models\PhpVersion;
use App\Models\Website;
use Filament\Widgets\Widget;

class DashboardOverview extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-overview';

    protected int | string | array $columnSpan = 'full';

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
