<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComposerPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'version',
        'description',
        'type',
        'project_path',
        'is_global',
    ];

    protected $casts = [
        'is_global' => 'boolean',
    ];

    // 安装Composer包
    public function install()
    {
        $command = 'cd ' . $this->project_path . ' && ';
        
        if ($this->is_global) {
            $command .= 'composer global require ' . $this->name;
        } else {
            $command .= 'composer require ' . $this->name;
        }
        
        if (!empty($this->version)) {
            $command .= ':' . $this->version;
        }
        
        $output = shell_exec($command);
        return $output;
    }

    // 更新Composer包
    public function update()
    {
        $command = 'cd ' . $this->project_path . ' && ';
        
        if ($this->is_global) {
            $command .= 'composer global update ' . $this->name;
        } else {
            $command .= 'composer update ' . $this->name;
        }
        
        $output = shell_exec($command);
        return $output;
    }

    // 卸载Composer包
    public function uninstall()
    {
        $command = 'cd ' . $this->project_path . ' && ';
        
        if ($this->is_global) {
            $command .= 'composer global remove ' . $this->name;
        } else {
            $command .= 'composer remove ' . $this->name;
        }
        
        $output = shell_exec($command);
        return $output;
    }

    // 获取项目中已安装的Composer包
    public static function getInstalledPackages($projectPath)
    {
        $command = 'cd ' . $projectPath . ' && composer show --format=json';
        $output = shell_exec($command);
        
        if ($output) {
            $packages = json_decode($output, true);
            return $packages['installed'] ?? [];
        }
        
        return [];
    }

    // 获取全局安装的Composer包
    public static function getGlobalPackages()
    {
        $command = 'composer global show --format=json';
        $output = shell_exec($command);
        
        if ($output) {
            $packages = json_decode($output, true);
            return $packages['installed'] ?? [];
        }
        
        return [];
    }

    // 搜索Composer包
    public static function searchPackages($keyword)
    {
        $command = 'composer search ' . $keyword . ' --format=json';
        $output = shell_exec($command);
        
        if ($output) {
            $result = json_decode($output, true);
            return $result['results'] ?? [];
        }
        
        return [];
    }
}
