<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use ZipArchive;

class Plugin extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'author',
        'website',
        'status',
        'settings',
        'installed_at',
    ];

    protected $casts = [
        'status' => 'boolean',
        'settings' => 'array',
        'installed_at' => 'datetime',
    ];

    // 插件状态常量
    const STATUS_ACTIVE = true;
    const STATUS_INACTIVE = false;

    // 获取插件目录
    public static function getPluginsDirectory()
    {
        return base_path('plugins');
    }

    // 获取插件路径
    public function getPluginPath()
    {
        return self::getPluginsDirectory() . '/' . $this->slug;
    }

    // 获取插件配置文件路径
    public function getConfigPath()
    {
        return $this->getPluginPath() . '/plugin.json';
    }

    // 获取插件主类路径
    public function getMainClassPath()
    {
        return $this->getPluginPath() . '/src/' . Str::studly($this->slug) . 'Plugin.php';
    }

    // 获取插件主类名称
    public function getMainClassName()
    {
        return 'Plugins\\' . Str::studly($this->slug) . '\\' . Str::studly($this->slug) . 'Plugin';
    }

    // 获取插件主类实例
    public function getMainClassInstance()
    {
        $className = $this->getMainClassName();
        
        if (!class_exists($className)) {
            return null;
        }
        
        return new $className();
    }

    // 激活插件
    public function activate()
    {
        // 检查插件是否存在
        if (!File::exists($this->getPluginPath())) {
            throw new \Exception('插件目录不存在');
        }
        
        // 检查插件主类是否存在
        if (!File::exists($this->getMainClassPath())) {
            throw new \Exception('插件主类不存在');
        }
        
        // 获取插件主类实例
        $instance = $this->getMainClassInstance();
        
        if (!$instance) {
            throw new \Exception('无法实例化插件主类');
        }
        
        // 调用插件激活方法
        if (method_exists($instance, 'activate')) {
            $instance->activate();
        }
        
        // 更新插件状态
        $this->status = self::STATUS_ACTIVE;
        $this->save();
        
        // 记录日志
        ActivityLog::logSystem(
            "激活插件 [{$this->name}]", 
            ['slug' => $this->slug, 'version' => $this->version], 
            ActivityLog::TYPE_SUCCESS
        );
        
        return true;
    }

    // 停用插件
    public function deactivate()
    {
        // 检查插件是否存在
        if (!File::exists($this->getPluginPath())) {
            throw new \Exception('插件目录不存在');
        }
        
        // 检查插件主类是否存在
        if (!File::exists($this->getMainClassPath())) {
            throw new \Exception('插件主类不存在');
        }
        
        // 获取插件主类实例
        $instance = $this->getMainClassInstance();
        
        if (!$instance) {
            throw new \Exception('无法实例化插件主类');
        }
        
        // 调用插件停用方法
        if (method_exists($instance, 'deactivate')) {
            $instance->deactivate();
        }
        
        // 更新插件状态
        $this->status = self::STATUS_INACTIVE;
        $this->save();
        
        // 记录日志
        ActivityLog::logSystem(
            "停用插件 [{$this->name}]", 
            ['slug' => $this->slug, 'version' => $this->version], 
            ActivityLog::TYPE_SUCCESS
        );
        
        return true;
    }

    // 安装插件
    public static function installPlugin($zipPath)
    {
        // 检查插件目录是否存在
        $pluginsDirectory = self::getPluginsDirectory();
        if (!File::exists($pluginsDirectory)) {
            File::makeDirectory($pluginsDirectory, 0755, true);
        }
        
        // 创建临时目录
        $tempDir = storage_path('app/temp/plugin_' . time());
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }
        
        // 解压插件
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \Exception('无法打开插件包');
        }
        
        $zip->extractTo($tempDir);
        $zip->close();
        
        // 检查插件配置文件
        $configFile = $tempDir . '/plugin.json';
        if (!File::exists($configFile)) {
            // 尝试查找子目录中的配置文件
            $directories = File::directories($tempDir);
            if (count($directories) === 1) {
                $configFile = $directories[0] . '/plugin.json';
                if (!File::exists($configFile)) {
                    throw new \Exception('插件配置文件不存在');
                }
                
                // 使用子目录作为插件目录
                $tempDir = $directories[0];
            } else {
                throw new \Exception('插件配置文件不存在');
            }
        }
        
        // 读取插件配置
        $config = json_decode(File::get($configFile), true);
        
        if (!$config || !isset($config['name']) || !isset($config['slug']) || !isset($config['version'])) {
            throw new \Exception('插件配置文件格式不正确');
        }
        
        // 检查插件是否已存在
        $existingPlugin = self::where('slug', $config['slug'])->first();
        
        if ($existingPlugin) {
            // 如果插件已存在，检查版本
            if (version_compare($existingPlugin->version, $config['version'], '>=')) {
                throw new \Exception('已安装相同或更高版本的插件');
            }
            
            // 停用插件
            if ($existingPlugin->status) {
                $existingPlugin->deactivate();
            }
            
            // 删除旧版本插件
            File::deleteDirectory($existingPlugin->getPluginPath());
        }
        
        // 移动插件到插件目录
        $pluginPath = $pluginsDirectory . '/' . $config['slug'];
        
        if (File::exists($pluginPath)) {
            File::deleteDirectory($pluginPath);
        }
        
        File::copyDirectory($tempDir, $pluginPath);
        
        // 清理临时目录
        File::deleteDirectory(dirname($tempDir));
        
        // 创建或更新插件记录
        $plugin = $existingPlugin ?: new self();
        $plugin->fill([
            'name' => $config['name'],
            'slug' => $config['slug'],
            'description' => $config['description'] ?? '',
            'version' => $config['version'],
            'author' => $config['author'] ?? '',
            'website' => $config['website'] ?? '',
            'status' => self::STATUS_INACTIVE,
            'settings' => $config['settings'] ?? [],
            'installed_at' => now(),
        ]);
        
        $plugin->save();
        
        // 运行插件迁移
        $migrationsPath = $pluginPath . '/database/migrations';
        if (File::exists($migrationsPath)) {
            Artisan::call('migrate', [
                '--path' => 'plugins/' . $config['slug'] . '/database/migrations',
                '--force' => true,
            ]);
        }
        
        // 获取插件主类实例
        $instance = $plugin->getMainClassInstance();
        
        if ($instance && method_exists($instance, 'install')) {
            $instance->install();
        }
        
        // 记录日志
        ActivityLog::logSystem(
            "安装插件 [{$plugin->name}]", 
            ['slug' => $plugin->slug, 'version' => $plugin->version], 
            ActivityLog::TYPE_SUCCESS
        );
        
        return $plugin;
    }

    // 卸载插件
    public function uninstall()
    {
        // 检查插件是否存在
        if (!File::exists($this->getPluginPath())) {
            throw new \Exception('插件目录不存在');
        }
        
        // 如果插件处于激活状态，先停用
        if ($this->status) {
            $this->deactivate();
        }
        
        // 获取插件主类实例
        $instance = $this->getMainClassInstance();
        
        if ($instance && method_exists($instance, 'uninstall')) {
            $instance->uninstall();
        }
        
        // 删除插件目录
        File::deleteDirectory($this->getPluginPath());
        
        // 记录日志
        ActivityLog::logSystem(
            "卸载插件 [{$this->name}]", 
            ['slug' => $this->slug, 'version' => $this->version], 
            ActivityLog::TYPE_SUCCESS
        );
        
        // 删除插件记录
        return $this->delete();
    }

    // 更新插件设置
    public function updateSettings($settings)
    {
        $this->settings = $settings;
        $this->save();
        
        // 获取插件主类实例
        $instance = $this->getMainClassInstance();
        
        if ($instance && method_exists($instance, 'updateSettings')) {
            $instance->updateSettings($settings);
        }
        
        // 记录日志
        ActivityLog::logSystem(
            "更新插件 [{$this->name}] 设置", 
            ['slug' => $this->slug], 
            ActivityLog::TYPE_SUCCESS
        );
        
        return true;
    }

    // 获取所有激活的插件
    public static function getActivePlugins()
    {
        return self::where('status', self::STATUS_ACTIVE)->get();
    }

    // 获取插件设置表单
    public function getSettingsForm()
    {
        // 获取插件主类实例
        $instance = $this->getMainClassInstance();
        
        if ($instance && method_exists($instance, 'getSettingsForm')) {
            return $instance->getSettingsForm();
        }
        
        return null;
    }

    // 扫描插件目录
    public static function scanPluginsDirectory()
    {
        $pluginsDirectory = self::getPluginsDirectory();
        
        if (!File::exists($pluginsDirectory)) {
            return [];
        }
        
        $plugins = [];
        
        foreach (File::directories($pluginsDirectory) as $directory) {
            $configFile = $directory . '/plugin.json';
            
            if (File::exists($configFile)) {
                $config = json_decode(File::get($configFile), true);
                
                if ($config && isset($config['name']) && isset($config['slug']) && isset($config['version'])) {
                    $plugins[] = [
                        'name' => $config['name'],
                        'slug' => $config['slug'],
                        'description' => $config['description'] ?? '',
                        'version' => $config['version'],
                        'author' => $config['author'] ?? '',
                        'website' => $config['website'] ?? '',
                        'path' => $directory,
                    ];
                }
            }
        }
        
        return $plugins;
    }

    // 同步插件
    public static function syncPlugins()
    {
        $scannedPlugins = self::scanPluginsDirectory();
        $installedPlugins = self::all()->keyBy('slug');
        
        $added = 0;
        $updated = 0;
        $removed = 0;
        
        // 添加或更新插件
        foreach ($scannedPlugins as $plugin) {
            if (isset($installedPlugins[$plugin['slug']])) {
                // 更新插件
                $installedPlugin = $installedPlugins[$plugin['slug']];
                
                if (version_compare($installedPlugin->version, $plugin['version'], '<')) {
                    $installedPlugin->update([
                        'name' => $plugin['name'],
                        'description' => $plugin['description'],
                        'version' => $plugin['version'],
                        'author' => $plugin['author'],
                        'website' => $plugin['website'],
                    ]);
                    
                    $updated++;
                }
            } else {
                // 添加插件
                self::create([
                    'name' => $plugin['name'],
                    'slug' => $plugin['slug'],
                    'description' => $plugin['description'],
                    'version' => $plugin['version'],
                    'author' => $plugin['author'],
                    'website' => $plugin['website'],
                    'status' => self::STATUS_INACTIVE,
                    'installed_at' => now(),
                ]);
                
                $added++;
            }
        }
        
        // 移除不存在的插件
        foreach ($installedPlugins as $slug => $installedPlugin) {
            $found = false;
            
            foreach ($scannedPlugins as $plugin) {
                if ($plugin['slug'] === $slug) {
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $installedPlugin->delete();
                $removed++;
            }
        }
        
        // 记录日志
        ActivityLog::logSystem(
            "同步插件", 
            ['added' => $added, 'updated' => $updated, 'removed' => $removed], 
            ActivityLog::TYPE_SUCCESS
        );
        
        return [
            'added' => $added,
            'updated' => $updated,
            'removed' => $removed,
        ];
    }
}
