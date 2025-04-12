<?php

namespace App\Console\Commands;

use App\Models\PhpVersion;
use Illuminate\Console\Command;

class SwitchPhpVersion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'php:switch {version? : PHP版本号}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '切换当前使用的PHP版本';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phpVersions = PhpVersion::all();
        
        if ($phpVersions->isEmpty()) {
            $this->error('没有找到PHP版本');
            return;
        }
        
        $version = $this->argument('version');
        
        if (!$version) {
            // 显示可用的PHP版本
            $headers = ['ID', '版本', '路径', '当前激活', '默认版本'];
            $rows = [];
            
            foreach ($phpVersions as $phpVersion) {
                $rows[] = [
                    $phpVersion->id,
                    $phpVersion->version,
                    $phpVersion->path,
                    $phpVersion->is_active ? '是' : '否',
                    $phpVersion->is_default ? '是' : '否',
                ];
            }
            
            $this->table($headers, $rows);
            
            // 让用户选择版本
            $version = $this->choice(
                '请选择要激活的PHP版本',
                $phpVersions->pluck('version')->toArray()
            );
        }
        
        // 查找选择的版本
        $phpVersion = PhpVersion::where('version', $version)->first();
        
        if (!$phpVersion) {
            $this->error("未找到PHP版本: {$version}");
            return;
        }
        
        $this->info("正在切换到PHP {$phpVersion->version}...");
        
        $result = $phpVersion->activate();
        
        if ($result) {
            $this->info("✓ 成功切换到PHP {$phpVersion->version}");
        } else {
            $this->error("✗ 切换PHP版本失败");
        }
    }
}
