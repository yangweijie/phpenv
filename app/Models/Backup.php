<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class Backup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'path',
        'size',
        'type',
        'description',
        'status',
    ];

    protected $casts = [
        'size' => 'integer',
        'status' => 'boolean',
    ];

    // 备份类型常量
    const TYPE_DATABASE = 'database';
    const TYPE_SETTINGS = 'settings';
    const TYPE_FULL = 'full';

    // 获取备份类型列表
    public static function getTypes()
    {
        return [
            self::TYPE_DATABASE => '数据库备份',
            self::TYPE_SETTINGS => '设置备份',
            self::TYPE_FULL => '完整备份',
        ];
    }

    // 创建备份
    public static function createBackup($type, $description = null)
    {
        // 创建备份目录
        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        // 生成备份文件名
        $timestamp = date('Y-m-d_H-i-s');
        $name = "backup_{$type}_{$timestamp}";
        $path = "{$backupDir}/{$name}.zip";

        // 创建备份
        switch ($type) {
            case self::TYPE_DATABASE:
                $result = self::createDatabaseBackup($path);
                break;
            case self::TYPE_SETTINGS:
                $result = self::createSettingsBackup($path);
                break;
            case self::TYPE_FULL:
                $result = self::createFullBackup($path);
                break;
            default:
                throw new \Exception('无效的备份类型');
        }

        if (!$result) {
            throw new \Exception('创建备份失败');
        }

        // 获取备份文件大小
        $size = File::size($path);

        // 创建备份记录
        $backup = self::create([
            'name' => $name,
            'path' => $path,
            'size' => $size,
            'type' => $type,
            'description' => $description,
            'status' => true,
        ]);

        // 记录日志
        ActivityLog::logSystem(
            "创建{$backup->getTypeName()}成功", 
            ['path' => $backup->path, 'size' => $backup->getFormattedSize()], 
            ActivityLog::TYPE_SUCCESS
        );

        return $backup;
    }

    // 创建数据库备份
    private static function createDatabaseBackup($path)
    {
        // 获取数据库配置
        $connection = DB::getDefaultConnection();
        $config = DB::getConfig($connection);

        // 创建临时目录
        $tempDir = storage_path('app/temp/db_backup_' . time());
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // 导出数据库表结构和数据
        $tables = DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . $config['database'];

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            
            // 导出表结构
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $createTableSql = $createTable[0]->{'Create Table'} . ';';
            File::put("{$tempDir}/{$tableName}_structure.sql", $createTableSql);
            
            // 导出表数据
            $rows = DB::table($tableName)->get();
            $insertSql = '';
            
            foreach ($rows as $row) {
                $values = [];
                foreach ((array)$row as $value) {
                    $values[] = is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                }
                
                $insertSql .= "INSERT INTO `{$tableName}` VALUES (" . implode(', ', $values) . ");\n";
            }
            
            if (!empty($insertSql)) {
                File::put("{$tempDir}/{$tableName}_data.sql", $insertSql);
            }
        }

        // 创建配置信息文件
        $configInfo = [
            'database' => $config['database'],
            'driver' => $config['driver'],
            'tables' => array_map(function ($table) use ($tableKey) {
                return $table->$tableKey;
            }, $tables),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
        File::put("{$tempDir}/config.json", json_encode($configInfo, JSON_PRETTY_PRINT));

        // 创建ZIP文件
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = File::allFiles($tempDir);
            
            foreach ($files as $file) {
                $zip->addFile($file->getPathname(), $file->getRelativePathname());
            }
            
            $zip->close();
            
            // 清理临时目录
            File::deleteDirectory($tempDir);
            
            return true;
        }
        
        // 清理临时目录
        File::deleteDirectory($tempDir);
        
        return false;
    }

    // 创建设置备份
    private static function createSettingsBackup($path)
    {
        // 创建临时目录
        $tempDir = storage_path('app/temp/settings_backup_' . time());
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // 导出设置
        $settings = Setting::all();
        File::put("{$tempDir}/settings.json", json_encode($settings->toArray(), JSON_PRETTY_PRINT));

        // 导出环境变量
        $envVars = EnvironmentVariable::all();
        File::put("{$tempDir}/environment_variables.json", json_encode($envVars->toArray(), JSON_PRETTY_PRINT));

        // 导出PHP版本
        $phpVersions = PhpVersion::all();
        File::put("{$tempDir}/php_versions.json", json_encode($phpVersions->toArray(), JSON_PRETTY_PRINT));

        // 导出服务
        $services = Service::all();
        File::put("{$tempDir}/services.json", json_encode($services->toArray(), JSON_PRETTY_PRINT));

        // 导出网站
        $websites = Website::all();
        File::put("{$tempDir}/websites.json", json_encode($websites->toArray(), JSON_PRETTY_PRINT));

        // 导出项目
        $projects = Project::all();
        File::put("{$tempDir}/projects.json", json_encode($projects->toArray(), JSON_PRETTY_PRINT));

        // 创建配置信息文件
        $configInfo = [
            'created_at' => date('Y-m-d H:i:s'),
            'settings_count' => $settings->count(),
            'env_vars_count' => $envVars->count(),
            'php_versions_count' => $phpVersions->count(),
            'services_count' => $services->count(),
            'websites_count' => $websites->count(),
            'projects_count' => $projects->count(),
        ];
        
        File::put("{$tempDir}/config.json", json_encode($configInfo, JSON_PRETTY_PRINT));

        // 创建ZIP文件
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = File::allFiles($tempDir);
            
            foreach ($files as $file) {
                $zip->addFile($file->getPathname(), $file->getRelativePathname());
            }
            
            $zip->close();
            
            // 清理临时目录
            File::deleteDirectory($tempDir);
            
            return true;
        }
        
        // 清理临时目录
        File::deleteDirectory($tempDir);
        
        return false;
    }

    // 创建完整备份
    private static function createFullBackup($path)
    {
        // 创建临时目录
        $tempDir = storage_path('app/temp/full_backup_' . time());
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // 创建数据库备份
        $dbBackupPath = "{$tempDir}/database";
        File::makeDirectory($dbBackupPath, 0755, true);
        if (!self::createDatabaseBackup("{$dbBackupPath}/database.zip")) {
            // 清理临时目录
            File::deleteDirectory($tempDir);
            return false;
        }

        // 创建设置备份
        $settingsBackupPath = "{$tempDir}/settings";
        File::makeDirectory($settingsBackupPath, 0755, true);
        if (!self::createSettingsBackup("{$settingsBackupPath}/settings.zip")) {
            // 清理临时目录
            File::deleteDirectory($tempDir);
            return false;
        }

        // 创建配置信息文件
        $configInfo = [
            'created_at' => date('Y-m-d H:i:s'),
            'app_version' => Setting::get('app_version', '1.0.0'),
            'backup_type' => self::TYPE_FULL,
        ];
        
        File::put("{$tempDir}/config.json", json_encode($configInfo, JSON_PRETTY_PRINT));

        // 创建ZIP文件
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = File::allFiles($tempDir);
            
            foreach ($files as $file) {
                $zip->addFile($file->getPathname(), $file->getRelativePathname());
            }
            
            $zip->close();
            
            // 清理临时目录
            File::deleteDirectory($tempDir);
            
            return true;
        }
        
        // 清理临时目录
        File::deleteDirectory($tempDir);
        
        return false;
    }

    // 恢复备份
    public function restore()
    {
        if (!File::exists($this->path)) {
            throw new \Exception('备份文件不存在');
        }

        // 创建临时目录
        $tempDir = storage_path('app/temp/restore_' . time());
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // 解压备份文件
        $zip = new ZipArchive();
        if ($zip->open($this->path) === true) {
            $zip->extractTo($tempDir);
            $zip->close();
        } else {
            throw new \Exception('解压备份文件失败');
        }

        // 根据备份类型恢复
        switch ($this->type) {
            case self::TYPE_DATABASE:
                $result = $this->restoreDatabase($tempDir);
                break;
            case self::TYPE_SETTINGS:
                $result = $this->restoreSettings($tempDir);
                break;
            case self::TYPE_FULL:
                $result = $this->restoreFull($tempDir);
                break;
            default:
                throw new \Exception('无效的备份类型');
        }

        // 清理临时目录
        File::deleteDirectory($tempDir);

        if (!$result) {
            throw new \Exception('恢复备份失败');
        }

        // 记录日志
        ActivityLog::logSystem(
            "恢复{$this->getTypeName()}成功", 
            ['path' => $this->path], 
            ActivityLog::TYPE_SUCCESS
        );

        return true;
    }

    // 恢复数据库备份
    private function restoreDatabase($tempDir)
    {
        // 检查配置文件
        if (!File::exists("{$tempDir}/config.json")) {
            return false;
        }

        // 读取配置信息
        $configInfo = json_decode(File::get("{$tempDir}/config.json"), true);
        if (!$configInfo || !isset($configInfo['tables'])) {
            return false;
        }

        // 获取数据库配置
        $connection = DB::getDefaultConnection();
        $config = DB::getConfig($connection);

        // 恢复数据库表
        foreach ($configInfo['tables'] as $tableName) {
            // 删除表（如果存在）
            DB::statement("DROP TABLE IF EXISTS `{$tableName}`");

            // 恢复表结构
            if (File::exists("{$tempDir}/{$tableName}_structure.sql")) {
                $structureSql = File::get("{$tempDir}/{$tableName}_structure.sql");
                DB::statement($structureSql);
            }

            // 恢复表数据
            if (File::exists("{$tempDir}/{$tableName}_data.sql")) {
                $dataSql = File::get("{$tempDir}/{$tableName}_data.sql");
                $statements = explode(";\n", $dataSql);
                
                foreach ($statements as $statement) {
                    if (!empty(trim($statement))) {
                        DB::statement($statement);
                    }
                }
            }
        }

        return true;
    }

    // 恢复设置备份
    private function restoreSettings($tempDir)
    {
        // 恢复设置
        if (File::exists("{$tempDir}/settings.json")) {
            $settings = json_decode(File::get("{$tempDir}/settings.json"), true);
            
            if ($settings) {
                // 清空现有设置
                Setting::query()->delete();
                
                // 导入设置
                foreach ($settings as $setting) {
                    Setting::create($setting);
                }
            }
        }

        // 恢复环境变量
        if (File::exists("{$tempDir}/environment_variables.json")) {
            $envVars = json_decode(File::get("{$tempDir}/environment_variables.json"), true);
            
            if ($envVars) {
                // 清空现有环境变量
                EnvironmentVariable::query()->delete();
                
                // 导入环境变量
                foreach ($envVars as $envVar) {
                    EnvironmentVariable::create($envVar);
                }
            }
        }

        // 恢复PHP版本
        if (File::exists("{$tempDir}/php_versions.json")) {
            $phpVersions = json_decode(File::get("{$tempDir}/php_versions.json"), true);
            
            if ($phpVersions) {
                // 清空现有PHP版本
                PhpVersion::query()->delete();
                
                // 导入PHP版本
                foreach ($phpVersions as $phpVersion) {
                    PhpVersion::create($phpVersion);
                }
            }
        }

        // 恢复服务
        if (File::exists("{$tempDir}/services.json")) {
            $services = json_decode(File::get("{$tempDir}/services.json"), true);
            
            if ($services) {
                // 清空现有服务
                Service::query()->delete();
                
                // 导入服务
                foreach ($services as $service) {
                    Service::create($service);
                }
            }
        }

        // 恢复网站
        if (File::exists("{$tempDir}/websites.json")) {
            $websites = json_decode(File::get("{$tempDir}/websites.json"), true);
            
            if ($websites) {
                // 清空现有网站
                Website::query()->delete();
                
                // 导入网站
                foreach ($websites as $website) {
                    Website::create($website);
                }
            }
        }

        // 恢复项目
        if (File::exists("{$tempDir}/projects.json")) {
            $projects = json_decode(File::get("{$tempDir}/projects.json"), true);
            
            if ($projects) {
                // 清空现有项目
                Project::query()->delete();
                
                // 导入项目
                foreach ($projects as $project) {
                    Project::create($project);
                }
            }
        }

        return true;
    }

    // 恢复完整备份
    private function restoreFull($tempDir)
    {
        // 检查配置文件
        if (!File::exists("{$tempDir}/config.json")) {
            return false;
        }

        // 读取配置信息
        $configInfo = json_decode(File::get("{$tempDir}/config.json"), true);
        if (!$configInfo || !isset($configInfo['backup_type']) || $configInfo['backup_type'] !== self::TYPE_FULL) {
            return false;
        }

        // 恢复数据库
        if (File::exists("{$tempDir}/database/database.zip")) {
            // 创建临时目录
            $dbTempDir = "{$tempDir}/database_temp";
            File::makeDirectory($dbTempDir, 0755, true);
            
            // 解压数据库备份
            $zip = new ZipArchive();
            if ($zip->open("{$tempDir}/database/database.zip") === true) {
                $zip->extractTo($dbTempDir);
                $zip->close();
                
                // 恢复数据库
                if (!$this->restoreDatabase($dbTempDir)) {
                    // 清理临时目录
                    File::deleteDirectory($dbTempDir);
                    return false;
                }
                
                // 清理临时目录
                File::deleteDirectory($dbTempDir);
            } else {
                return false;
            }
        }

        // 恢复设置
        if (File::exists("{$tempDir}/settings/settings.zip")) {
            // 创建临时目录
            $settingsTempDir = "{$tempDir}/settings_temp";
            File::makeDirectory($settingsTempDir, 0755, true);
            
            // 解压设置备份
            $zip = new ZipArchive();
            if ($zip->open("{$tempDir}/settings/settings.zip") === true) {
                $zip->extractTo($settingsTempDir);
                $zip->close();
                
                // 恢复设置
                if (!$this->restoreSettings($settingsTempDir)) {
                    // 清理临时目录
                    File::deleteDirectory($settingsTempDir);
                    return false;
                }
                
                // 清理临时目录
                File::deleteDirectory($settingsTempDir);
            } else {
                return false;
            }
        }

        return true;
    }

    // 下载备份
    public function download()
    {
        if (!File::exists($this->path)) {
            throw new \Exception('备份文件不存在');
        }

        return response()->download($this->path);
    }

    // 删除备份
    public function deleteBackup()
    {
        if (File::exists($this->path)) {
            File::delete($this->path);
        }

        // 记录日志
        ActivityLog::logSystem(
            "删除{$this->getTypeName()}", 
            ['path' => $this->path], 
            ActivityLog::TYPE_INFO
        );

        return $this->delete();
    }

    // 获取备份类型名称
    public function getTypeName()
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }

    // 获取格式化的备份大小
    public function getFormattedSize()
    {
        $size = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;
        
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }
        
        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    // 清理过期备份
    public static function cleanupExpiredBackups($days = 30)
    {
        $expiredDate = now()->subDays($days);
        $expiredBackups = self::where('created_at', '<', $expiredDate)->get();
        
        foreach ($expiredBackups as $backup) {
            $backup->deleteBackup();
        }
        
        return $expiredBackups->count();
    }
}
