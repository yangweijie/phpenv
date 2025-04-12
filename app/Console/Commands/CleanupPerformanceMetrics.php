<?php

namespace App\Console\Commands;

use App\Models\PerformanceMetric;
use Illuminate\Console\Command;

class CleanupPerformanceMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrics:cleanup {days=30 : 保留天数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清理旧的性能指标数据';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int)$this->argument('days');
        
        if ($days < 1) {
            $this->error('保留天数必须大于0');
            return;
        }
        
        $this->info("开始清理 {$days} 天前的性能指标数据...");
        
        $count = PerformanceMetric::cleanupOldMetrics($days);
        
        $this->info("✓ 已清理 {$count} 条性能指标数据");
    }
}
