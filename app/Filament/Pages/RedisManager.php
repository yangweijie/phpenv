<?php

namespace App\Filament\Pages;

use App\Models\Service;
use App\Services\RedisInstaller;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\File;

class RedisManager extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-server';
    
    protected static ?string $navigationLabel = 'Redis管理';
    
    protected static ?string $navigationGroup = '服务管理';
    
    protected static ?int $navigationSort = 25;

    protected static string $view = 'filament.pages.redis-manager';
    
    public $redisService = null;
    public $isInstalled = false;
    public $isRunning = false;
    public $redisInfo = [];
    public $redisStats = [];
    public $redisClients = [];
    public $redisMemory = [];
    public $redisKeyspace = [];
    public $redisConfig = [];
    
    public $commandInput = '';
    public $commandOutput = '';
    
    public $newPort = 6379;
    public $maxMemory = 128;
    public $maxClients = 100;
    public $databases = 16;
    
    public function mount(): void
    {
        $this->loadRedisService();
    }
    
    public function loadRedisService(): void
    {
        $this->redisService = Service::where('type', Service::TYPE_REDIS)->first();
        $this->isInstalled = $this->redisService !== null;
        $this->isRunning = $this->isInstalled && $this->redisService->status;
        
        if ($this->isRunning) {
            $this->loadRedisInfo();
        }
    }
    
    public function loadRedisInfo(): void
    {
        try {
            if (!$this->isRunning) {
                return;
            }
            
            $redisCliPath = dirname($this->redisService->path) . '/redis-cli.exe';
            $port = $this->redisService->port;
            
            // 获取Redis信息
            $infoOutput = $this->executeRedisCommand($redisCliPath, $port, 'INFO');
            $this->parseRedisInfo($infoOutput);
            
            // 获取Redis配置
            $configOutput = $this->executeRedisCommand($redisCliPath, $port, 'CONFIG GET *');
            $this->parseRedisConfig($configOutput);
        } catch (\Exception $e) {
            Notification::make()
                ->title('获取Redis信息失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    protected function executeRedisCommand($redisCliPath, $port, $command): string
    {
        $output = shell_exec('"' . $redisCliPath . '" -p ' . $port . ' ' . $command);
        return $output ?: '';
    }
    
    protected function parseRedisInfo($infoOutput): void
    {
        $sections = [
            'server' => &$this->redisInfo,
            'stats' => &$this->redisStats,
            'clients' => &$this->redisClients,
            'memory' => &$this->redisMemory,
            'keyspace' => &$this->redisKeyspace,
        ];
        
        $currentSection = null;
        
        foreach (explode("\n", $infoOutput) as $line) {
            $line = trim($line);
            
            if (empty($line) || $line[0] === '#') {
                $sectionName = trim(substr($line, 1));
                $currentSection = strtolower($sectionName);
                continue;
            }
            
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                
                if (isset($sections[$currentSection])) {
                    $sections[$currentSection][$key] = $value;
                }
            }
        }
    }
    
    protected function parseRedisConfig($configOutput): void
    {
        $lines = explode("\n", $configOutput);
        $count = count($lines);
        
        for ($i = 0; $i < $count; $i += 2) {
            if (isset($lines[$i]) && isset($lines[$i + 1])) {
                $key = trim($lines[$i]);
                $value = trim($lines[$i + 1]);
                
                if (!empty($key)) {
                    $this->redisConfig[$key] = $value;
                }
            }
        }
    }
    
    public function installRedis(): void
    {
        try {
            $installer = new RedisInstaller();
            $this->redisService = $installer->install($this->newPort);
            
            Notification::make()
                ->title('Redis安装成功')
                ->success()
                ->send();
                
            $this->loadRedisService();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Redis安装失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function uninstallRedis(): void
    {
        try {
            if (!$this->isInstalled) {
                return;
            }
            
            $installer = new RedisInstaller();
            $installer->uninstall($this->redisService);
            
            Notification::make()
                ->title('Redis卸载成功')
                ->success()
                ->send();
                
            $this->redisService = null;
            $this->isInstalled = false;
            $this->isRunning = false;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Redis卸载失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function startRedis(): void
    {
        try {
            if (!$this->isInstalled || $this->isRunning) {
                return;
            }
            
            $result = $this->redisService->start();
            
            if ($result) {
                Notification::make()
                    ->title('Redis启动成功')
                    ->success()
                    ->send();
                    
                $this->isRunning = true;
                $this->loadRedisInfo();
            } else {
                Notification::make()
                    ->title('Redis启动失败')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Redis启动失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function stopRedis(): void
    {
        try {
            if (!$this->isInstalled || !$this->isRunning) {
                return;
            }
            
            $result = $this->redisService->stop();
            
            if ($result) {
                Notification::make()
                    ->title('Redis停止成功')
                    ->success()
                    ->send();
                    
                $this->isRunning = false;
            } else {
                Notification::make()
                    ->title('Redis停止失败')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Redis停止失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function restartRedis(): void
    {
        try {
            if (!$this->isInstalled) {
                return;
            }
            
            $result = $this->redisService->restart();
            
            if ($result) {
                Notification::make()
                    ->title('Redis重启成功')
                    ->success()
                    ->send();
                    
                $this->isRunning = true;
                $this->loadRedisInfo();
            } else {
                Notification::make()
                    ->title('Redis重启失败')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Redis重启失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function executeCommand(): void
    {
        try {
            if (!$this->isInstalled || !$this->isRunning || empty($this->commandInput)) {
                return;
            }
            
            $redisCliPath = dirname($this->redisService->path) . '/redis-cli.exe';
            $port = $this->redisService->port;
            
            $this->commandOutput = $this->executeRedisCommand($redisCliPath, $port, $this->commandInput);
            $this->commandInput = '';
            
            // 如果命令可能改变了Redis状态，重新加载信息
            $this->loadRedisInfo();
        } catch (\Exception $e) {
            Notification::make()
                ->title('执行命令失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function updateConfig(): void
    {
        try {
            if (!$this->isInstalled) {
                return;
            }
            
            // 读取配置文件
            $configFile = $this->redisService->config_path;
            $config = File::get($configFile);
            
            // 更新配置
            $config = preg_replace('/port\s+\d+/', 'port ' . $this->newPort, $config);
            $config = preg_replace('/maxmemory\s+\d+mb/', 'maxmemory ' . $this->maxMemory . 'mb', $config);
            
            if (!preg_match('/maxmemory\s+\d+mb/', $config)) {
                $config .= "\nmaxmemory " . $this->maxMemory . "mb";
            }
            
            $config = preg_replace('/maxclients\s+\d+/', 'maxclients ' . $this->maxClients, $config);
            
            if (!preg_match('/maxclients\s+\d+/', $config)) {
                $config .= "\nmaxclients " . $this->maxClients;
            }
            
            $config = preg_replace('/databases\s+\d+/', 'databases ' . $this->databases, $config);
            
            if (!preg_match('/databases\s+\d+/', $config)) {
                $config .= "\ndatabases " . $this->databases;
            }
            
            // 保存配置
            File::put($configFile, $config);
            
            // 更新服务记录
            $this->redisService->port = $this->newPort;
            $this->redisService->save();
            
            Notification::make()
                ->title('Redis配置更新成功')
                ->body('需要重启Redis服务以应用新配置')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('更新Redis配置失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function flushDB(): void
    {
        try {
            if (!$this->isInstalled || !$this->isRunning) {
                return;
            }
            
            $redisCliPath = dirname($this->redisService->path) . '/redis-cli.exe';
            $port = $this->redisService->port;
            
            $result = $this->executeRedisCommand($redisCliPath, $port, 'FLUSHDB');
            
            if (strpos($result, 'OK') !== false) {
                Notification::make()
                    ->title('清空当前数据库成功')
                    ->success()
                    ->send();
                    
                $this->loadRedisInfo();
            } else {
                Notification::make()
                    ->title('清空当前数据库失败')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('清空当前数据库失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function flushAll(): void
    {
        try {
            if (!$this->isInstalled || !$this->isRunning) {
                return;
            }
            
            $redisCliPath = dirname($this->redisService->path) . '/redis-cli.exe';
            $port = $this->redisService->port;
            
            $result = $this->executeRedisCommand($redisCliPath, $port, 'FLUSHALL');
            
            if (strpos($result, 'OK') !== false) {
                Notification::make()
                    ->title('清空所有数据库成功')
                    ->success()
                    ->send();
                    
                $this->loadRedisInfo();
            } else {
                Notification::make()
                    ->title('清空所有数据库失败')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('清空所有数据库失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
