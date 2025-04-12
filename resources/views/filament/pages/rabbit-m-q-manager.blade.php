<x-filament-panels::page>
    <div class="space-y-6">
        @if(!$isInstalled)
            <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                <h2 class="text-xl font-semibold mb-4">安装RabbitMQ</h2>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="newPort" class="block text-sm font-medium text-gray-700 dark:text-gray-300">AMQP端口</label>
                            <input type="number" id="newPort" wire:model="newPort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" min="1024" max="65535">
                        </div>
                        
                        <div>
                            <label for="managementPort" class="block text-sm font-medium text-gray-700 dark:text-gray-300">管理端口</label>
                            <input type="number" id="managementPort" wire:model="managementPort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" min="1024" max="65535">
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="button" wire:click="installRabbitMQ" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800">
                            安装RabbitMQ
                        </button>
                    </div>
                    
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <p>注意: 安装RabbitMQ需要先安装Erlang运行时。如果未安装，系统将自动下载并安装Erlang。</p>
                        <p>安装过程可能需要几分钟时间，请耐心等待。</p>
                    </div>
                </div>
            </div>
        @else
            <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">RabbitMQ服务</h2>
                    
                    <div class="flex space-x-2">
                        @if($isRunning)
                            <button type="button" wire:click="stopRabbitMQ" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800">
                                停止
                            </button>
                            <button type="button" wire:click="restartRabbitMQ" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 dark:focus:ring-offset-gray-800">
                                重启
                            </button>
                            <button type="button" wire:click="openManagementUI" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                                管理界面
                            </button>
                        @else
                            <button type="button" wire:click="startRabbitMQ" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800">
                                启动
                            </button>
                        @endif
                        <button type="button" wire:click="uninstallRabbitMQ" class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800">
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
                        <p class="text-sm text-gray-500 dark:text-gray-400">AMQP端口: {{ $rabbitMQService->port }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">管理端口: {{ $managementPort }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">路径: {{ $rabbitMQService->path }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">配置文件: {{ $rabbitMQService->config_path }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">自动启动: 
                            @if($rabbitMQService->auto_start)
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
                            <p class="text-sm text-gray-500 dark:text-gray-400">RabbitMQ版本: {{ $overview['rabbitmq_version'] ?? '未知' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Erlang版本: {{ $overview['erlang_version'] ?? '未知' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">集群名称: {{ $overview['cluster_name'] ?? '未知' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">节点数: {{ isset($nodes) ? count($nodes) : '未知' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">队列数: {{ $overview['object_totals']['queues'] ?? '0' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">连接数: {{ $overview['object_totals']['connections'] ?? '0' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">交换器数: {{ $overview['object_totals']['exchanges'] ?? '0' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">消费者数: {{ $overview['object_totals']['consumers'] ?? '0' }}</p>
                        </div>
                    </div>
                    
                    <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                        <h3 class="text-lg font-medium mb-4">消息统计</h3>
                        
                        <div class="space-y-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">发布消息数: {{ $overview['message_stats']['publish'] ?? '0' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">发布速率: {{ $overview['message_stats']['publish_details']['rate'] ?? '0' }} 条/秒</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">确认消息数: {{ $overview['message_stats']['ack'] ?? '0' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">确认速率: {{ $overview['message_stats']['ack_details']['rate'] ?? '0' }} 条/秒</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">投递消息数: {{ $overview['message_stats']['deliver'] ?? '0' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">投递速率: {{ $overview['message_stats']['deliver_details']['rate'] ?? '0' }} 条/秒</p>
                        </div>
                    </div>
                    
                    <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                        <h3 class="text-lg font-medium mb-4">节点信息</h3>
                        
                        <div class="space-y-2">
                            @if(!empty($nodes))
                                @foreach($nodes as $node)
                                    <p class="text-sm text-gray-500 dark:text-gray-400">节点名称: {{ $node['name'] ?? '未知' }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">运行时间: {{ isset($node['uptime']) ? floor($node['uptime'] / 86400000) . '天 ' . gmdate('H:i:s', $node['uptime'] / 1000 % 86400) : '未知' }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">内存使用: {{ isset($node['mem_used']) ? round($node['mem_used'] / 1024 / 1024, 2) . ' MB' : '未知' }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">进程数: {{ $node['proc_used'] ?? '0' }} / {{ $node['proc_total'] ?? '0' }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">文件描述符: {{ $node['fd_used'] ?? '0' }} / {{ $node['fd_total'] ?? '0' }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">磁盘空间: {{ isset($node['disk_free']) ? round($node['disk_free'] / 1024 / 1024 / 1024, 2) . ' GB 可用' : '未知' }}</p>
                                @endforeach
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400">无法获取节点信息</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 gap-6">
                    <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium">队列管理</h3>
                            
                            <div x-data="{ open: false, name: '', durable: true, autoDelete: false }">
                                <button type="button" x-on:click="open = true" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800">
                                    创建队列
                                </button>
                                
                                <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                                        
                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                        
                                        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 dark:bg-gray-800">
                                            <div>
                                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">创建队列</h3>
                                                
                                                <div class="mt-4 space-y-4">
                                                    <div>
                                                        <label for="queueName" class="block text-sm font-medium text-gray-700 dark:text-gray-300">队列名称</label>
                                                        <input type="text" id="queueName" x-model="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                                                    </div>
                                                    
                                                    <div class="flex items-center">
                                                        <input type="checkbox" id="queueDurable" x-model="durable" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded dark:border-gray-600 dark:bg-gray-700">
                                                        <label for="queueDurable" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">持久化</label>
                                                    </div>
                                                    
                                                    <div class="flex items-center">
                                                        <input type="checkbox" id="queueAutoDelete" x-model="autoDelete" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded dark:border-gray-600 dark:bg-gray-700">
                                                        <label for="queueAutoDelete" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">自动删除</label>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                                <button type="button" x-on:click="$wire.createQueue(name, durable, autoDelete); open = false" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:col-start-2 sm:text-sm dark:focus:ring-offset-gray-800">
                                                    创建
                                                </button>
                                                <button type="button" x-on:click="open = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:col-start-1 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800">
                                                    取消
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                            名称
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                            状态
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                            消息数
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                            消费者数
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                            操作
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    @forelse($queues as $queue)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $queue['name'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                @if($queue['state'] === 'running')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">
                                                        运行中
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100">
                                                        {{ $queue['state'] }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $queue['messages'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $queue['consumers'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button type="button" wire:click="purgeQueue('{{ $queue['name'] }}')" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300 mr-3">
                                                    清空
                                                </button>
                                                <button type="button" wire:click="deleteQueue('{{ $queue['name'] }}')" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                    删除
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                                没有队列
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
            
            <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                <h3 class="text-lg font-medium mb-4">RabbitMQ配置</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="newPort" class="block text-sm font-medium text-gray-700 dark:text-gray-300">AMQP端口</label>
                        <input type="number" id="newPort" wire:model="newPort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" min="1024" max="65535">
                    </div>
                    
                    <div>
                        <label for="managementPort" class="block text-sm font-medium text-gray-700 dark:text-gray-300">管理端口</label>
                        <input type="number" id="managementPort" wire:model="managementPort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" min="1024" max="65535">
                    </div>
                    
                    <div>
                        <label for="memoryLimit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">内存限制 (MB)</label>
                        <input type="number" id="memoryLimit" wire:model="memoryLimit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" min="128">
                    </div>
                    
                    <div>
                        <label for="diskFreeLimit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">磁盘空间限制 (MB)</label>
                        <input type="number" id="diskFreeLimit" wire:model="diskFreeLimit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" min="100">
                    </div>
                </div>
                
                <div class="mt-4 flex justify-end">
                    <button type="button" wire:click="updateConfig" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800">
                        保存配置
                    </button>
                </div>
                
                <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                    <p>注意: 修改配置后需要重启RabbitMQ服务才能生效。</p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
