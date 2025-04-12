<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\File;
use ZipArchive;

class RabbitMQInstaller
{
    // RabbitMQ下载URL
    protected $downloadUrl = 'https://github.com/rabbitmq/rabbitmq-server/releases/download/v3.12.0/rabbitmq-server-windows-3.12.0.zip';
    
    // Erlang下载URL
    protected $erlangDownloadUrl = 'https://github.com/erlang/otp/releases/download/OTP-25.3.2/otp_win64_25.3.2.exe';
    
    // 安装目录
    protected $installDir;
    
    // 临时目录
    protected $tempDir;
    
    // 配置模板
    protected $configTemplate = <<<'CONFIG'
[
  {rabbit, [
    {tcp_listeners, [{"::", %PORT%}]},
    {loopback_users, []},
    {default_user, <<"guest">>},
    {default_pass, <<"guest">>},
    {default_vhost, <<"/">>},
    {default_permissions, [<<".*">>, <<".*">>, <<".*">>]},
    {log_levels, [{connection, info}, {channel, info}, {queue, info}]},
    {disk_free_limit, 50000000}
  ]},
  {rabbitmq_management, [
    {listener, [{port, %MGMT_PORT%}]}
  ]}
].
CONFIG;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->installDir = base_path('services/rabbitmq');
        $this->tempDir = storage_path('app/temp/rabbitmq');
    }
    
    /**
     * 安装RabbitMQ
     */
    public function install($port = 5672, $managementPort = 15672)
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
            
            // 检查Erlang是否已安装
            if (!$this->isErlangInstalled()) {
                // 下载并安装Erlang
                $this->installErlang();
            }
            
            // 下载RabbitMQ
            $zipFile = $this->tempDir . '/rabbitmq.zip';
            $this->downloadRabbitMQ($zipFile);
            
            // 解压RabbitMQ
            $this->extractRabbitMQ($zipFile);
            
            // 创建配置文件
            $configFile = $this->createConfig($port, $managementPort);
            
            // 启用管理插件
            $this->enableManagementPlugin();
            
            // 创建服务记录
            $service = $this->createServiceRecord($port, $configFile);
            
            // 清理临时文件
            File::delete($zipFile);
            
            // 记录日志
            ActivityLog::logSystem(
                "RabbitMQ服务安装成功", 
                ['port' => $port, 'management_port' => $managementPort], 
                ActivityLog::TYPE_SUCCESS
            );
            
            return $service;
        } catch (\Exception $e) {
            // 记录错误日志
            ActivityLog::logSystem(
                "RabbitMQ服务安装失败: {$e->getMessage()}", 
                [], 
                ActivityLog::TYPE_ERROR
            );
            
            throw $e;
        }
    }
    
    /**
     * 检查Erlang是否已安装
     */
    protected function isErlangInstalled()
    {
        $result = shell_exec('where erl 2>&1');
        return strpos($result, 'erl.exe') !== false;
    }
    
    /**
     * 安装Erlang
     */
    protected function installErlang()
    {
        // 下载Erlang安装程序
        $erlangInstaller = $this->tempDir . '/erlang_installer.exe';
        $this->downloadFile($this->erlangDownloadUrl, $erlangInstaller);
        
        // 安装Erlang
        $result = shell_exec('"' . $erlangInstaller . '" /S');
        
        // 等待安装完成
        sleep(30);
        
        // 检查安装是否成功
        if (!$this->isErlangInstalled()) {
            throw new \Exception('Erlang安装失败');
        }
        
        // 删除安装程序
        File::delete($erlangInstaller);
    }
    
    /**
     * 下载RabbitMQ
     */
    protected function downloadRabbitMQ($zipFile)
    {
        $this->downloadFile($this->downloadUrl, $zipFile);
    }
    
    /**
     * 下载文件
     */
    protected function downloadFile($url, $destination)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        
        if (curl_errno($ch)) {
            throw new \Exception('下载失败: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        File::put($destination, $data);
        
        if (!File::exists($destination)) {
            throw new \Exception('下载失败: 文件未创建');
        }
    }
    
    /**
     * 解压RabbitMQ
     */
    protected function extractRabbitMQ($zipFile)
    {
        $zip = new ZipArchive();
        
        if ($zip->open($zipFile) !== true) {
            throw new \Exception('解压RabbitMQ失败: 无法打开ZIP文件');
        }
        
        $zip->extractTo($this->tempDir);
        $zip->close();
        
        // 找到解压后的RabbitMQ目录
        $directories = File::directories($this->tempDir);
        $rabbitMQDir = null;
        
        foreach ($directories as $dir) {
            if (strpos(basename($dir), 'rabbitmq') !== false) {
                $rabbitMQDir = $dir;
                break;
            }
        }
        
        if (!$rabbitMQDir) {
            throw new \Exception('解压RabbitMQ失败: 未找到RabbitMQ目录');
        }
        
        // 复制文件到安装目录
        File::copyDirectory($rabbitMQDir, $this->installDir);
    }
    
    /**
     * 创建配置文件
     */
    protected function createConfig($port, $managementPort)
    {
        $configDir = $this->installDir . '/etc/rabbitmq';
        
        if (!File::exists($configDir)) {
            File::makeDirectory($configDir, 0755, true);
        }
        
        $configFile = $configDir . '/rabbitmq.conf';
        
        $config = str_replace(
            ['%PORT%', '%MGMT_PORT%'],
            [$port, $managementPort],
            $this->configTemplate
        );
        
        File::put($configFile, $config);
        
        return $configFile;
    }
    
    /**
     * 启用管理插件
     */
    protected function enableManagementPlugin()
    {
        $rabbitctlPath = $this->installDir . '/sbin/rabbitmq-plugins.bat';
        
        if (!File::exists($rabbitctlPath)) {
            throw new \Exception('未找到rabbitmq-plugins.bat');
        }
        
        $command = 'cd ' . $this->installDir . '/sbin && rabbitmq-plugins.bat enable rabbitmq_management';
        $result = shell_exec($command);
        
        if (strpos($result, 'Error') !== false) {
            throw new \Exception('启用管理插件失败: ' . $result);
        }
    }
    
    /**
     * 创建服务记录
     */
    protected function createServiceRecord($port, $configFile)
    {
        return Service::create([
            'name' => 'RabbitMQ',
            'type' => Service::TYPE_RABBITMQ,
            'path' => $this->installDir . '/sbin/rabbitmq-server.bat',
            'config_path' => $configFile,
            'port' => $port,
            'status' => false,
            'auto_start' => false,
        ]);
    }
    
    /**
     * 卸载RabbitMQ
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
                "RabbitMQ服务卸载成功", 
                [], 
                ActivityLog::TYPE_SUCCESS
            );
            
            return true;
        } catch (\Exception $e) {
            // 记录错误日志
            ActivityLog::logSystem(
                "RabbitMQ服务卸载失败: {$e->getMessage()}", 
                [], 
                ActivityLog::TYPE_ERROR
            );
            
            throw $e;
        }
    }
}
