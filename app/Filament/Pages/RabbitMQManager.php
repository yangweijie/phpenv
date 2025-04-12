<?php

namespace App\Filament\Pages;

use App\Models\Service;
use App\Services\RabbitMQInstaller;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class RabbitMQManager extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationLabel = 'RabbitMQ管理';

    protected static ?string $navigationGroup = '服务管理';

    protected static ?int $navigationSort = 26;

    protected static string $view = 'filament.pages.rabbit-m-q-manager';

    public $rabbitMQService = null;
    public $isInstalled = false;
    public $isRunning = false;
    public $overview = [];
    public $nodes = [];
    public $queues = [];
    public $exchanges = [];
    public $connections = [];
    public $bindings = [];
    public $channelStats = [];
    public $queueStats = [];

    // 消息测试工具变量
    public $testExchange = '';
    public $testQueue = '';
    public $testRoutingKey = '';
    public $testMessageContent = '';
    public $testMessageProperties = [];
    public $receivedMessages = [];

    public $newPort = 5672;
    public $managementPort = 15672;
    public $memoryLimit = 512;
    public $diskFreeLimit = 1024;

    public function mount(): void
    {
        $this->loadRabbitMQService();
    }

    public function loadRabbitMQService(): void
    {
        $this->rabbitMQService = Service::where('type', Service::TYPE_RABBITMQ)->first();
        $this->isInstalled = $this->rabbitMQService !== null;
        $this->isRunning = $this->isInstalled && $this->rabbitMQService->status;

        if ($this->isRunning) {
            $this->loadRabbitMQInfo();
        }
    }

    public function loadRabbitMQInfo(): void
    {
        try {
            if (!$this->isRunning) {
                return;
            }

            $this->managementPort = $this->rabbitMQService->port + 10000;

            // 获取概览信息
            $this->overview = $this->fetchApiData('overview');

            // 获取节点信息
            $this->nodes = $this->fetchApiData('nodes');

            // 获取队列信息
            $this->queues = $this->fetchApiData('queues');

            // 获取交换器信息
            $this->exchanges = $this->fetchApiData('exchanges');

            // 获取连接信息
            $this->connections = $this->fetchApiData('connections');

            // 获取绑定信息
            $this->bindings = $this->fetchApiData('bindings');

            // 获取通道统计信息
            $this->channelStats = $this->fetchApiData('channels');

            // 获取队列详细统计信息
            $this->queueStats = [];
            foreach ($this->queues as $queue) {
                if (isset($queue['name'])) {
                    $encodedName = urlencode($queue['name']);
                    $queueStats = $this->fetchApiData("queues/%2F/{$encodedName}");
                    if (!empty($queueStats)) {
                        $this->queueStats[$queue['name']] = $queueStats;
                    }
                }
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('获取RabbitMQ信息失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function fetchApiData($endpoint): array
    {
        try {
            $response = Http::withBasicAuth('guest', 'guest')
                ->timeout(5)
                ->get("http://localhost:{$this->managementPort}/api/{$endpoint}");

            if ($response->successful()) {
                return $response->json() ?: [];
            }
        } catch (\Exception $e) {
            // 忽略错误
        }

        return [];
    }

    public function installRabbitMQ(): void
    {
        try {
            $installer = new RabbitMQInstaller();
            $this->rabbitMQService = $installer->install($this->newPort, $this->managementPort);

            Notification::make()
                ->title('RabbitMQ安装成功')
                ->success()
                ->send();

            $this->loadRabbitMQService();
        } catch (\Exception $e) {
            Notification::make()
                ->title('RabbitMQ安装失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function uninstallRabbitMQ(): void
    {
        try {
            if (!$this->isInstalled) {
                return;
            }

            $installer = new RabbitMQInstaller();
            $installer->uninstall($this->rabbitMQService);

            Notification::make()
                ->title('RabbitMQ卸载成功')
                ->success()
                ->send();

            $this->rabbitMQService = null;
            $this->isInstalled = false;
            $this->isRunning = false;
        } catch (\Exception $e) {
            Notification::make()
                ->title('RabbitMQ卸载失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function startRabbitMQ(): void
    {
        try {
            if (!$this->isInstalled || $this->isRunning) {
                return;
            }

            $result = $this->rabbitMQService->start();

            if ($result) {
                Notification::make()
                    ->title('RabbitMQ启动成功')
                    ->success()
                    ->send();

                $this->isRunning = true;
                // 等待服务完全启动
                sleep(5);
                $this->loadRabbitMQInfo();
            } else {
                Notification::make()
                    ->title('RabbitMQ启动失败')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('RabbitMQ启动失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function stopRabbitMQ(): void
    {
        try {
            if (!$this->isInstalled || !$this->isRunning) {
                return;
            }

            $result = $this->rabbitMQService->stop();

            if ($result) {
                Notification::make()
                    ->title('RabbitMQ停止成功')
                    ->success()
                    ->send();

                $this->isRunning = false;
            } else {
                Notification::make()
                    ->title('RabbitMQ停止失败')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('RabbitMQ停止失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function restartRabbitMQ(): void
    {
        try {
            if (!$this->isInstalled) {
                return;
            }

            $result = $this->rabbitMQService->restart();

            if ($result) {
                Notification::make()
                    ->title('RabbitMQ重启成功')
                    ->success()
                    ->send();

                $this->isRunning = true;
                // 等待服务完全启动
                sleep(5);
                $this->loadRabbitMQInfo();
            } else {
                Notification::make()
                    ->title('RabbitMQ重启失败')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('RabbitMQ重启失败')
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
            $configFile = $this->rabbitMQService->config_path;
            $config = File::get($configFile);

            // 更新配置
            $config = preg_replace('/%PORT%/', $this->newPort, $config);
            $config = preg_replace('/%MGMT_PORT%/', $this->managementPort, $config);

            // 更新内存限制
            if (preg_match('/\{disk_free_limit,\s*\d+\}/', $config)) {
                $config = preg_replace('/\{disk_free_limit,\s*\d+\}/', '{disk_free_limit, ' . ($this->diskFreeLimit * 1024 * 1024) . '}', $config);
            } else {
                $config = str_replace('{disk_free_limit, 50000000}', '{disk_free_limit, ' . ($this->diskFreeLimit * 1024 * 1024) . '}', $config);
            }

            // 保存配置
            File::put($configFile, $config);

            // 更新服务记录
            $this->rabbitMQService->port = $this->newPort;
            $this->rabbitMQService->save();

            Notification::make()
                ->title('RabbitMQ配置更新成功')
                ->body('需要重启RabbitMQ服务以应用新配置')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('更新RabbitMQ配置失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function createQueue($name, $durable = true, $autoDelete = false): void
    {
        try {
            if (!$this->isRunning || empty($name)) {
                return;
            }

            $response = Http::withBasicAuth('guest', 'guest')
                ->put("http://localhost:{$this->managementPort}/api/queues/%2F/{$name}", [
                    'durable' => $durable,
                    'auto_delete' => $autoDelete,
                    'arguments' => []
                ]);

            if ($response->successful()) {
                Notification::make()
                    ->title('创建队列成功')
                    ->success()
                    ->send();

                $this->loadRabbitMQInfo();
            } else {
                Notification::make()
                    ->title('创建队列失败')
                    ->body($response->body())
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('创建队列失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function deleteQueue($name): void
    {
        try {
            if (!$this->isRunning || empty($name)) {
                return;
            }

            $response = Http::withBasicAuth('guest', 'guest')
                ->delete("http://localhost:{$this->managementPort}/api/queues/%2F/{$name}");

            if ($response->successful()) {
                Notification::make()
                    ->title('删除队列成功')
                    ->success()
                    ->send();

                $this->loadRabbitMQInfo();
            } else {
                Notification::make()
                    ->title('删除队列失败')
                    ->body($response->body())
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('删除队列失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function purgeQueue($name): void
    {
        try {
            if (!$this->isRunning || empty($name)) {
                return;
            }

            $response = Http::withBasicAuth('guest', 'guest')
                ->delete("http://localhost:{$this->managementPort}/api/queues/%2F/{$name}/contents");

            if ($response->successful()) {
                Notification::make()
                    ->title('清空队列成功')
                    ->success()
                    ->send();

                $this->loadRabbitMQInfo();
            } else {
                Notification::make()
                    ->title('清空队列失败')
                    ->body($response->body())
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('清空队列失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // 交换器管理方法
    public function createExchange($name, $type = 'direct', $durable = true, $autoDelete = false): void
    {
        try {
            if (!$this->isRunning || empty($name)) {
                return;
            }

            $response = Http::withBasicAuth('guest', 'guest')
                ->put("http://localhost:{$this->managementPort}/api/exchanges/%2F/{$name}", [
                    'type' => $type,
                    'durable' => $durable,
                    'auto_delete' => $autoDelete,
                    'internal' => false,
                    'arguments' => []
                ]);

            if ($response->successful()) {
                Notification::make()
                    ->title('创建交换器成功')
                    ->success()
                    ->send();

                $this->loadRabbitMQInfo();
            } else {
                Notification::make()
                    ->title('创建交换器失败')
                    ->body($response->body())
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('创建交换器失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function deleteExchange($name): void
    {
        try {
            if (!$this->isRunning || empty($name) || $name === 'amq.default') {
                return;
            }

            // 默认交换器和系统交换器不能删除
            if (strpos($name, 'amq.') === 0) {
                Notification::make()
                    ->title('无法删除系统交换器')
                    ->danger()
                    ->send();
                return;
            }

            $response = Http::withBasicAuth('guest', 'guest')
                ->delete("http://localhost:{$this->managementPort}/api/exchanges/%2F/{$name}");

            if ($response->successful()) {
                Notification::make()
                    ->title('删除交换器成功')
                    ->success()
                    ->send();

                $this->loadRabbitMQInfo();
            } else {
                Notification::make()
                    ->title('删除交换器失败')
                    ->body($response->body())
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('删除交换器失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // 绑定管理方法
    public function createBinding($source, $destination, $routingKey = '', $sourceType = 'exchange', $destinationType = 'queue'): void
    {
        try {
            if (!$this->isRunning || empty($source) || empty($destination)) {
                return;
            }

            $response = Http::withBasicAuth('guest', 'guest')
                ->post("http://localhost:{$this->managementPort}/api/bindings/%2F/e/{$source}/q/{$destination}", [
                    'routing_key' => $routingKey,
                    'arguments' => []
                ]);

            if ($response->successful()) {
                Notification::make()
                    ->title('创建绑定成功')
                    ->success()
                    ->send();

                $this->loadRabbitMQInfo();
            } else {
                Notification::make()
                    ->title('创建绑定失败')
                    ->body($response->body())
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('创建绑定失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function deleteBinding($source, $destination, $routingKey = '', $propertiesKey = '~'): void
    {
        try {
            if (!$this->isRunning || empty($source) || empty($destination)) {
                return;
            }

            $response = Http::withBasicAuth('guest', 'guest')
                ->delete("http://localhost:{$this->managementPort}/api/bindings/%2F/e/{$source}/q/{$destination}/{$propertiesKey}");

            if ($response->successful()) {
                Notification::make()
                    ->title('删除绑定成功')
                    ->success()
                    ->send();

                $this->loadRabbitMQInfo();
            } else {
                Notification::make()
                    ->title('删除绑定失败')
                    ->body($response->body())
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('删除绑定失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // 消息测试工具方法
    public function sendTestMessage(): void
    {
        try {
            if (!$this->isRunning || empty($this->testExchange) || empty($this->testMessageContent)) {
                return;
            }

            // 构建消息属性
            $properties = [
                'content_type' => 'text/plain',
                'delivery_mode' => 2, // 持久化
                'timestamp' => time() * 1000,
            ];

            // 添加自定义属性
            if (!empty($this->testMessageProperties)) {
                foreach ($this->testMessageProperties as $key => $value) {
                    $properties['headers'][$key] = $value;
                }
            }

            // 发送消息
            $response = Http::withBasicAuth('guest', 'guest')
                ->post("http://localhost:{$this->managementPort}/api/exchanges/%2F/{$this->testExchange}/publish", [
                    'properties' => $properties,
                    'routing_key' => $this->testRoutingKey,
                    'payload' => base64_encode($this->testMessageContent),
                    'payload_encoding' => 'base64',
                ]);

            if ($response->successful() && isset($response->json()['routed']) && $response->json()['routed']) {
                Notification::make()
                    ->title('发送消息成功')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('发送消息失败')
                    ->body(isset($response->json()['routed']) ? '消息未被路由到任何队列' : $response->body())
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('发送消息失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function receiveTestMessages(): void
    {
        try {
            if (!$this->isRunning || empty($this->testQueue)) {
                return;
            }

            // 接收消息
            $response = Http::withBasicAuth('guest', 'guest')
                ->post("http://localhost:{$this->managementPort}/api/queues/%2F/{$this->testQueue}/get", [
                    'count' => 10, // 最多接收10条消息
                    'ackmode' => 'ack_requeue_false', // 接收后删除消息
                    'encoding' => 'auto',
                ]);

            if ($response->successful()) {
                $messages = $response->json();

                if (empty($messages)) {
                    Notification::make()
                        ->title('没有消息可接收')
                        ->warning()
                        ->send();
                } else {
                    $this->receivedMessages = [];

                    foreach ($messages as $message) {
                        $this->receivedMessages[] = [
                            'payload' => $message['payload'],
                            'properties' => $message['properties'],
                            'routing_key' => $message['routing_key'],
                            'exchange' => $message['exchange'],
                            'redelivered' => $message['redelivered'],
                            'message_count' => $message['message_count'],
                        ];
                    }

                    Notification::make()
                        ->title('接收消息成功')
                        ->body('接收到 ' . count($messages) . ' 条消息')
                        ->success()
                        ->send();
                }
            } else {
                Notification::make()
                    ->title('接收消息失败')
                    ->body($response->body())
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('接收消息失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function clearReceivedMessages(): void
    {
        $this->receivedMessages = [];
    }

    public function openManagementUI(): void
    {
        if (!$this->isRunning) {
            return;
        }

        // 在浏览器中打开管理界面
        $url = "http://localhost:{$this->managementPort}";

        if (PHP_OS_FAMILY === 'Windows') {
            shell_exec("start {$url}");
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            shell_exec("open {$url}");
        } elseif (PHP_OS_FAMILY === 'Linux') {
            shell_exec("xdg-open {$url}");
        }
    }
}
