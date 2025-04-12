<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvironmentVariable extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'value',
        'type',
        'description',
    ];

    // 变量类型常量
    const TYPE_SYSTEM = 'system';
    const TYPE_USER = 'user';
    const TYPE_PATH = 'path';

    // 获取变量类型列表
    public static function getTypes()
    {
        return [
            self::TYPE_SYSTEM => '系统变量',
            self::TYPE_USER => '用户变量',
            self::TYPE_PATH => 'PATH变量',
        ];
    }

    // 获取系统环境变量
    public static function getSystemVariables()
    {
        $command = 'powershell.exe -Command "Get-ChildItem Env: | ForEach-Object { $_.Name + \'=\' + $_.Value }"';
        $output = shell_exec($command);
        $variables = [];
        
        if ($output) {
            $lines = explode("\n", $output);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    list($name, $value) = explode('=', $line, 2);
                    $variables[] = [
                        'name' => $name,
                        'value' => $value,
                        'type' => self::TYPE_SYSTEM,
                    ];
                }
            }
        }
        
        return $variables;
    }

    // 设置环境变量
    public function setVariable()
    {
        $command = '';
        
        if ($this->type === self::TYPE_SYSTEM) {
            // 设置系统环境变量
            $command = 'powershell.exe -Command "[Environment]::SetEnvironmentVariable(\'' . $this->name . '\', \'' . $this->value . '\', \'Machine\')"';
        } elseif ($this->type === self::TYPE_USER) {
            // 设置用户环境变量
            $command = 'powershell.exe -Command "[Environment]::SetEnvironmentVariable(\'' . $this->name . '\', \'' . $this->value . '\', \'User\')"';
        } elseif ($this->type === self::TYPE_PATH) {
            // 设置PATH环境变量
            $this->updatePathVariable();
            return true;
        }
        
        shell_exec($command);
        return true;
    }

    // 删除环境变量
    public function removeVariable()
    {
        $command = '';
        
        if ($this->type === self::TYPE_SYSTEM) {
            // 删除系统环境变量
            $command = 'powershell.exe -Command "[Environment]::SetEnvironmentVariable(\'' . $this->name . '\', $null, \'Machine\')"';
        } elseif ($this->type === self::TYPE_USER) {
            // 删除用户环境变量
            $command = 'powershell.exe -Command "[Environment]::SetEnvironmentVariable(\'' . $this->name . '\', $null, \'User\')"';
        } elseif ($this->type === self::TYPE_PATH) {
            // 从PATH环境变量中删除
            $this->removeFromPathVariable();
            return true;
        }
        
        shell_exec($command);
        return true;
    }

    // 更新PATH环境变量
    private function updatePathVariable()
    {
        // 获取当前PATH环境变量
        $command = 'powershell.exe -Command "[Environment]::GetEnvironmentVariable(\'PATH\', \'Machine\')"';
        $currentPath = trim(shell_exec($command));
        
        // 检查值是否已存在于PATH中
        $pathParts = explode(';', $currentPath);
        if (!in_array($this->value, $pathParts)) {
            // 添加新值到PATH
            $pathParts[] = $this->value;
            $newPath = implode(';', $pathParts);
            
            // 更新PATH环境变量
            $command = 'powershell.exe -Command "[Environment]::SetEnvironmentVariable(\'PATH\', \'' . $newPath . '\', \'Machine\')"';
            shell_exec($command);
        }
        
        return true;
    }

    // 从PATH环境变量中删除
    private function removeFromPathVariable()
    {
        // 获取当前PATH环境变量
        $command = 'powershell.exe -Command "[Environment]::GetEnvironmentVariable(\'PATH\', \'Machine\')"';
        $currentPath = trim(shell_exec($command));
        
        // 从PATH中移除值
        $pathParts = explode(';', $currentPath);
        $newPathParts = array_filter($pathParts, function($part) {
            return $part !== $this->value;
        });
        
        $newPath = implode(';', $newPathParts);
        
        // 更新PATH环境变量
        $command = 'powershell.exe -Command "[Environment]::SetEnvironmentVariable(\'PATH\', \'' . $newPath . '\', \'Machine\')"';
        shell_exec($command);
        
        return true;
    }
}
