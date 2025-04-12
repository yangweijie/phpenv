<x-filament-panels::page>
    <div class="space-y-6">
        @if(!$isInstalled)
            <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                <h2 class="text-xl font-semibold mb-4">安装Redis</h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="newPort" class="block text-sm font-medium text-gray-700 dark:text-gray-300">端口</label>
                        <input type="number" id="newPort" wire:model="newPort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" min="1024" max="65535">
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="button" wire:click="installRedis" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800">
                            安装Redis
                        </button>
                    </div>
                </div>
            </div>
        @else
            <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Redis服务</h2>
                    
                    <div class="flex space-x-2">
                        @if($isRunning)
                            <button type="button" wire:click="stopRedis" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800">
                                停止
                            </button>
                            <button type="button" wire:click="restartRedis" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 dark:focus:ring-offset-gray-800">
                                重启
                            </button>
                        @else
                            <button type="button" wire:click="startRedis" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800">
                                启动
                            </button>
                        @endif
                        <button type="button" wire:click="uninstallRedis" class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800">
                            卸载
                        </button>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">状态: 
                            @if($isRunning)
                                <span class="text-green-500 font-medium">运行中</span>
                            @else
                                <span class="text-red-500 font-medium">已停止</span>
                            @endif
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">端口: {{ $redisService->port }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">配置文件: {{ $redisService->config_path }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">路径: {{ $redisService->path }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">自动启动: 
                            @if($redisService->auto_start)
                                <span class="text-green-500 font-medium">是</span>
                            @else
                                <span class="text-red-500 font-medium">否</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            
            @if($isRunning)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                        <h3 class="text-lg font-medium mb-4">服务信息</h3>
                        
                        <div class="space-y-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Redis版本: {{ $redisInfo['redis_version'] ?? '未知' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">运行模式: {{ $redisInfo['redis_mode'] ?? '未知' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">操作系统: {{ $redisInfo['os'] ?? '未知' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">进程ID: {{ $redisInfo['process_id'] ?? '未知' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">运行时间: {{ isset($redisInfo['uptime_in_seconds']) ? floor($redisInfo['uptime_in_seconds'] / 86400) . '天 ' . gmdate('H:i:s', $redisInfo['uptime_in_seconds'] % 86400) : '未知' }}</p>
                        </div>
                    </div>
                    
                    <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                        <h3 class="text-lg font-medium mb-4">内存使用</h3>
                        
                        <div class="space-y-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">已用内存: {{ isset($redisMemory['used_memory_human']) ? $redisMemory['used_memory_human'] : '未知' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">内存峰值: {{ isset($redisMemory['used_memory_peak_human']) ? $redisMemory['used_memory_peak_human'] : '未知' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">内存碎片率: {{ isset($redisMemory['mem_fragmentation_ratio']) ? $redisMemory['mem_fragmentation_ratio'] : '未知' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">内存分配器: {{ isset($redisMemory['mem_allocator']) ? $redisMemory['mem_allocator'] : '未知' }}</p>
                        </div>
                    </div>
                    
                    <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                        <h3 class="text-lg font-medium mb-4">统计信息</h3>
                        
                        <div class="space-y-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">连接数: {{ isset($redisClients['connected_clients']) ? $redisClients['connected_clients'] : '未知' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">命令执行数: {{ isset($redisStats['total_commands_processed']) ? $redisStats['total_commands_processed'] : '未知' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">键命中率: {{ isset($redisStats['keyspace_hits'], $redisStats['keyspace_misses']) ? round($redisStats['keyspace_hits'] / ($redisStats['keyspace_hits'] + $redisStats['keyspace_misses'] + 0.001) * 100, 2) . '%' : '未知' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">过期键数: {{ isset($redisStats['expired_keys']) ? $redisStats['expired_keys'] : '未知' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">被驱逐键数: {{ isset($redisStats['evicted_keys']) ? $redisStats['evicted_keys'] : '未知' }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                        <h3 class="text-lg font-medium mb-4">数据库信息</h3>
                        
                        <div class="space-y-2">
                            @forelse($redisKeyspace as $db => $info)
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $db }}: {{ $info }}</p>
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">没有数据库信息</p>
                            @endforelse
                        </div>
                        
                        <div class="mt-4 flex space-x-2">
                            <button type="button" wire:click="flushDB" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 dark:focus:ring-offset-gray-800">
                                清空当前数据库
                            </button>
                            <button type="button" wire:click="flushAll" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800">
                                清空所有数据库
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                        <h3 class="text-lg font-medium mb-4">命令执行</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="commandInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300">命令</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="text" id="commandInput" wire:model="commandInput" wire:keydown.enter="executeCommand" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" placeholder="输入Redis命令，如: GET key">
                                    <button type="button" wire:click="executeCommand" class="ml-2 inline-flex items-center px-3 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800">
                                        执行
                                    </button>
                                </div>
                            </div>
                            
                            <div>
                                <label for="commandOutput" class="block text-sm font-medium text-gray-700 dark:text-gray-300">输出</label>
                                <div class="mt-1">
                                    <textarea id="commandOutput" wire:model="commandOutput" rows="5" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" readonly></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            
            <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                <h3 class="text-lg font-medium mb-4">Redis配置</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="newPort" class="block text-sm font-medium text-gray-700 dark:text-gray-300">端口</label>
                        <input type="number" id="newPort" wire:model="newPort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" min="1024" max="65535">
                    </div>
                    
                    <div>
                        <label for="maxMemory" class="block text-sm font-medium text-gray-700 dark:text-gray-300">最大内存 (MB)</label>
                        <input type="number" id="maxMemory" wire:model="maxMemory" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" min="1">
                    </div>
                    
                    <div>
                        <label for="maxClients" class="block text-sm font-medium text-gray-700 dark:text-gray-300">最大客户端连接数</label>
                        <input type="number" id="maxClients" wire:model="maxClients" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" min="1">
                    </div>
                    
                    <div>
                        <label for="databases" class="block text-sm font-medium text-gray-700 dark:text-gray-300">数据库数量</label>
                        <input type="number" id="databases" wire:model="databases" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" min="1" max="16">
                    </div>
                </div>
                
                <div class="mt-4 flex justify-end">
                    <button type="button" wire:click="updateConfig" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800">
                        保存配置
                    </button>
                </div>
                
                <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                    <p>注意: 修改配置后需要重启Redis服务才能生效。</p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
