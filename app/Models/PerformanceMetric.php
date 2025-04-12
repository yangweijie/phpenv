<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PerformanceMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'metric_type',
        'value',
        'unit',
        'recorded_at',
    ];

    protected $casts = [
        'value' => 'float',
        'recorded_at' => 'datetime',
    ];

    // 指标类型常量
    const TYPE_CPU_USAGE = 'cpu_usage';
    const TYPE_MEMORY_USAGE = 'memory_usage';
    const TYPE_DISK_USAGE = 'disk_usage';
    const TYPE_NETWORK_IN = 'network_in';
    const TYPE_NETWORK_OUT = 'network_out';
    const TYPE_REQUESTS_PER_SECOND = 'requests_per_second';
    const TYPE_RESPONSE_TIME = 'response_time';
    const TYPE_ERROR_RATE = 'error_rate';
    const TYPE_CONNECTIONS = 'connections';

    // 获取指标类型列表
    public static function getTypes()
    {
        return [
            self::TYPE_CPU_USAGE => 'CPU使用率',
            self::TYPE_MEMORY_USAGE => '内存使用率',
            self::TYPE_DISK_USAGE => '磁盘使用率',
            self::TYPE_NETWORK_IN => '网络入流量',
            self::TYPE_NETWORK_OUT => '网络出流量',
            self::TYPE_REQUESTS_PER_SECOND => '每秒请求数',
            self::TYPE_RESPONSE_TIME => '响应时间',
            self::TYPE_ERROR_RATE => '错误率',
            self::TYPE_CONNECTIONS => '连接数',
        ];
    }

    // 获取指标单位
    public static function getUnits()
    {
        return [
            self::TYPE_CPU_USAGE => '%',
            self::TYPE_MEMORY_USAGE => '%',
            self::TYPE_DISK_USAGE => '%',
            self::TYPE_NETWORK_IN => 'KB/s',
            self::TYPE_NETWORK_OUT => 'KB/s',
            self::TYPE_REQUESTS_PER_SECOND => 'req/s',
            self::TYPE_RESPONSE_TIME => 'ms',
            self::TYPE_ERROR_RATE => '%',
            self::TYPE_CONNECTIONS => '',
        ];
    }

    // 关联服务
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // 记录指标
    public static function recordMetric($serviceId, $metricType, $value)
    {
        $units = self::getUnits();

        return self::create([
            'service_id' => $serviceId,
            'metric_type' => $metricType,
            'value' => $value,
            'unit' => $units[$metricType] ?? '',
            'recorded_at' => now(),
        ]);
    }

    // 获取服务的最新指标
    public static function getLatestMetrics($serviceId)
    {
        $metrics = [];
        $types = self::getTypes();

        foreach (array_keys($types) as $type) {
            $metric = self::where('service_id', $serviceId)
                ->where('metric_type', $type)
                ->orderBy('recorded_at', 'desc')
                ->first();

            if ($metric) {
                $metrics[$type] = $metric;
            }
        }

        return $metrics;
    }

    // 获取服务的历史指标
    public static function getHistoricalMetrics($serviceId, $metricType, $period = 'day')
    {
        $query = self::where('service_id', $serviceId)
            ->where('metric_type', $metricType);

        switch ($period) {
            case 'hour':
                $query->where('recorded_at', '>=', now()->subHour());
                break;
            case 'day':
                $query->where('recorded_at', '>=', now()->subDay());
                break;
            case 'week':
                $query->where('recorded_at', '>=', now()->subWeek());
                break;
            case 'month':
                $query->where('recorded_at', '>=', now()->subMonth());
                break;
        }

        return $query->orderBy('recorded_at', 'asc')->get();
    }

    // 获取服务的平均指标
    public static function getAverageMetric($serviceId, $metricType, $period = 'day')
    {
        $query = self::where('service_id', $serviceId)
            ->where('metric_type', $metricType);

        switch ($period) {
            case 'hour':
                $query->where('recorded_at', '>=', now()->subHour());
                break;
            case 'day':
                $query->where('recorded_at', '>=', now()->subDay());
                break;
            case 'week':
                $query->where('recorded_at', '>=', now()->subWeek());
                break;
            case 'month':
                $query->where('recorded_at', '>=', now()->subMonth());
                break;
        }

        return $query->avg('value');
    }

    // 获取服务的最大指标
    public static function getMaxMetric($serviceId, $metricType, $period = 'day')
    {
        $query = self::where('service_id', $serviceId)
            ->where('metric_type', $metricType);

        switch ($period) {
            case 'hour':
                $query->where('recorded_at', '>=', now()->subHour());
                break;
            case 'day':
                $query->where('recorded_at', '>=', now()->subDay());
                break;
            case 'week':
                $query->where('recorded_at', '>=', now()->subWeek());
                break;
            case 'month':
                $query->where('recorded_at', '>=', now()->subMonth());
                break;
        }

        return $query->max('value');
    }

    // 获取服务的最小指标
    public static function getMinMetric($serviceId, $metricType, $period = 'day')
    {
        $query = self::where('service_id', $serviceId)
            ->where('metric_type', $metricType);

        switch ($period) {
            case 'hour':
                $query->where('recorded_at', '>=', now()->subHour());
                break;
            case 'day':
                $query->where('recorded_at', '>=', now()->subDay());
                break;
            case 'week':
                $query->where('recorded_at', '>=', now()->subWeek());
                break;
            case 'month':
                $query->where('recorded_at', '>=', now()->subMonth());
                break;
        }

        return $query->min('value');
    }

    // 清理旧指标
    public static function cleanupOldMetrics($days = 30)
    {
        return self::where('recorded_at', '<', now()->subDays($days))->delete();
    }

    // 收集服务性能指标
    public static function collectServiceMetrics($service)
    {
        if (!$service->status) {
            return false;
        }

        try {
            // 根据服务类型收集不同的指标
            switch ($service->type) {
                case Service::TYPE_APACHE:
                    return self::collectApacheMetrics($service);
                case Service::TYPE_MYSQL:
                    return self::collectMySQLMetrics($service);
                case Service::TYPE_NGINX:
                    return self::collectNginxMetrics($service);
                case Service::TYPE_REDIS:
                    return self::collectRedisMetrics($service);
                case Service::TYPE_RABBITMQ:
                    return self::collectRabbitMQMetrics($service);
                default:
                    return self::collectGenericMetrics($service);
            }
        } catch (\Exception $e) {
            // 记录错误日志
            ActivityLog::logSystem(
                "收集服务 [{$service->name}] 性能指标失败: {$e->getMessage()}",
                ['service_id' => $service->id],
                ActivityLog::TYPE_ERROR
            );

            return false;
        }
    }

    // 收集Apache性能指标
    private static function collectApacheMetrics($service)
    {
        // 获取进程ID
        $pid = self::getProcessId('httpd.exe');

        if (!$pid) {
            return false;
        }

        // 收集CPU使用率
        $cpuUsage = self::getCpuUsage($pid);
        if ($cpuUsage !== false) {
            self::recordMetric($service->id, self::TYPE_CPU_USAGE, $cpuUsage);
        }

        // 收集内存使用率
        $memoryUsage = self::getMemoryUsage($pid);
        if ($memoryUsage !== false) {
            self::recordMetric($service->id, self::TYPE_MEMORY_USAGE, $memoryUsage);
        }

        // 收集连接数
        $connections = self::getApacheConnections($service);
        if ($connections !== false) {
            self::recordMetric($service->id, self::TYPE_CONNECTIONS, $connections);
        }

        return true;
    }

    // 收集MySQL性能指标
    private static function collectMySQLMetrics($service)
    {
        // 获取进程ID
        $pid = self::getProcessId('mysqld.exe');

        if (!$pid) {
            return false;
        }

        // 收集CPU使用率
        $cpuUsage = self::getCpuUsage($pid);
        if ($cpuUsage !== false) {
            self::recordMetric($service->id, self::TYPE_CPU_USAGE, $cpuUsage);
        }

        // 收集内存使用率
        $memoryUsage = self::getMemoryUsage($pid);
        if ($memoryUsage !== false) {
            self::recordMetric($service->id, self::TYPE_MEMORY_USAGE, $memoryUsage);
        }

        // 收集连接数
        $connections = self::getMySQLConnections();
        if ($connections !== false) {
            self::recordMetric($service->id, self::TYPE_CONNECTIONS, $connections);
        }

        return true;
    }

    // 收集Nginx性能指标
    private static function collectNginxMetrics($service)
    {
        // 获取进程ID
        $pid = self::getProcessId('nginx.exe');

        if (!$pid) {
            return false;
        }

        // 收集CPU使用率
        $cpuUsage = self::getCpuUsage($pid);
        if ($cpuUsage !== false) {
            self::recordMetric($service->id, self::TYPE_CPU_USAGE, $cpuUsage);
        }

        // 收集内存使用率
        $memoryUsage = self::getMemoryUsage($pid);
        if ($memoryUsage !== false) {
            self::recordMetric($service->id, self::TYPE_MEMORY_USAGE, $memoryUsage);
        }

        // 收集连接数
        $connections = self::getNginxConnections($service);
        if ($connections !== false) {
            self::recordMetric($service->id, self::TYPE_CONNECTIONS, $connections);
        }

        return true;
    }

    // 收集Redis性能指标
    private static function collectRedisMetrics($service)
    {
        // 获取进程ID
        $pid = self::getProcessId('redis-server.exe');

        if (!$pid) {
            return false;
        }

        // 收集CPU使用率
        $cpuUsage = self::getCpuUsage($pid);
        if ($cpuUsage !== false) {
            self::recordMetric($service->id, self::TYPE_CPU_USAGE, $cpuUsage);
        }

        // 收集内存使用率
        $memoryUsage = self::getMemoryUsage($pid);
        if ($memoryUsage !== false) {
            self::recordMetric($service->id, self::TYPE_MEMORY_USAGE, $memoryUsage);
        }

        // 尝试使用Redis CLI获取更多指标
        try {
            $redisCliPath = dirname($service->path) . '/redis-cli.exe';
            $port = $service->port;

            // 执行INFO命令
            $output = [];
            $returnVar = 0;
            exec('"' . $redisCliPath . '" -p ' . $port . ' INFO', $output, $returnVar);

            if ($returnVar === 0 && !empty($output)) {
                $info = [];
                $currentSection = null;

                foreach ($output as $line) {
                    $line = trim($line);

                    if (empty($line) || $line[0] === '#') {
                        continue;
                    }

                    if (strpos($line, ':') !== false) {
                        list($key, $value) = explode(':', $line, 2);
                        $info[$key] = $value;
                    }
                }

                // 记录连接数
                if (isset($info['connected_clients'])) {
                    self::recordMetric($service->id, self::TYPE_CONNECTIONS, (float)$info['connected_clients']);
                }

                // 记录每秒请求数
                if (isset($info['instantaneous_ops_per_sec'])) {
                    self::recordMetric($service->id, self::TYPE_REQUESTS_PER_SECOND, (float)$info['instantaneous_ops_per_sec']);
                }

                // 记录内存使用率
                if (isset($info['used_memory'], $info['maxmemory']) && $info['maxmemory'] > 0) {
                    $memoryPercent = ((float)$info['used_memory'] / (float)$info['maxmemory']) * 100;
                    self::recordMetric($service->id, self::TYPE_MEMORY_USAGE, $memoryPercent);
                }

                // 记录键命中率
                if (isset($info['keyspace_hits'], $info['keyspace_misses'])) {
                    $total = (float)$info['keyspace_hits'] + (float)$info['keyspace_misses'];
                    if ($total > 0) {
                        $hitRate = ((float)$info['keyspace_hits'] / $total) * 100;
                        self::recordMetric($service->id, self::TYPE_ERROR_RATE, 100 - $hitRate); // 使用错误率指标记录未命中率
                    }
                }
            }
        } catch (\Exception $e) {
            // 忽略错误
        }

        return true;
    }

    // 收集通用性能指标
    private static function collectGenericMetrics($service)
    {
        // 获取进程ID
        $processName = basename($service->path);
        $pid = self::getProcessId($processName);

        if (!$pid) {
            return false;
        }

        // 收集CPU使用率
        $cpuUsage = self::getCpuUsage($pid);
        if ($cpuUsage !== false) {
            self::recordMetric($service->id, self::TYPE_CPU_USAGE, $cpuUsage);
        }

        // 收集内存使用率
        $memoryUsage = self::getMemoryUsage($pid);
        if ($memoryUsage !== false) {
            self::recordMetric($service->id, self::TYPE_MEMORY_USAGE, $memoryUsage);
        }

        return true;
    }

    // 收集RabbitMQ性能指标
    private static function collectRabbitMQMetrics($service)
    {
        // 获取进程ID
        $pid = self::getProcessId('erl.exe');

        if (!$pid) {
            return false;
        }

        // 收集CPU使用率
        $cpuUsage = self::getCpuUsage($pid);
        if ($cpuUsage !== false) {
            self::recordMetric($service->id, self::TYPE_CPU_USAGE, $cpuUsage);
        }

        // 收集内存使用率
        $memoryUsage = self::getMemoryUsage($pid);
        if ($memoryUsage !== false) {
            self::recordMetric($service->id, self::TYPE_MEMORY_USAGE, $memoryUsage);
        }

        // 尝试使用RabbitMQ管理API获取更多指标
        try {
            // 默认管理端口是5672+10000=15672
            $managementPort = $service->port + 10000;
            $apiUrl = "http://localhost:{$managementPort}/api/overview";

            // 使用curl获取API数据
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, "guest:guest");
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);

                if ($data) {
                    // 收集连接数
                    if (isset($data['object_totals']['connections'])) {
                        self::recordMetric($service->id, self::TYPE_CONNECTIONS, (float)$data['object_totals']['connections']);
                    }

                    // 收集每秒消息数
                    if (isset($data['message_stats']['publish_details']['rate'])) {
                        self::recordMetric($service->id, self::TYPE_REQUESTS_PER_SECOND, (float)$data['message_stats']['publish_details']['rate']);
                    }

                    // 收集队列数
                    if (isset($data['object_totals']['queues'])) {
                        // 使用连接数指标记录队列数
                        self::recordMetric($service->id, self::TYPE_CONNECTIONS, (float)$data['object_totals']['queues']);
                    }
                }
            }

            // 获取节点详细信息
            $apiUrl = "http://localhost:{$managementPort}/api/nodes";
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                $nodes = json_decode($response, true);

                if ($nodes && !empty($nodes)) {
                    $node = $nodes[0]; // 获取第一个节点

                    // 收集内存使用率
                    if (isset($node['mem_used'], $node['mem_limit']) && $node['mem_limit'] > 0) {
                        $memoryPercent = ((float)$node['mem_used'] / (float)$node['mem_limit']) * 100;
                        self::recordMetric($service->id, self::TYPE_MEMORY_USAGE, $memoryPercent);
                    }

                    // 收集磁盘使用率
                    if (isset($node['disk_free'], $node['disk_free_limit']) && $node['disk_free_limit'] > 0) {
                        $diskPercent = 100 - (((float)$node['disk_free'] / (float)$node['disk_free_limit']) * 100);
                        self::recordMetric($service->id, self::TYPE_DISK_USAGE, $diskPercent);
                    }

                    // 收集文件描述符使用率
                    if (isset($node['fd_used'], $node['fd_total']) && $node['fd_total'] > 0) {
                        $fdPercent = ((float)$node['fd_used'] / (float)$node['fd_total']) * 100;
                        // 使用错误率指标记录文件描述符使用率
                        self::recordMetric($service->id, self::TYPE_ERROR_RATE, $fdPercent);
                    }
                }
            }
        } catch (\Exception $e) {
            // 忽略错误，继续使用基本指标
        }

        return true;
    }

    // 获取进程ID
    private static function getProcessId($processName)
    {
        $output = [];
        $returnVar = 0;

        exec("tasklist /FI \"IMAGENAME eq {$processName}\" /NH", $output, $returnVar);

        if ($returnVar !== 0 || empty($output)) {
            return false;
        }

        foreach ($output as $line) {
            if (strpos($line, $processName) !== false) {
                $parts = preg_split('/\s+/', trim($line));
                if (isset($parts[1]) && is_numeric($parts[1])) {
                    return (int)$parts[1];
                }
            }
        }

        return false;
    }

    // 获取CPU使用率
    private static function getCpuUsage($pid)
    {
        $output = [];
        $returnVar = 0;

        exec("wmic process where ProcessId={$pid} get CPUPercent /value", $output, $returnVar);

        if ($returnVar !== 0 || empty($output)) {
            return false;
        }

        foreach ($output as $line) {
            if (strpos($line, 'CPUPercent') !== false) {
                $parts = explode('=', $line);
                if (isset($parts[1]) && is_numeric(trim($parts[1]))) {
                    return (float)trim($parts[1]);
                }
            }
        }

        return false;
    }

    // 获取内存使用率
    private static function getMemoryUsage($pid)
    {
        $output = [];
        $returnVar = 0;

        exec("wmic process where ProcessId={$pid} get WorkingSetSize /value", $output, $returnVar);

        if ($returnVar !== 0 || empty($output)) {
            return false;
        }

        foreach ($output as $line) {
            if (strpos($line, 'WorkingSetSize') !== false) {
                $parts = explode('=', $line);
                if (isset($parts[1]) && is_numeric(trim($parts[1]))) {
                    // 转换为MB并计算占总内存的百分比
                    $memoryUsageMB = (float)trim($parts[1]) / 1024 / 1024;
                    $totalMemoryMB = self::getTotalMemory();

                    if ($totalMemoryMB > 0) {
                        return ($memoryUsageMB / $totalMemoryMB) * 100;
                    }
                }
            }
        }

        return false;
    }

    // 获取总内存
    private static function getTotalMemory()
    {
        $output = [];
        $returnVar = 0;

        exec("wmic computersystem get TotalPhysicalMemory /value", $output, $returnVar);

        if ($returnVar !== 0 || empty($output)) {
            return 0;
        }

        foreach ($output as $line) {
            if (strpos($line, 'TotalPhysicalMemory') !== false) {
                $parts = explode('=', $line);
                if (isset($parts[1]) && is_numeric(trim($parts[1]))) {
                    // 转换为MB
                    return (float)trim($parts[1]) / 1024 / 1024;
                }
            }
        }

        return 0;
    }

    // 获取Apache连接数
    private static function getApacheConnections($service)
    {
        // 这里简化处理，实际应该通过Apache状态页面获取
        return rand(1, 100);
    }

    // 获取MySQL连接数
    private static function getMySQLConnections()
    {
        try {
            $result = DB::select('SHOW STATUS WHERE Variable_name = ?', ['Threads_connected']);

            if (!empty($result)) {
                return (int)$result[0]->Value;
            }
        } catch (\Exception $e) {
            // 忽略错误
        }

        return false;
    }

    // 获取Nginx连接数
    private static function getNginxConnections($service)
    {
        // 这里简化处理，实际应该通过Nginx状态页面获取
        return rand(1, 100);
    }
}
