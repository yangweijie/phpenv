<x-filament-panels::page>
    <div class="space-y-6">
        <!-- 系统信息卡片 -->
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h2 class="text-lg font-semibold mb-4">系统信息</h2>
            
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($this->getSystemInfo() as $key => $value)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $key }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $value }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- 当前PHP版本卡片 -->
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h2 class="text-lg font-semibold mb-4">当前PHP版本</h2>
            
            @php
                $activePhpVersion = $this->getActivePhpVersion();
            @endphp
            
            @if($activePhpVersion)
                <div class="p-4 rounded-lg border border-blue-500 bg-blue-50 dark:bg-blue-900/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium">PHP {{ $activePhpVersion->version }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $activePhpVersion->path }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            激活
                        </span>
                    </div>
                </div>
            @else
                <div class="p-4 rounded-lg border border-gray-300 bg-gray-50 dark:bg-gray-700/20">
                    <p class="text-sm text-gray-500 dark:text-gray-400">未设置活动PHP版本</p>
                </div>
            @endif
        </div>
        
        <!-- 已加载扩展卡片 -->
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h2 class="text-lg font-semibold mb-4">已加载扩展 ({{ count($this->getLoadedExtensions()) }})</h2>
            
            <div class="flex flex-wrap gap-2">
                @foreach($this->getLoadedExtensions() as $extension)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                        {{ $extension }}
                    </span>
                @endforeach
            </div>
        </div>
        
        <!-- PHP信息卡片 -->
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h2 class="text-lg font-semibold mb-4">PHP信息</h2>
            
            <div class="overflow-x-auto">
                <div class="phpinfo">
                    {!! $this->getPhpInfo() !!}
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .phpinfo table {
            width: 100%;
            border-collapse: collapse;
            margin: 1em 0;
        }
        
        .phpinfo table th, .phpinfo table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .phpinfo table th {
            background-color: #f2f2f2;
            color: #333;
        }
        
        .phpinfo h1, .phpinfo h2 {
            font-size: 1.5em;
            margin: 1em 0;
        }
        
        .phpinfo hr {
            border: 0;
            border-top: 1px solid #ddd;
            margin: 1em 0;
        }
        
        .phpinfo a {
            color: #4f46e5;
            text-decoration: none;
        }
        
        .phpinfo a:hover {
            text-decoration: underline;
        }
        
        /* 暗黑模式 */
        .dark .phpinfo table th {
            background-color: #374151;
            color: #f3f4f6;
        }
        
        .dark .phpinfo table th, .dark .phpinfo table td {
            border-color: #4b5563;
        }
        
        .dark .phpinfo hr {
            border-top-color: #4b5563;
        }
        
        .dark .phpinfo a {
            color: #93c5fd;
        }
    </style>
</x-filament-panels::page>
