<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex flex-col md:flex-row gap-4">
                <select
                    class="block w-full rounded-lg border border-gray-300 bg-white py-2 px-3 text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:focus:border-primary-500"
                    wire:model.live="selectedService"
                >
                    <option value="">选择服务</option>
                    @foreach($this->getServices() as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
                
                <select
                    class="block w-full rounded-lg border border-gray-300 bg-white py-2 px-3 text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:focus:border-primary-500"
                    wire:model.live="selectedMetric"
                >
                    @foreach($this->getMetricTypes() as $type => $label)
                        <option value="{{ $type }}">{{ $label }}</option>
                    @endforeach
                </select>
                
                <select
                    class="block w-full rounded-lg border border-gray-300 bg-white py-2 px-3 text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:focus:border-primary-500"
                    wire:model.live="selectedPeriod"
                >
                    @foreach($this->getPeriods() as $period => $label)
                        <option value="{{ $period }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            
            <button
                type="button"
                class="inline-flex items-center justify-center py-2 px-4 rounded-lg text-sm font-medium text-white bg-primary-600 hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400 dark:focus:ring-primary-400"
                wire:click="refreshMetrics"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                刷新数据
            </button>
        </div>
        
        @if($this->selectedService)
            @php
                $service = $this->getSelectedService();
                $latestMetrics = $this->getLatestMetrics();
                $metricStats = $this->getMetricStats();
            @endphp
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">平均值</h3>
                    <p class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                        {{ number_format($metricStats['avg'], 2) }}
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                            {{ $latestMetrics[$selectedMetric]->unit ?? '' }}
                        </span>
                    </p>
                </div>
                
                <div class="p-4 rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">最大值</h3>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                        {{ number_format($metricStats['max'], 2) }}
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                            {{ $latestMetrics[$selectedMetric]->unit ?? '' }}
                        </span>
                    </p>
                </div>
                
                <div class="p-4 rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">最小值</h3>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                        {{ number_format($metricStats['min'], 2) }}
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                            {{ $latestMetrics[$selectedMetric]->unit ?? '' }}
                        </span>
                    </p>
                </div>
            </div>
            
            <div class="p-4 rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ $service->name }} - {{ $this->getMetricTypes()[$selectedMetric] ?? $selectedMetric }}
                </h3>
                
                <div class="w-full h-80" wire:ignore>
                    <canvas id="metricsChart"></canvas>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($latestMetrics as $type => $metric)
                    <div class="p-4 rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ $this->getMetricTypes()[$type] ?? $type }}
                        </h3>
                        <p class="text-3xl font-bold 
                            @if($metric->value > 80)
                                text-red-600 dark:text-red-400
                            @elseif($metric->value > 50)
                                text-yellow-600 dark:text-yellow-400
                            @else
                                text-green-600 dark:text-green-400
                            @endif
                        ">
                            {{ number_format($metric->value, 2) }}
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                                {{ $metric->unit }}
                            </span>
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            最后更新: {{ $metric->recorded_at->format('Y-m-d H:i:s') }}
                        </p>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-4 rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
                <p class="text-center text-gray-500 dark:text-gray-400">
                    请选择一个服务查看性能指标
                </p>
            </div>
        @endif
    </div>
    
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            let chart = null;
            
            function initChart() {
                const ctx = document.getElementById('metricsChart').getContext('2d');
                const chartData = @json($this->getChartData());
                
                if (chart) {
                    chart.destroy();
                }
                
                chart = new Chart(ctx, {
                    type: 'line',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
            document.addEventListener('DOMContentLoaded', function() {
                if (document.getElementById('metricsChart')) {
                    initChart();
                }
            });
            
            document.addEventListener('metrics-refreshed', function() {
                if (document.getElementById('metricsChart')) {
                    initChart();
                }
            });
            
            document.addEventListener('livewire:navigated', function() {
                if (document.getElementById('metricsChart')) {
                    initChart();
                }
            });
        </script>
    @endpush
</x-filament-panels::page>
