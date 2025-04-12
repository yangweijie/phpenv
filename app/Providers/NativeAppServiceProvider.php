<?php

namespace App\Providers;

use Native\Laravel\Facades\Window;
use Native\Laravel\NativeServiceProvider;

class NativeAppServiceProvider extends NativeServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 在运行迁移时暂时禁用NativePHP功能
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }

        // 创建主窗口
        Window::open('main')
            ->title('PHP开发集成环境')
            ->width(1200)
            ->height(800)
            ->minWidth(800)
            ->minHeight(600)
            ->route('filament.admin.pages.dashboard');
    }
}
