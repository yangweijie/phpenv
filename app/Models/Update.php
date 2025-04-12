<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use ZipArchive;

class Update extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'description',
        'download_url',
        'release_notes',
        'is_installed',
        'installed_at',
        'size',
        'checksum',
        'requires_version',
    ];

    protected $casts = [
        'is_installed' => 'boolean',
        'installed_at' => 'datetime',
        'size' => 'integer',
    ];

    // 更新服务器URL
    protected static $updateServerUrl = 'https://example.com/api/updates';

    // 当前版本
    public static function getCurrentVersion()
    {
        return Setting::get('app_version', '1.0.0');
    }

    // 检查更新
    public static function checkForUpdates()
    {
        try {
            $currentVersion = self::getCurrentVersion();
            
            // 从缓存中获取更新信息
            $updates = Cache::remember('available_updates', 60, function () use ($currentVersion) {
                $response = Http::get(self::$updateServerUrl, [
                    'current_version' => $currentVersion,
                ]);
                
                if ($response->successful()) {
                    return $response->json();
                }
                
                return [];
            });
            
            if (empty($updates)) {
                return [];
            }
            
            // 过滤已安装的更新
            $installedVersions = self::where('is_installed', true)->pluck('version')->toArray();
            
            $availableUpdates = [];
            
            foreach ($updates as $update) {
                if (!in_array($update['version'], $installedVersions) && version_compare($update['version'], $currentVersion, '>')) {
                    $availableUpdates[] = $update;
                }
            }
            
            // 按版本排序
            usort($availableUpdates, function ($a, $b) {
                return version_compare($a['version'], $b['version']);
            });
            
            return $availableUpdates;
        } catch (\Exception $e) {
            // 记录错误日志
            ActivityLog::logSystem(
                "检查更新失败: {$e->getMessage()}", 
                [], 
                ActivityLog::TYPE_ERROR
            );
            
            return [];
        }
    }

    // 下载更新
    public static function downloadUpdate($version)
    {
        try {
            // 检查更新是否存在
            $updates = self::checkForUpdates();
            
            $updateInfo = null;
            
            foreach ($updates as $update) {
                if ($update['version'] === $version) {
                    $updateInfo = $update;
                    break;
                }
            }
            
            if (!$updateInfo) {
                throw new \Exception('更新不存在');
            }
            
            // 检查版本要求
            $currentVersion = self::getCurrentVersion();
            
            if (isset($updateInfo['requires_version']) && version_compare($currentVersion, $updateInfo['requires_version'], '<')) {
                throw new \Exception("需要先更新到版本 {$updateInfo['requires_version']}");
            }
            
            // 下载更新
            $response = Http::get($updateInfo['download_url']);
            
            if (!$response->successful()) {
                throw new \Exception('下载更新失败');
            }
            
            // 保存更新文件
            $updateDir = storage_path('app/updates');
            
            if (!File::exists($updateDir)) {
                File::makeDirectory($updateDir, 0755, true);
            }
            
            $updateFile = $updateDir . "/update_{$version}.zip";
            File::put($updateFile, $response->body());
            
            // 验证校验和
            if (isset($updateInfo['checksum'])) {
                $checksum = hash_file('sha256', $updateFile);
                
                if ($checksum !== $updateInfo['checksum']) {
                    File::delete($updateFile);
                    throw new \Exception('更新文件校验失败');
                }
            }
            
            // 创建或更新记录
            $update = self::updateOrCreate(
                ['version' => $version],
                [
                    'description' => $updateInfo['description'] ?? '',
                    'download_url' => $updateInfo['download_url'],
                    'release_notes' => $updateInfo['release_notes'] ?? '',
                    'is_installed' => false,
                    'size' => File::size($updateFile),
                    'checksum' => $updateInfo['checksum'] ?? '',
                    'requires_version' => $updateInfo['requires_version'] ?? null,
                ]
            );
            
            // 记录日志
            ActivityLog::logSystem(
                "下载更新 [{$version}] 成功", 
                ['size' => File::size($updateFile)], 
                ActivityLog::TYPE_SUCCESS
            );
            
            return $update;
        } catch (\Exception $e) {
            // 记录错误日志
            ActivityLog::logSystem(
                "下载更新 [{$version}] 失败: {$e->getMessage()}", 
                [], 
                ActivityLog::TYPE_ERROR
            );
            
            throw $e;
        }
    }

    // 安装更新
    public function install()
    {
        try {
            // 检查更新文件是否存在
            $updateFile = storage_path("app/updates/update_{$this->version}.zip");
            
            if (!File::exists($updateFile)) {
                throw new \Exception('更新文件不存在');
            }
            
            // 创建备份
            $backup = Backup::createBackup(Backup::TYPE_FULL, "更新到版本 {$this->version} 前的备份");
            
            // 创建临时目录
            $tempDir = storage_path("app/temp/update_{$this->version}");
            
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            
            File::makeDirectory($tempDir, 0755, true);
            
            // 解压更新文件
            $zip = new ZipArchive();
            
            if ($zip->open($updateFile) !== true) {
                throw new \Exception('无法打开更新文件');
            }
            
            $zip->extractTo($tempDir);
            $zip->close();
            
            // 检查更新配置文件
            $updateConfig = $tempDir . '/update.json';
            
            if (!File::exists($updateConfig)) {
                throw new \Exception('更新配置文件不存在');
            }
            
            $config = json_decode(File::get($updateConfig), true);
            
            if (!$config || !isset($config['version']) || $config['version'] !== $this->version) {
                throw new \Exception('更新配置文件无效');
            }
            
            // 执行更新前脚本
            if (isset($config['pre_update_script']) && File::exists($tempDir . '/' . $config['pre_update_script'])) {
                include $tempDir . '/' . $config['pre_update_script'];
                
                if (function_exists('preUpdate')) {
                    preUpdate();
                }
            }
            
            // 复制文件
            if (isset($config['files']) && is_array($config['files'])) {
                foreach ($config['files'] as $file) {
                    $source = $tempDir . '/' . $file['source'];
                    $destination = base_path($file['destination']);
                    
                    // 确保目标目录存在
                    $destinationDir = dirname($destination);
                    
                    if (!File::exists($destinationDir)) {
                        File::makeDirectory($destinationDir, 0755, true);
                    }
                    
                    // 复制文件
                    if (File::exists($source)) {
                        File::copy($source, $destination, true);
                    }
                }
            }
            
            // 删除文件
            if (isset($config['delete_files']) && is_array($config['delete_files'])) {
                foreach ($config['delete_files'] as $file) {
                    $path = base_path($file);
                    
                    if (File::exists($path)) {
                        if (File::isDirectory($path)) {
                            File::deleteDirectory($path);
                        } else {
                            File::delete($path);
                        }
                    }
                }
            }
            
            // 运行迁移
            if (isset($config['run_migrations']) && $config['run_migrations']) {
                Artisan::call('migrate', ['--force' => true]);
            }
            
            // 运行种子
            if (isset($config['run_seeders']) && is_array($config['run_seeders'])) {
                foreach ($config['run_seeders'] as $seeder) {
                    Artisan::call('db:seed', [
                        '--class' => $seeder,
                        '--force' => true,
                    ]);
                }
            }
            
            // 执行更新后脚本
            if (isset($config['post_update_script']) && File::exists($tempDir . '/' . $config['post_update_script'])) {
                include $tempDir . '/' . $config['post_update_script'];
                
                if (function_exists('postUpdate')) {
                    postUpdate();
                }
            }
            
            // 更新版本号
            Setting::set('app_version', $this->version);
            
            // 更新记录
            $this->is_installed = true;
            $this->installed_at = now();
            $this->save();
            
            // 清理临时目录
            File::deleteDirectory($tempDir);
            
            // 记录日志
            ActivityLog::logSystem(
                "安装更新 [{$this->version}] 成功", 
                [], 
                ActivityLog::TYPE_SUCCESS
            );
            
            return true;
        } catch (\Exception $e) {
            // 记录错误日志
            ActivityLog::logSystem(
                "安装更新 [{$this->version}] 失败: {$e->getMessage()}", 
                [], 
                ActivityLog::TYPE_ERROR
            );
            
            throw $e;
        }
    }

    // 回滚更新
    public function rollback()
    {
        try {
            // 查找更新前的备份
            $backup = Backup::where('description', "更新到版本 {$this->version} 前的备份")
                ->orderBy('created_at', 'desc')
                ->first();
            
            if (!$backup) {
                throw new \Exception('未找到更新前的备份');
            }
            
            // 恢复备份
            $backup->restore();
            
            // 更新记录
            $this->is_installed = false;
            $this->installed_at = null;
            $this->save();
            
            // 记录日志
            ActivityLog::logSystem(
                "回滚更新 [{$this->version}]", 
                [], 
                ActivityLog::TYPE_SUCCESS
            );
            
            return true;
        } catch (\Exception $e) {
            // 记录错误日志
            ActivityLog::logSystem(
                "回滚更新 [{$this->version}] 失败: {$e->getMessage()}", 
                [], 
                ActivityLog::TYPE_ERROR
            );
            
            throw $e;
        }
    }

    // 清理更新文件
    public static function cleanupUpdateFiles()
    {
        $updateDir = storage_path('app/updates');
        
        if (!File::exists($updateDir)) {
            return 0;
        }
        
        $files = File::files($updateDir);
        $count = count($files);
        
        foreach ($files as $file) {
            File::delete($file);
        }
        
        return $count;
    }

    // 获取更新历史
    public static function getUpdateHistory()
    {
        return self::where('is_installed', true)
            ->orderBy('installed_at', 'desc')
            ->get();
    }
}
