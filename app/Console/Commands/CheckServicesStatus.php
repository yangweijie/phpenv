<?php

namespace App\Console\Commands;

use App\Models\Service;
use Illuminate\Console\Command;

class CheckServicesStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检查所有服务的运行状态';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $services = Service::all();
        
        if ($services->isEmpty()) {
            $this->info('没有找到服务');
            return;
        }
        
        $this->info('开始检查服务状态...');
        
        $headers = ['ID', '名称', '类型', '状态', '端口'];
        $rows = [];
        
        foreach ($services as $service) {
            $status = $service->checkStatus();
            
            $rows[] = [
                $service->id,
                $service->name,
                Service::getTypes()[$service->type] ?? $service->type,
                $status ? '运行中' : '已停止',
                $service->port,
            ];
        }
        
        $this->table($headers, $rows);
        $this->info('服务状态检查完成');
    }
}
