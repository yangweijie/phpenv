<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhpVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'path',
        'is_active',
        'is_default',
        'extensions_path',
        'php_ini_path',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    // 获取已安装的PHP扩展
    public function getInstalledExtensions()
    {
        $extensions = [];

        if (file_exists($this->extensions_path)) {
            $files = scandir($this->extensions_path);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'dll') {
                    $extensions[] = pathinfo($file, PATHINFO_FILENAME);
                }
            }
        }

        return $extensions;
    }

    // 激活此PHP版本
    public function activate()
    {
        // 首先将所有版本设置为非激活状态
        self::query()->update(['is_active' => false]);

        // 设置当前版本为激活状态
        $this->is_active = true;
        $this->save();

        // 更新系统环境变量
        $result = $this->updateEnvironmentVariables();

        // 记录日志
        \App\Models\ActivityLog::logPhpVersionAction($this, '激活', $result);

        return $result;
    }

    // 设置为默认版本
    public function setAsDefault()
    {
        // 首先将所有版本设置为非默认状态
        self::query()->update(['is_default' => false]);

        // 设置当前版本为默认状态
        $this->is_default = true;
        $this->save();

        // 记录日志
        \App\Models\ActivityLog::logPhpVersionAction($this, '设置为默认版本', true);

        return true;
    }

    // 更新系统环境变量
    private function updateEnvironmentVariables()
    {
        // 获取当前PATH环境变量
        $command = 'powershell.exe -Command "[Environment]::GetEnvironmentVariable(\'PATH\', \'Machine\')"';
        $currentPath = trim(shell_exec($command));

        // 移除其他PHP路径
        $pathParts = explode(';', $currentPath);
        $newPathParts = [];

        foreach ($pathParts as $part) {
            // 如果路径不包含php，或者是当前PHP版本的路径，则保留
            if (stripos($part, 'php') === false || stripos($part, $this->path) !== false) {
                $newPathParts[] = $part;
            }
        }

        // 确保当前PHP版本的路径在PATH中
        if (!in_array($this->path, $newPathParts)) {
            $newPathParts[] = $this->path;
        }

        // 更新PATH环境变量
        $newPath = implode(';', $newPathParts);
        $command = 'powershell.exe -Command "[Environment]::SetEnvironmentVariable(\'PATH\', \'' . $newPath . '\', \'Machine\')"';
        shell_exec($command);

        return true;
    }

    // 获取PHP版本信息
    public function getVersionInfo()
    {
        $command = $this->path . '\php.exe -v';
        return shell_exec($command);
    }

    // 获取PHP配置信息
    public function getPhpInfo()
    {
        $command = $this->path . '\php.exe -i';
        return shell_exec($command);
    }
}
