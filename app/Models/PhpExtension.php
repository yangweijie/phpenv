<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhpExtension extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'version',
        'status',
        'php_version_id',
        'description',
        'file_name',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // 关联PHP版本
    public function phpVersion()
    {
        return $this->belongsTo(PhpVersion::class);
    }

    // 启用扩展
    public function enable()
    {
        // 获取PHP版本信息
        $phpVersion = $this->phpVersion;
        
        if (!$phpVersion) {
            return false;
        }
        
        // 获取php.ini文件路径
        $phpIniPath = $phpVersion->php_ini_path;
        
        if (!file_exists($phpIniPath)) {
            return false;
        }
        
        // 读取php.ini文件内容
        $phpIniContent = file_get_contents($phpIniPath);
        
        // 检查扩展是否已启用
        $extensionLine = 'extension=' . $this->file_name;
        if (strpos($phpIniContent, $extensionLine) !== false) {
            // 扩展已启用
            $this->status = true;
            $this->save();
            return true;
        }
        
        // 添加扩展到php.ini
        $phpIniContent .= "\n" . $extensionLine;
        file_put_contents($phpIniPath, $phpIniContent);
        
        // 更新状态
        $this->status = true;
        $this->save();
        
        return true;
    }

    // 禁用扩展
    public function disable()
    {
        // 获取PHP版本信息
        $phpVersion = $this->phpVersion;
        
        if (!$phpVersion) {
            return false;
        }
        
        // 获取php.ini文件路径
        $phpIniPath = $phpVersion->php_ini_path;
        
        if (!file_exists($phpIniPath)) {
            return false;
        }
        
        // 读取php.ini文件内容
        $phpIniContent = file_get_contents($phpIniPath);
        
        // 移除扩展配置
        $extensionLine = 'extension=' . $this->file_name;
        $phpIniContent = str_replace($extensionLine, ';' . $extensionLine, $phpIniContent);
        file_put_contents($phpIniPath, $phpIniContent);
        
        // 更新状态
        $this->status = false;
        $this->save();
        
        return true;
    }

    // 获取扩展信息
    public function getInfo()
    {
        // 获取PHP版本信息
        $phpVersion = $this->phpVersion;
        
        if (!$phpVersion) {
            return null;
        }
        
        // 执行命令获取扩展信息
        $command = $phpVersion->path . '\php.exe -r "echo json_encode(get_extension_funcs(\'' . $this->name . '\'));"';
        $output = shell_exec($command);
        
        if ($output) {
            return json_decode($output, true);
        }
        
        return null;
    }

    // 检查扩展状态
    public function checkStatus()
    {
        // 获取PHP版本信息
        $phpVersion = $this->phpVersion;
        
        if (!$phpVersion) {
            return false;
        }
        
        // 执行命令检查扩展是否已加载
        $command = $phpVersion->path . '\php.exe -r "echo extension_loaded(\'' . $this->name . '\') ? \'1\' : \'0\';"';
        $output = trim(shell_exec($command));
        
        $status = $output === '1';
        
        // 更新状态
        $this->status = $status;
        $this->save();
        
        return $status;
    }
}
