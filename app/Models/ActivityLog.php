<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'log_type',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    // 日志类型常量
    const TYPE_INFO = 'info';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR = 'error';
    const TYPE_SUCCESS = 'success';

    // 获取日志类型列表
    public static function getTypes()
    {
        return [
            self::TYPE_INFO => '信息',
            self::TYPE_WARNING => '警告',
            self::TYPE_ERROR => '错误',
            self::TYPE_SUCCESS => '成功',
        ];
    }

    // 获取日志类型颜色
    public function getTypeColor()
    {
        return match ($this->log_type) {
            self::TYPE_INFO => 'info',
            self::TYPE_WARNING => 'warning',
            self::TYPE_ERROR => 'danger',
            self::TYPE_SUCCESS => 'success',
            default => 'gray',
        };
    }

    // 获取日志类型图标
    public function getTypeIcon()
    {
        return match ($this->log_type) {
            self::TYPE_INFO => 'heroicon-o-information-circle',
            self::TYPE_WARNING => 'heroicon-o-exclamation-triangle',
            self::TYPE_ERROR => 'heroicon-o-x-circle',
            self::TYPE_SUCCESS => 'heroicon-o-check-circle',
            default => 'heroicon-o-document-text',
        };
    }

    // 记录服务操作日志
    public static function logServiceAction($service, $action, $success = true)
    {
        $logType = $success ? self::TYPE_SUCCESS : self::TYPE_ERROR;
        $description = "服务 [{$service->name}] {$action}" . ($success ? '成功' : '失败');

        return self::create([
            'description' => $description,
            'subject_type' => get_class($service),
            'subject_id' => $service->id,
            'properties' => [
                'action' => $action,
                'success' => $success,
                'service_type' => $service->type,
            ],
            'log_type' => $logType,
        ]);
    }

    // 记录PHP版本操作日志
    public static function logPhpVersionAction($phpVersion, $action, $success = true)
    {
        $logType = $success ? self::TYPE_SUCCESS : self::TYPE_ERROR;
        $description = "PHP版本 [{$phpVersion->version}] {$action}" . ($success ? '成功' : '失败');

        return self::create([
            'description' => $description,
            'subject_type' => get_class($phpVersion),
            'subject_id' => $phpVersion->id,
            'properties' => [
                'action' => $action,
                'success' => $success,
                'version' => $phpVersion->version,
            ],
            'log_type' => $logType,
        ]);
    }

    // 记录网站操作日志
    public static function logWebsiteAction($website, $action, $success = true)
    {
        $logType = $success ? self::TYPE_SUCCESS : self::TYPE_ERROR;
        $description = "网站 [{$website->name}] {$action}" . ($success ? '成功' : '失败');

        return self::create([
            'description' => $description,
            'subject_type' => get_class($website),
            'subject_id' => $website->id,
            'properties' => [
                'action' => $action,
                'success' => $success,
                'domain' => $website->domain,
            ],
            'log_type' => $logType,
        ]);
    }

    // 记录系统日志
    public static function logSystem($description, $properties = [], $logType = self::TYPE_INFO)
    {
        return self::create([
            'description' => $description,
            'properties' => $properties,
            'log_type' => $logType,
        ]);
    }
}
