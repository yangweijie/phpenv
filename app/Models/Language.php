<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_default',
        'is_active',
        'direction',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    // 语言方向常量
    const DIRECTION_LTR = 'ltr';
    const DIRECTION_RTL = 'rtl';

    // 获取语言方向列表
    public static function getDirections()
    {
        return [
            self::DIRECTION_LTR => '从左到右',
            self::DIRECTION_RTL => '从右到左',
        ];
    }

    // 设置为默认语言
    public function setAsDefault()
    {
        // 首先将所有语言设置为非默认状态
        self::query()->update(['is_default' => false]);
        
        // 设置当前语言为默认状态
        $this->is_default = true;
        $this->save();
        
        // 记录日志
        ActivityLog::logSystem(
            "设置 [{$this->name}] 为默认语言", 
            ['code' => $this->code], 
            ActivityLog::TYPE_SUCCESS
        );
        
        return true;
    }

    // 激活语言
    public function activate()
    {
        $this->is_active = true;
        $this->save();
        
        // 记录日志
        ActivityLog::logSystem(
            "激活语言 [{$this->name}]", 
            ['code' => $this->code], 
            ActivityLog::TYPE_SUCCESS
        );
        
        return true;
    }

    // 停用语言
    public function deactivate()
    {
        // 如果是默认语言，不允许停用
        if ($this->is_default) {
            throw new \Exception('不能停用默认语言');
        }
        
        $this->is_active = false;
        $this->save();
        
        // 记录日志
        ActivityLog::logSystem(
            "停用语言 [{$this->name}]", 
            ['code' => $this->code], 
            ActivityLog::TYPE_SUCCESS
        );
        
        return true;
    }

    // 获取语言文件路径
    public function getLanguageFilePath($file = 'general')
    {
        return resource_path("lang/{$this->code}/{$file}.php");
    }

    // 获取语言文件内容
    public function getLanguageFileContent($file = 'general')
    {
        $path = $this->getLanguageFilePath($file);
        
        if (!File::exists($path)) {
            return [];
        }
        
        return include $path;
    }

    // 保存语言文件内容
    public function saveLanguageFileContent($content, $file = 'general')
    {
        $path = $this->getLanguageFilePath($file);
        
        // 确保目录存在
        $directory = dirname($path);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
        
        // 格式化内容为PHP数组
        $formattedContent = "<?php\n\nreturn " . var_export($content, true) . ";\n";
        
        // 保存文件
        File::put($path, $formattedContent);
        
        // 清除缓存
        Cache::forget("language.{$this->code}.{$file}");
        
        // 记录日志
        ActivityLog::logSystem(
            "更新语言文件 [{$this->code}/{$file}]", 
            [], 
            ActivityLog::TYPE_SUCCESS
        );
        
        return true;
    }

    // 获取可用的语言文件
    public function getAvailableLanguageFiles()
    {
        $path = resource_path("lang/{$this->code}");
        
        if (!File::exists($path)) {
            return [];
        }
        
        $files = File::files($path);
        $result = [];
        
        foreach ($files as $file) {
            $filename = $file->getFilenameWithoutExtension();
            $result[$filename] = $filename;
        }
        
        return $result;
    }

    // 导入语言文件
    public function importLanguageFile($sourcePath, $file = 'general')
    {
        if (!File::exists($sourcePath)) {
            throw new \Exception('源文件不存在');
        }
        
        $content = include $sourcePath;
        
        if (!is_array($content)) {
            throw new \Exception('源文件格式不正确');
        }
        
        return $this->saveLanguageFileContent($content, $file);
    }

    // 导出语言文件
    public function exportLanguageFile($targetPath, $file = 'general')
    {
        $content = $this->getLanguageFileContent($file);
        
        // 确保目录存在
        $directory = dirname($targetPath);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
        
        // 格式化内容为PHP数组
        $formattedContent = "<?php\n\nreturn " . var_export($content, true) . ";\n";
        
        // 保存文件
        File::put($targetPath, $formattedContent);
        
        return true;
    }

    // 复制语言文件
    public static function copyLanguageFiles($sourceCode, $targetCode)
    {
        $sourcePath = resource_path("lang/{$sourceCode}");
        $targetPath = resource_path("lang/{$targetCode}");
        
        if (!File::exists($sourcePath)) {
            throw new \Exception('源语言文件不存在');
        }
        
        // 确保目标目录存在
        if (!File::exists($targetPath)) {
            File::makeDirectory($targetPath, 0755, true);
        }
        
        // 复制文件
        File::copyDirectory($sourcePath, $targetPath);
        
        // 记录日志
        ActivityLog::logSystem(
            "复制语言文件 [{$sourceCode}] 到 [{$targetCode}]", 
            [], 
            ActivityLog::TYPE_SUCCESS
        );
        
        return true;
    }

    // 获取当前语言
    public static function getCurrentLanguage()
    {
        $locale = App::getLocale();
        $language = self::where('code', $locale)->first();
        
        if (!$language) {
            $language = self::where('is_default', true)->first();
        }
        
        return $language;
    }

    // 设置当前语言
    public static function setCurrentLanguage($code)
    {
        $language = self::where('code', $code)->where('is_active', true)->first();
        
        if (!$language) {
            $language = self::where('is_default', true)->first();
        }
        
        if ($language) {
            App::setLocale($language->code);
            session(['locale' => $language->code]);
            
            // 记录日志
            ActivityLog::logSystem(
                "切换语言到 [{$language->name}]", 
                ['code' => $language->code], 
                ActivityLog::TYPE_INFO
            );
        }
        
        return $language;
    }

    // 获取翻译
    public static function trans($key, $replace = [], $locale = null)
    {
        if (!$locale) {
            $locale = App::getLocale();
        }
        
        // 分割键名，获取文件名和键名
        $parts = explode('.', $key, 2);
        $file = $parts[0];
        $keyName = $parts[1] ?? null;
        
        if (!$keyName) {
            return $key;
        }
        
        // 从缓存中获取翻译
        $translations = Cache::remember("language.{$locale}.{$file}", 60, function () use ($locale, $file) {
            $path = resource_path("lang/{$locale}/{$file}.php");
            
            if (!File::exists($path)) {
                return [];
            }
            
            return include $path;
        });
        
        // 获取翻译
        $translation = $translations[$keyName] ?? $key;
        
        // 替换占位符
        foreach ($replace as $key => $value) {
            $translation = str_replace(':' . $key, $value, $translation);
        }
        
        return $translation;
    }

    // 初始化默认语言
    public static function initializeDefaultLanguages()
    {
        // 创建英语
        $english = self::firstOrCreate(
            ['code' => 'en'],
            [
                'name' => 'English',
                'is_default' => true,
                'is_active' => true,
                'direction' => self::DIRECTION_LTR,
            ]
        );
        
        // 创建中文
        $chinese = self::firstOrCreate(
            ['code' => 'zh'],
            [
                'name' => '中文',
                'is_default' => false,
                'is_active' => true,
                'direction' => self::DIRECTION_LTR,
            ]
        );
        
        // 初始化英语翻译
        $englishTranslations = [
            'app' => [
                'name' => 'PHP Development Environment',
                'description' => 'PHP Development Environment Client',
                'version' => '1.0.0',
                'author' => 'PHP Development Team',
            ],
            'common' => [
                'save' => 'Save',
                'cancel' => 'Cancel',
                'delete' => 'Delete',
                'edit' => 'Edit',
                'view' => 'View',
                'create' => 'Create',
                'update' => 'Update',
                'search' => 'Search',
                'filter' => 'Filter',
                'reset' => 'Reset',
                'back' => 'Back',
                'next' => 'Next',
                'previous' => 'Previous',
                'yes' => 'Yes',
                'no' => 'No',
                'confirm' => 'Confirm',
                'success' => 'Success',
                'error' => 'Error',
                'warning' => 'Warning',
                'info' => 'Information',
            ],
            'dashboard' => [
                'title' => 'Dashboard',
                'welcome' => 'Welcome to PHP Development Environment',
                'services' => 'Services',
                'php_versions' => 'PHP Versions',
                'websites' => 'Websites',
                'latest_activities' => 'Latest Activities',
            ],
            'services' => [
                'title' => 'Services',
                'name' => 'Name',
                'type' => 'Type',
                'status' => 'Status',
                'port' => 'Port',
                'path' => 'Path',
                'auto_start' => 'Auto Start',
                'start' => 'Start',
                'stop' => 'Stop',
                'restart' => 'Restart',
                'running' => 'Running',
                'stopped' => 'Stopped',
            ],
            'php' => [
                'title' => 'PHP Versions',
                'version' => 'Version',
                'path' => 'Path',
                'active' => 'Active',
                'default' => 'Default',
                'activate' => 'Activate',
                'set_default' => 'Set as Default',
                'extensions' => 'Extensions',
                'enable' => 'Enable',
                'disable' => 'Disable',
            ],
            'websites' => [
                'title' => 'Websites',
                'name' => 'Name',
                'domain' => 'Domain',
                'root_path' => 'Root Path',
                'server_type' => 'Server Type',
                'php_version' => 'PHP Version',
                'create_config' => 'Create Config',
                'update_hosts' => 'Update Hosts',
                'open' => 'Open',
            ],
            'projects' => [
                'title' => 'Projects',
                'name' => 'Name',
                'path' => 'Path',
                'type' => 'Type',
                'description' => 'Description',
                'php_version' => 'PHP Version',
                'website' => 'Website',
                'git_repository' => 'Git Repository',
                'status' => 'Status',
                'open' => 'Open Project',
                'open_website' => 'Open Website',
                'view_info' => 'View Info',
            ],
            'backups' => [
                'title' => 'Backups',
                'name' => 'Name',
                'path' => 'Path',
                'size' => 'Size',
                'type' => 'Type',
                'description' => 'Description',
                'status' => 'Status',
                'created_at' => 'Created At',
                'create_backup' => 'Create Backup',
                'restore' => 'Restore',
                'download' => 'Download',
                'cleanup' => 'Cleanup',
            ],
            'settings' => [
                'title' => 'Settings',
                'general' => 'General Settings',
                'services' => 'Services Settings',
                'php' => 'PHP Settings',
                'security' => 'Security Settings',
                'app_name' => 'Application Name',
                'app_description' => 'Application Description',
                'app_version' => 'Application Version',
                'app_author' => 'Application Author',
                'app_email' => 'Contact Email',
                'services_auto_start' => 'Auto Start Services',
                'services_check_interval' => 'Services Check Interval (minutes)',
                'services_notification' => 'Services Status Change Notification',
                'php_default_version' => 'Default PHP Version',
                'php_auto_switch' => 'Auto Switch PHP Version',
                'php_extensions_auto_enable' => 'Auto Enable Common PHP Extensions',
                'security_log_actions' => 'Log All Actions',
                'security_log_retention' => 'Log Retention Days',
                'security_backup_enabled' => 'Enable Auto Backup',
                'security_backup_interval' => 'Backup Interval (days)',
            ],
        ];
        
        // 初始化中文翻译
        $chineseTranslations = [
            'app' => [
                'name' => 'PHP开发环境',
                'description' => 'PHP开发环境客户端',
                'version' => '1.0.0',
                'author' => 'PHP开发团队',
            ],
            'common' => [
                'save' => '保存',
                'cancel' => '取消',
                'delete' => '删除',
                'edit' => '编辑',
                'view' => '查看',
                'create' => '创建',
                'update' => '更新',
                'search' => '搜索',
                'filter' => '筛选',
                'reset' => '重置',
                'back' => '返回',
                'next' => '下一步',
                'previous' => '上一步',
                'yes' => '是',
                'no' => '否',
                'confirm' => '确认',
                'success' => '成功',
                'error' => '错误',
                'warning' => '警告',
                'info' => '信息',
            ],
            'dashboard' => [
                'title' => '仪表盘',
                'welcome' => '欢迎使用PHP开发环境',
                'services' => '服务',
                'php_versions' => 'PHP版本',
                'websites' => '网站',
                'latest_activities' => '最近活动',
            ],
            'services' => [
                'title' => '服务',
                'name' => '名称',
                'type' => '类型',
                'status' => '状态',
                'port' => '端口',
                'path' => '路径',
                'auto_start' => '自动启动',
                'start' => '启动',
                'stop' => '停止',
                'restart' => '重启',
                'running' => '运行中',
                'stopped' => '已停止',
            ],
            'php' => [
                'title' => 'PHP版本',
                'version' => '版本',
                'path' => '路径',
                'active' => '活动',
                'default' => '默认',
                'activate' => '激活',
                'set_default' => '设为默认',
                'extensions' => '扩展',
                'enable' => '启用',
                'disable' => '禁用',
            ],
            'websites' => [
                'title' => '网站',
                'name' => '名称',
                'domain' => '域名',
                'root_path' => '根目录',
                'server_type' => '服务器类型',
                'php_version' => 'PHP版本',
                'create_config' => '创建配置',
                'update_hosts' => '更新Hosts',
                'open' => '打开',
            ],
            'projects' => [
                'title' => '项目',
                'name' => '名称',
                'path' => '路径',
                'type' => '类型',
                'description' => '描述',
                'php_version' => 'PHP版本',
                'website' => '网站',
                'git_repository' => 'Git仓库',
                'status' => '状态',
                'open' => '打开项目',
                'open_website' => '打开网站',
                'view_info' => '查看信息',
            ],
            'backups' => [
                'title' => '备份',
                'name' => '名称',
                'path' => '路径',
                'size' => '大小',
                'type' => '类型',
                'description' => '描述',
                'status' => '状态',
                'created_at' => '创建时间',
                'create_backup' => '创建备份',
                'restore' => '恢复',
                'download' => '下载',
                'cleanup' => '清理',
            ],
            'settings' => [
                'title' => '设置',
                'general' => '常规设置',
                'services' => '服务设置',
                'php' => 'PHP设置',
                'security' => '安全设置',
                'app_name' => '应用名称',
                'app_description' => '应用描述',
                'app_version' => '应用版本',
                'app_author' => '应用作者',
                'app_email' => '联系邮箱',
                'services_auto_start' => '启动时自动启动服务',
                'services_check_interval' => '服务状态检查间隔（分钟）',
                'services_notification' => '服务状态变化通知',
                'php_default_version' => '默认PHP版本',
                'php_auto_switch' => '根据项目自动切换PHP版本',
                'php_extensions_auto_enable' => '自动启用常用PHP扩展',
                'security_log_actions' => '记录所有操作',
                'security_log_retention' => '日志保留天数',
                'security_backup_enabled' => '启用自动备份',
                'security_backup_interval' => '备份间隔（天）',
            ],
        ];
        
        // 保存英语翻译
        foreach ($englishTranslations as $file => $translations) {
            $english->saveLanguageFileContent($translations, $file);
        }
        
        // 保存中文翻译
        foreach ($chineseTranslations as $file => $translations) {
            $chinese->saveLanguageFileContent($translations, $file);
        }
        
        return [
            'en' => $english,
            'zh' => $chinese,
        ];
    }
}
