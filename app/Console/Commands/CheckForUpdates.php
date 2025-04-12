<?php

namespace App\Console\Commands;

use App\Models\Update;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Console\Command;

class CheckForUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updates:check {--notify : 发送通知}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检查系统更新';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('正在检查系统更新...');
        
        $currentVersion = Update::getCurrentVersion();
        $this->info("当前版本: {$currentVersion}");
        
        try {
            $updates = Update::checkForUpdates();
            
            if (empty($updates)) {
                $this->info('没有可用的更新');
                return;
            }
            
            $this->info('找到 ' . count($updates) . ' 个可用更新:');
            
            foreach ($updates as $update) {
                $this->info("- 版本 {$update['version']}: {$update['description']}");
            }
            
            // 发送通知
            if ($this->option('notify') && Setting::get('updates_notification', true)) {
                $latestUpdate = end($updates);
                
                ActivityLog::logSystem(
                    "发现新版本 [{$latestUpdate['version']}] 可用", 
                    ['current_version' => $currentVersion], 
                    ActivityLog::TYPE_INFO
                );
            }
        } catch (\Exception $e) {
            $this->error('检查更新失败: ' . $e->getMessage());
        }
    }
}
