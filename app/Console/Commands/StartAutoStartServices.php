<?php

namespace App\Console\Commands;

use App\Models\Service;
use Illuminate\Console\Command;

class StartAutoStartServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:autostart';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '启动所有设置为自动启动的服务';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $services = Service::where('auto_start', true)->get();
        
        if ($services->isEmpty()) {
            $this->info('没有找到需要自动启动的服务');
            return;
        }
        
        $this->info('开始启动服务...');
        
        foreach ($services as $service) {
            $this->info("正在启动 {$service->name}...");
            
            $result = $service->start();
            
            if ($result) {
                $this->info("✓ {$service->name} 启动成功");
            } else {
                $this->error("✗ {$service->name} 启动失败");
            }
        }
        
        $this->info('所有自动启动服务处理完成');
    }
}
