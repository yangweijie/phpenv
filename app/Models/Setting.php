<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    protected $casts = [
        'value' => 'json',
    ];

    // 设置类型常量
    const TYPE_GENERAL = 'general';
    const TYPE_SERVICES = 'services';
    const TYPE_PHP = 'php';
    const TYPE_SECURITY = 'security';

    // 获取设置类型列表
    public static function getTypes()
    {
        return [
            self::TYPE_GENERAL => '常规设置',
            self::TYPE_SERVICES => '服务设置',
            self::TYPE_PHP => 'PHP设置',
            self::TYPE_SECURITY => '安全设置',
        ];
    }

    // 获取设置值
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }
        
        return $setting->value;
    }

    // 设置值
    public static function set($key, $value, $type = self::TYPE_GENERAL, $description = null)
    {
        $setting = self::where('key', $key)->first();
        
        if ($setting) {
            $setting->update([
                'value' => $value,
                'type' => $type,
                'description' => $description ?? $setting->description,
            ]);
        } else {
            $setting = self::create([
                'key' => $key,
                'value' => $value,
                'type' => $type,
                'description' => $description,
            ]);
        }
        
        return $setting;
    }
}
