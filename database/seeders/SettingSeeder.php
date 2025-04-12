<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 常规设置
        Setting::set('app_name', 'PHP开发集成环境', Setting::TYPE_GENERAL, '应用名称');
        Setting::set('app_description', 'PHP开发集成环境客户端', Setting::TYPE_GENERAL, '应用描述');
        Setting::set('app_version', '1.0.0', Setting::TYPE_GENERAL, '应用版本');
        Setting::set('app_author', 'PHP开发团队', Setting::TYPE_GENERAL, '应用作者');
        Setting::set('app_email', 'admin@example.com', Setting::TYPE_GENERAL, '联系邮箱');
        
        // 服务设置
        Setting::set('services_auto_start', true, Setting::TYPE_SERVICES, '启动时自动启动服务');
        Setting::set('services_check_interval', 5, Setting::TYPE_SERVICES, '服务状态检查间隔（分钟）');
        Setting::set('services_notification', true, Setting::TYPE_SERVICES, '服务状态变化通知');
        
        // PHP设置
        Setting::set('php_default_version', '8.1', Setting::TYPE_PHP, '默认PHP版本');
        Setting::set('php_auto_switch', true, Setting::TYPE_PHP, '根据项目自动切换PHP版本');
        Setting::set('php_extensions_auto_enable', true, Setting::TYPE_PHP, '自动启用常用PHP扩展');
        
        // 安全设置
        Setting::set('security_log_actions', true, Setting::TYPE_SECURITY, '记录所有操作');
        Setting::set('security_log_retention', 30, Setting::TYPE_SECURITY, '日志保留天数');
        Setting::set('security_backup_enabled', true, Setting::TYPE_SECURITY, '启用自动备份');
        Setting::set('security_backup_interval', 7, Setting::TYPE_SECURITY, '备份间隔（天）');
    }
}
