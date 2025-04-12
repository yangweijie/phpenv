<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\File;
use ZipArchive;

class RedisInstaller
{
    // Redis下载URL
    protected $downloadUrl = 'https://github.com/microsoftarchive/redis/releases/download/win-3.2.100/Redis-x64-3.2.100.zip';
    
    // 安装目录
    protected $installDir;
    
    // 临时目录
    protected $tempDir;
    
    // 配置模板
    protected $configTemplate = <<<'CONFIG'
# Redis配置文件
port {port}
bind 127.0.0.1
protected-mode yes
daemonize no
pidfile {pidfile}
logfile {logfile}
dir {dir}
dbfilename dump.rdb
appendonly yes
appendfilename "appendonly.aof"
appendfsync everysec
CONFIG;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->installDir = base_path('services/redis');
        $this->tempDir = storage_path('app/temp/redis');
    }
    
    /**
     * 安装Redis
     */
    public function install($port = 6379)
    {
        try {
            // 创建安装目录
            if (!File::exists($this->installDir)) {
                File::makeDirectory($this->installDir, 0755, true);
            }
            
            // 创建临时目录
            if (!File::exists($this->tempDir)) {
                File::makeDirectory($this->tempDir, 0755, true);
            }
            
            // 下载Redis
            $zipFile = $this->tempDir . '/redis.zip';
            $this->downloadRedis($zipFile);
            
            // 解压Redis
            $this->extractRedis($zipFile);
            
            // 创建配置文件
            $configFile = $this->createConfig($port);
            
            // 创建服务记录
            $service = $this->createServiceRecord($port, $configFile);
            
            // 清理临时文件
            File::delete($zipFile);
            
            // 记录日志
            ActivityLog::logSystem(
                "Redis服务安装成功", 
                ['port' => $port], 
                ActivityLog::TYPE_SUCCESS
            );
            
            return $service;
        } catch (\Exception $e) {
            // 记录错误日志
            ActivityLog::logSystem(
                "Redis服务安装失败: {$e->getMessage()}", 
                [], 
                ActivityLog::TYPE_ERROR
            );
            
            throw $e;
        }
    }
    
    /**
     * 下载Redis
     */
    protected function downloadRedis($zipFile)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->downloadUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        
        if (curl_errno($ch)) {
            throw new \Exception('下载Redis失败: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        File::put($zipFile, $data);
        
        if (!File::exists($zipFile)) {
            throw new \Exception('下载Redis失败: 文件未创建');
        }
    }
    
    /**
     * 解压Redis
     */
    protected function extractRedis($zipFile)
    {
        $zip = new ZipArchive();
        
        if ($zip->open($zipFile) !== true) {
            throw new \Exception('解压Redis失败: 无法打开ZIP文件');
        }
        
        $zip->extractTo($this->tempDir);
        $zip->close();
        
        // 复制文件到安装目录
        $files = File::files($this->tempDir);
        
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'exe' || pathinfo($file, PATHINFO_EXTENSION) === 'conf') {
                File::copy($file, $this->installDir . '/' . basename($file));
            }
        }
    }
    
    /**
     * 创建配置文件
     */
    protected function createConfig($port)
    {
        $configFile = $this->installDir . '/redis.conf';
        
        $config = str_replace(
            ['{port}', '{pidfile}', '{logfile}', '{dir}'],
            [$port, $this->installDir . '/redis.pid', $this->installDir . '/redis.log', $this->installDir],
            $this->configTemplate
        );
        
        File::put($configFile, $config);
        
        return $configFile;
    }
    
    /**
     * 创建服务记录
     */
    protected function createServiceRecord($port, $configFile)
    {
        return Service::create([
            'name' => 'Redis',
            'type' => Service::TYPE_REDIS,
            'path' => $this->installDir . '/redis-server.exe',
            'config_path' => $configFile,
            'port' => $port,
            'status' => false,
            'auto_start' => false,
        ]);
    }
    
    /**
     * 卸载Redis
     */
    public function uninstall(Service $service)
    {
        try {
            // 停止服务
            if ($service->status) {
                $service->stop();
            }
            
            // 删除安装目录
            if (File::exists($this->installDir)) {
                File::deleteDirectory($this->installDir);
            }
            
            // 删除服务记录
            $service->delete();
            
            // 记录日志
            ActivityLog::logSystem(
                "Redis服务卸载成功", 
                [], 
                ActivityLog::TYPE_SUCCESS
            );
            
            return true;
        } catch (\Exception $e) {
            // 记录错误日志
            ActivityLog::logSystem(
                "Redis服务卸载失败: {$e->getMessage()}", 
                [], 
                ActivityLog::TYPE_ERROR
            );
            
            throw $e;
        }
    }
}
