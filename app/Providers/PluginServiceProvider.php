<?php

namespace App\Providers;

use App\Models\Plugin;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Event;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // 注册插件服务
        $this->app->singleton('plugins', function ($app) {
            return new \App\Services\PluginManager();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // 如果在控制台运行，不加载插件
        if ($this->app->runningInConsole()) {
            return;
        }
        
        // 加载激活的插件
        $plugins = Plugin::getActivePlugins();
        
        foreach ($plugins as $plugin) {
            $this->loadPlugin($plugin);
        }
    }

    /**
     * 加载插件
     */
    protected function loadPlugin(Plugin $plugin): void
    {
        $pluginPath = $plugin->getPluginPath();
        
        // 加载插件路由
        $routesPath = $pluginPath . '/routes/web.php';
        if (File::exists($routesPath)) {
            Route::middleware('web')
                ->namespace('Plugins\\' . ucfirst($plugin->slug) . '\\Controllers')
                ->group($routesPath);
        }
        
        // 加载插件视图
        $viewsPath = $pluginPath . '/resources/views';
        if (File::exists($viewsPath)) {
            View::addNamespace($plugin->slug, $viewsPath);
        }
        
        // 加载插件语言文件
        $langPath = $pluginPath . '/resources/lang';
        if (File::exists($langPath)) {
            $this->loadTranslationsFrom($langPath, $plugin->slug);
        }
        
        // 加载插件配置
        $configPath = $pluginPath . '/config';
        if (File::exists($configPath)) {
            $files = File::files($configPath);
            
            foreach ($files as $file) {
                $filename = pathinfo($file, PATHINFO_FILENAME);
                $this->mergeConfigFrom($file, $plugin->slug . '.' . $filename);
            }
        }
        
        // 加载插件资源
        $assetsPath = $pluginPath . '/public';
        if (File::exists($assetsPath)) {
            $this->publishes([
                $assetsPath => public_path('plugins/' . $plugin->slug),
            ], $plugin->slug . '-assets');
        }
        
        // 获取插件主类实例
        $instance = $plugin->getMainClassInstance();
        
        if ($instance) {
            // 调用插件启动方法
            if (method_exists($instance, 'boot')) {
                $instance->boot();
            }
            
            // 注册插件事件监听器
            if (method_exists($instance, 'registerEventListeners')) {
                $eventListeners = $instance->registerEventListeners();
                
                if (is_array($eventListeners)) {
                    foreach ($eventListeners as $event => $listeners) {
                        foreach ($listeners as $listener) {
                            Event::listen($event, $listener);
                        }
                    }
                }
            }
        }
    }
}
