<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Models\PerformanceMetric;
use Illuminate\Console\Command;

class CollectPerformanceMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrics:collect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '收集服务性能指标';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $services = Service::where('status', true)->get();
        
        if ($services->isEmpty()) {
            $this->info('没有找到运行中的服务');
            return;
        }
        
        $this->info('开始收集服务性能指标...');
        
        foreach ($services as $service) {
            $this->info("正在收集 {$service->name} 的性能指标...");
            
            $result = PerformanceMetric::collectServiceMetrics($service);
            
            if ($result) {
                $this->info("✓ {$service->name} 性能指标收集成功");
            } else {
                $this->error("✗ {$service->name} 性能指标收集失败");
            }
        }
        
        $this->info('所有服务性能指标收集完成');
    }
}
