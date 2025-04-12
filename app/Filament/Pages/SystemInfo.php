<?php

namespace App\Filament\Pages;

use App\Models\PhpVersion;
use Filament\Pages\Page;

class SystemInfo extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-information-circle';
    
    protected static ?string $navigationLabel = '系统信息';
    
    protected static ?string $navigationGroup = '系统设置';
    
    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.system-info';
    
    public function getPhpInfo()
    {
        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();
        
        // 提取主体内容
        $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
        
        return $phpinfo;
    }
    
    public function getActivePhpVersion()
    {
        return PhpVersion::where('is_active', true)->first();
    }
    
    public function getSystemInfo()
    {
        return [
            'PHP版本' => PHP_VERSION,
            '操作系统' => PHP_OS,
            '服务器软件' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'PHP SAPI' => php_sapi_name(),
            '当前工作目录' => getcwd(),
            '临时文件目录' => sys_get_temp_dir(),
            'PHP配置文件' => php_ini_loaded_file(),
            'PHP扩展目录' => ini_get('extension_dir'),
            'PHP时区' => date_default_timezone_get(),
            'PHP内存限制' => ini_get('memory_limit'),
            'PHP最大执行时间' => ini_get('max_execution_time') . '秒',
            'PHP上传文件大小限制' => ini_get('upload_max_filesize'),
            'PHP POST大小限制' => ini_get('post_max_size'),
        ];
    }
    
    public function getLoadedExtensions()
    {
        $extensions = get_loaded_extensions();
        sort($extensions);
        return $extensions;
    }
}
