<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'status',
        'path',
        'port',
        'auto_start',
        'config_path',
        'log_path',
    ];

    protected $casts = [
        'auto_start' => 'boolean',
        'status' => 'boolean',
    ];

    // 服务类型常量
    const TYPE_APACHE = 'apache';
    const TYPE_MYSQL = 'mysql';
    const TYPE_NGINX = 'nginx';
    const TYPE_REDIS = 'redis';
    const TYPE_MEMCACHED = 'memcached';
    const TYPE_RABBITMQ = 'rabbitmq';

    // 获取服务类型列表
    public static function getTypes()
    {
        return [
            self::TYPE_APACHE => 'Apache',
            self::TYPE_MYSQL => 'MySQL',
            self::TYPE_NGINX => 'Nginx',
            self::TYPE_REDIS => 'Redis',
            self::TYPE_MEMCACHED => 'Memcached',
            self::TYPE_RABBITMQ => 'RabbitMQ',
        ];
    }

    // 启动服务
    public function start()
    {
        // 根据不同服务类型执行不同的启动命令
        switch ($this->type) {
            case self::TYPE_APACHE:
                // 启动Apache服务
                $result = $this->executeCommand($this->path . ' -k start');
                break;
            case self::TYPE_MYSQL:
                // 启动MySQL服务
                $result = $this->executeCommand('net start mysql');
                break;
            case self::TYPE_NGINX:
                // 启动Nginx服务
                $result = $this->executeCommand($this->path . ' -c ' . $this->config_path);
                break;
            case self::TYPE_REDIS:
                // 启动Redis服务
                $result = $this->executeCommand($this->path . ' ' . $this->config_path);
                break;
            case self::TYPE_MEMCACHED:
                // 启动Memcached服务
                $result = $this->executeCommand($this->path . ' -p ' . $this->port . ' -l 127.0.0.1');
                break;
            case self::TYPE_RABBITMQ:
                // 启动RabbitMQ服务
                $rabbitDir = dirname($this->path);
                $result = $this->executeCommand('cd ' . $rabbitDir . ' && ' . $this->path . ' start');
                break;
            default:
                $result = false;
                break;
        }

        if ($result) {
            $this->status = true;
            $this->save();
        }

        // 记录日志
        \App\Models\ActivityLog::logServiceAction($this, '启动', $result);

        return $result;
    }

    // 停止服务
    public function stop()
    {
        // 根据不同服务类型执行不同的停止命令
        switch ($this->type) {
            case self::TYPE_APACHE:
                // 停止Apache服务
                $result = $this->executeCommand($this->path . ' -k stop');
                break;
            case self::TYPE_MYSQL:
                // 停止MySQL服务
                $result = $this->executeCommand('net stop mysql');
                break;
            case self::TYPE_NGINX:
                // 停止Nginx服务
                $result = $this->executeCommand($this->path . ' -s stop');
                break;
            case self::TYPE_REDIS:
                // 停止Redis服务
                // 使用Redis CLI发送SHUTDOWN命令
                $redisCliPath = dirname($this->path) . '/redis-cli.exe';
                $result = $this->executeCommand($redisCliPath . ' -p ' . $this->port . ' shutdown');
                break;
            case self::TYPE_MEMCACHED:
                // 停止Memcached服务
                // 使用taskkill结束进程
                $result = $this->executeCommand('taskkill /F /IM memcached.exe');
                break;
            case self::TYPE_RABBITMQ:
                // 停止RabbitMQ服务
                $rabbitDir = dirname($this->path);
                $result = $this->executeCommand('cd ' . $rabbitDir . ' && ' . $this->path . ' stop');
                break;
            default:
                $result = false;
                break;
        }

        if ($result) {
            $this->status = false;
            $this->save();
        }

        // 记录日志
        \App\Models\ActivityLog::logServiceAction($this, '停止', $result);

        return $result;
    }

    // 重启服务
    public function restart()
    {
        $stopResult = $this->stop();
        $startResult = $this->start();
        $result = $stopResult && $startResult;

        // 记录日志
        \App\Models\ActivityLog::logServiceAction($this, '重启', $result);

        return $result;
    }

    // 检查服务状态
    public function checkStatus()
    {
        // 根据不同服务类型检查服务状态
        switch ($this->type) {
            case self::TYPE_APACHE:
                // 检查Apache服务状态
                $result = $this->executeCommand('tasklist /FI "IMAGENAME eq httpd.exe" /NH');
                $status = strpos($result, 'httpd.exe') !== false;
                break;
            case self::TYPE_MYSQL:
                // 检查MySQL服务状态
                $result = $this->executeCommand('tasklist /FI "IMAGENAME eq mysqld.exe" /NH');
                $status = strpos($result, 'mysqld.exe') !== false;
                break;
            case self::TYPE_NGINX:
                // 检查Nginx服务状态
                $result = $this->executeCommand('tasklist /FI "IMAGENAME eq nginx.exe" /NH');
                $status = strpos($result, 'nginx.exe') !== false;
                break;
            case self::TYPE_REDIS:
                // 检查Redis服务状态
                $result = $this->executeCommand('tasklist /FI "IMAGENAME eq redis-server.exe" /NH');
                $status = strpos($result, 'redis-server.exe') !== false;

                // 如果进程存在，还要尝试连接Redis服务
                if ($status) {
                    $redisCliPath = dirname($this->path) . '/redis-cli.exe';
                    $pingResult = $this->executeCommand($redisCliPath . ' -p ' . $this->port . ' ping');
                    $status = strpos($pingResult, 'PONG') !== false;
                }
                break;
            case self::TYPE_MEMCACHED:
                // 检查Memcached服务状态
                $result = $this->executeCommand('tasklist /FI "IMAGENAME eq memcached.exe" /NH');
                $status = strpos($result, 'memcached.exe') !== false;
                break;
            case self::TYPE_RABBITMQ:
                // 检查RabbitMQ服务状态
                $result = $this->executeCommand('tasklist /FI "IMAGENAME eq rabbitmq-server.exe" /NH');
                $rabbitRunning = strpos($result, 'rabbitmq-server.exe') !== false;

                // 检查Erlang运行时
                $erlangResult = $this->executeCommand('tasklist /FI "IMAGENAME eq erl.exe" /NH');
                $erlangRunning = strpos($erlangResult, 'erl.exe') !== false;

                // 如果进程存在，还要尝试检查RabbitMQ状态
                if ($rabbitRunning || $erlangRunning) {
                    $rabbitDir = dirname($this->path);
                    $statusResult = $this->executeCommand('cd ' . $rabbitDir . ' && ' . $this->path . ' status');
                    $status = strpos($statusResult, 'running') !== false;
                } else {
                    $status = false;
                }
                break;
            default:
                $status = false;
                break;
        }

        // 如果状态发生变化，记录日志
        $oldStatus = $this->status;
        $this->status = $status;
        $this->save();

        if ($oldStatus !== $status) {
            $action = $status ? '自动检测到运行中' : '自动检测到已停止';
            \App\Models\ActivityLog::logServiceAction($this, $action, true);
        }

        return $status;
    }

    // 执行命令
    private function executeCommand($command)
    {
        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);
        return $returnVar === 0;
    }
}
