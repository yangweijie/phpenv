<?php

namespace App\Filament\Pages;

use App\Models\Service;
use App\Models\PerformanceMetric;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\DB;

class PerformanceMonitoring extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static ?string $navigationLabel = '性能监控';
    
    protected static ?string $navigationGroup = '服务管理';
    
    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.performance-monitoring';
    
    public $selectedService = null;
    public $selectedMetric = PerformanceMetric::TYPE_CPU_USAGE;
    public $selectedPeriod = 'day';
    
    public function mount(): void
    {
        $service = Service::where('status', true)->first();
        
        if ($service) {
            $this->selectedService = $service->id;
        }
    }
    
    public function getServices()
    {
        return Service::all();
    }
    
    public function getMetricTypes()
    {
        return PerformanceMetric::getTypes();
    }
    
    public function getPeriods()
    {
        return [
            'hour' => '最近1小时',
            'day' => '最近24小时',
            'week' => '最近7天',
            'month' => '最近30天',
        ];
    }
    
    public function getSelectedService()
    {
        if (!$this->selectedService) {
            return null;
        }
        
        return Service::find($this->selectedService);
    }
    
    public function getLatestMetrics()
    {
        if (!$this->selectedService) {
            return [];
        }
        
        return PerformanceMetric::getLatestMetrics($this->selectedService);
    }
    
    public function getHistoricalMetrics()
    {
        if (!$this->selectedService || !$this->selectedMetric) {
            return [];
        }
        
        return PerformanceMetric::getHistoricalMetrics(
            $this->selectedService,
            $this->selectedMetric,
            $this->selectedPeriod
        );
    }
    
    public function getMetricStats()
    {
        if (!$this->selectedService || !$this->selectedMetric) {
            return [
                'avg' => 0,
                'max' => 0,
                'min' => 0,
            ];
        }
        
        return [
            'avg' => PerformanceMetric::getAverageMetric(
                $this->selectedService,
                $this->selectedMetric,
                $this->selectedPeriod
            ),
            'max' => PerformanceMetric::getMaxMetric(
                $this->selectedService,
                $this->selectedMetric,
                $this->selectedPeriod
            ),
            'min' => PerformanceMetric::getMinMetric(
                $this->selectedService,
                $this->selectedMetric,
                $this->selectedPeriod
            ),
        ];
    }
    
    public function getChartData()
    {
        $metrics = $this->getHistoricalMetrics();
        
        if (empty($metrics)) {
            return [
                'labels' => [],
                'datasets' => [
                    [
                        'label' => PerformanceMetric::getTypes()[$this->selectedMetric] ?? $this->selectedMetric,
                        'data' => [],
                        'borderColor' => 'rgb(75, 192, 192)',
                        'tension' => 0.1,
                    ],
                ],
            ];
        }
        
        $labels = [];
        $data = [];
        
        foreach ($metrics as $metric) {
            $labels[] = $metric->recorded_at->format('Y-m-d H:i:s');
            $data[] = $metric->value;
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => PerformanceMetric::getTypes()[$this->selectedMetric] ?? $this->selectedMetric,
                    'data' => $data,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'tension' => 0.1,
                ],
            ],
        ];
    }
    
    public function refreshMetrics()
    {
        if (!$this->selectedService) {
            return;
        }
        
        $service = Service::find($this->selectedService);
        
        if (!$service) {
            return;
        }
        
        PerformanceMetric::collectServiceMetrics($service);
        
        $this->dispatch('metrics-refreshed');
    }
}
