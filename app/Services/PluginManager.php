<?php

namespace App\Services;

use App\Models\Plugin;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use ZipArchive;

class PluginManager
{
    // 插件市场URL
    protected $marketplaceUrl = 'https://example.com/api/plugins';
    
    // 缓存时间（分钟）
    protected $cacheTime = 60;
    
    /**
     * 获取所有插件
     */
    public function getAllPlugins()
    {
        return Plugin::all();
    }
    
    /**
     * 获取激活的插件
     */
    public function getActivePlugins()
    {
        return Plugin::getActivePlugins();
    }
    
    /**
     * 获取插件
     */
    public function getPlugin($slug)
    {
        return Plugin::where('slug', $slug)->first();
    }
    
    /**
     * 安装插件
     */
    public function installPlugin($zipPath)
    {
        return Plugin::installPlugin($zipPath);
    }
    
    /**
     * 卸载插件
     */
    public function uninstallPlugin($slug)
    {
        $plugin = $this->getPlugin($slug);
        
        if (!$plugin) {
            throw new \Exception('插件不存在');
        }
        
        return $plugin->uninstall();
    }
    
    /**
     * 激活插件
     */
    public function activatePlugin($slug)
    {
        $plugin = $this->getPlugin($slug);
        
        if (!$plugin) {
            throw new \Exception('插件不存在');
        }
        
        return $plugin->activate();
    }
    
    /**
     * 停用插件
     */
    public function deactivatePlugin($slug)
    {
        $plugin = $this->getPlugin($slug);
        
        if (!$plugin) {
            throw new \Exception('插件不存在');
        }
        
        return $plugin->deactivate();
    }
    
    /**
     * 更新插件设置
     */
    public function updatePluginSettings($slug, $settings)
    {
        $plugin = $this->getPlugin($slug);
        
        if (!$plugin) {
            throw new \Exception('插件不存在');
        }
        
        return $plugin->updateSettings($settings);
    }
    
    /**
     * 同步插件
     */
    public function syncPlugins()
    {
        return Plugin::syncPlugins();
    }
    
    /**
     * 获取插件市场列表
     */
    public function getMarketplacePlugins()
    {
        return Cache::remember('marketplace_plugins', $this->cacheTime, function () {
            try {
                $response = Http::get($this->marketplaceUrl);
                
                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                // 忽略错误
            }
            
            return [];
        });
    }
    
    /**
     * 从插件市场下载插件
     */
    public function downloadPluginFromMarketplace($pluginId)
    {
        try {
            $response = Http::get($this->marketplaceUrl . '/' . $pluginId . '/download');
            
            if ($response->successful()) {
                $tempFile = storage_path('app/temp/plugin_' . time() . '.zip');
                
                // 确保目录存在
                if (!File::exists(dirname($tempFile))) {
                    File::makeDirectory(dirname($tempFile), 0755, true);
                }
                
                // 保存文件
                File::put($tempFile, $response->body());
                
                // 安装插件
                $plugin = $this->installPlugin($tempFile);
                
                // 删除临时文件
                File::delete($tempFile);
                
                return $plugin;
            }
        } catch (\Exception $e) {
            throw new \Exception('下载插件失败: ' . $e->getMessage());
        }
        
        throw new \Exception('下载插件失败');
    }
    
    /**
     * 创建插件
     */
    public function createPlugin($data)
    {
        // 验证数据
        if (!isset($data['name']) || !isset($data['slug'])) {
            throw new \Exception('插件名称和标识符是必须的');
        }
        
        // 检查插件是否已存在
        if (Plugin::where('slug', $data['slug'])->exists()) {
            throw new \Exception('插件标识符已存在');
        }
        
        // 创建插件目录
        $pluginPath = Plugin::getPluginsDirectory() . '/' . $data['slug'];
        
        if (File::exists($pluginPath)) {
            throw new \Exception('插件目录已存在');
        }
        
        // 创建目录结构
        File::makeDirectory($pluginPath, 0755, true);
        File::makeDirectory($pluginPath . '/src', 0755, true);
        File::makeDirectory($pluginPath . '/resources', 0755, true);
        File::makeDirectory($pluginPath . '/resources/views', 0755, true);
        File::makeDirectory($pluginPath . '/resources/lang', 0755, true);
        File::makeDirectory($pluginPath . '/routes', 0755, true);
        File::makeDirectory($pluginPath . '/config', 0755, true);
        File::makeDirectory($pluginPath . '/database', 0755, true);
        File::makeDirectory($pluginPath . '/database/migrations', 0755, true);
        File::makeDirectory($pluginPath . '/public', 0755, true);
        
        // 创建插件配置文件
        $config = [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? '',
            'version' => $data['version'] ?? '1.0.0',
            'author' => $data['author'] ?? '',
            'website' => $data['website'] ?? '',
            'settings' => $data['settings'] ?? [],
        ];
        
        File::put($pluginPath . '/plugin.json', json_encode($config, JSON_PRETTY_PRINT));
        
        // 创建插件主类
        $mainClass = $this->generatePluginMainClass($data);
        File::put($pluginPath . '/src/' . ucfirst($data['slug']) . 'Plugin.php', $mainClass);
        
        // 创建路由文件
        $routes = $this->generatePluginRoutes($data);
        File::put($pluginPath . '/routes/web.php', $routes);
        
        // 创建插件记录
        $plugin = Plugin::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? '',
            'version' => $data['version'] ?? '1.0.0',
            'author' => $data['author'] ?? '',
            'website' => $data['website'] ?? '',
            'status' => Plugin::STATUS_INACTIVE,
            'settings' => $data['settings'] ?? [],
            'installed_at' => now(),
        ]);
        
        return $plugin;
    }
    
    /**
     * 生成插件主类
     */
    protected function generatePluginMainClass($data)
    {
        $slug = $data['slug'];
        $name = $data['name'];
        $className = ucfirst($slug) . 'Plugin';
        
        return <<<PHP
<?php

namespace Plugins\\{$className};

class {$className}
{
    /**
     * 插件激活时调用
     */
    public function activate()
    {
        // 插件激活逻辑
    }
    
    /**
     * 插件停用时调用
     */
    public function deactivate()
    {
        // 插件停用逻辑
    }
    
    /**
     * 插件安装时调用
     */
    public function install()
    {
        // 插件安装逻辑
    }
    
    /**
     * 插件卸载时调用
     */
    public function uninstall()
    {
        // 插件卸载逻辑
    }
    
    /**
     * 插件启动时调用
     */
    public function boot()
    {
        // 插件启动逻辑
    }
    
    /**
     * 注册事件监听器
     */
    public function registerEventListeners()
    {
        return [
            // 事件 => [监听器1, 监听器2, ...]
        ];
    }
    
    /**
     * 获取插件设置表单
     */
    public function getSettingsForm()
    {
        return [
            // 设置表单定义
        ];
    }
    
    /**
     * 更新插件设置
     */
    public function updateSettings(\$settings)
    {
        // 更新插件设置逻辑
    }
}
PHP;
    }
    
    /**
     * 生成插件路由
     */
    protected function generatePluginRoutes($data)
    {
        $slug = $data['slug'];
        
        return <<<PHP
<?php

use Illuminate\Support\Facades\Route;

// 插件路由定义
Route::prefix('{$slug}')->group(function () {
    // 在这里定义插件路由
});
PHP;
    }
}
