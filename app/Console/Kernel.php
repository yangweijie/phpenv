<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 每5分钟检查一次服务状态
        $schedule->command('services:check')->everyFiveMinutes();

        // 每分钟收集一次性能指标
        $schedule->command('metrics:collect')->everyMinute();

        // 每天凌晨2点清理旧的性能指标数据
        $schedule->command('metrics:cleanup')->dailyAt('02:00');

        // 每天检查一次系统更新
        $schedule->command('updates:check --notify')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
