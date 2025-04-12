<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'root_path',
        'server_type',
        'status',
        'php_version_id',
        'config_path',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // 服务器类型常量
    const SERVER_TYPE_APACHE = 'apache';
    const SERVER_TYPE_NGINX = 'nginx';

    // 获取服务器类型列表
    public static function getServerTypes()
    {
        return [
            self::SERVER_TYPE_APACHE => 'Apache',
            self::SERVER_TYPE_NGINX => 'Nginx',
        ];
    }

    // 关联PHP版本
    public function phpVersion()
    {
        return $this->belongsTo(PhpVersion::class);
    }

    // 创建网站配置
    public function createConfig()
    {
        if ($this->server_type === self::SERVER_TYPE_APACHE) {
            $result = $this->createApacheConfig();
        } elseif ($this->server_type === self::SERVER_TYPE_NGINX) {
            $result = $this->createNginxConfig();
        } else {
            $result = false;
        }

        // 记录日志
        \App\Models\ActivityLog::logWebsiteAction($this, '创建配置', $result);

        return $result;
    }

    // 创建Apache配置
    private function createApacheConfig()
    {
        // 获取Apache服务
        $apacheService = Service::where('type', Service::TYPE_APACHE)->first();

        if (!$apacheService) {
            return false;
        }

        // 配置文件内容
        $configContent = "<VirtualHost *:80>\n";
        $configContent .= "    ServerName " . $this->domain . "\n";
        $configContent .= "    DocumentRoot \"" . $this->root_path . "\"\n";
        $configContent .= "    <Directory \"" . $this->root_path . "\">\n";
        $configContent .= "        Options Indexes FollowSymLinks\n";
        $configContent .= "        AllowOverride All\n";
        $configContent .= "        Require all granted\n";
        $configContent .= "    </Directory>\n";

        // 如果指定了PHP版本，添加PHP处理器配置
        if ($this->php_version_id) {
            $phpVersion = PhpVersion::find($this->php_version_id);
            if ($phpVersion) {
                $configContent .= "    <FilesMatch \\.php$>\n";
                $configContent .= "        SetHandler application/x-httpd-php\n";
                $configContent .= "        Action application/x-httpd-php \"" . $phpVersion->path . "\\php-cgi.exe\"\n";
                $configContent .= "    </FilesMatch>\n";
            }
        }

        $configContent .= "    ErrorLog \"logs/" . $this->domain . "-error.log\"\n";
        $configContent .= "    CustomLog \"logs/" . $this->domain . "-access.log\" common\n";
        $configContent .= "</VirtualHost>";

        // 保存配置文件
        $configPath = $apacheService->config_path . '/sites-available/' . $this->domain . '.conf';
        file_put_contents($configPath, $configContent);

        // 创建符号链接到sites-enabled目录
        $enabledPath = $apacheService->config_path . '/sites-enabled/' . $this->domain . '.conf';
        symlink($configPath, $enabledPath);

        // 更新配置路径
        $this->config_path = $configPath;
        $this->save();

        // 重启Apache服务
        $apacheService->restart();

        return true;
    }

    // 创建Nginx配置
    private function createNginxConfig()
    {
        // 获取Nginx服务
        $nginxService = Service::where('type', Service::TYPE_NGINX)->first();

        if (!$nginxService) {
            return false;
        }

        // 配置文件内容
        $configContent = "server {\n";
        $configContent .= "    listen 80;\n";
        $configContent .= "    server_name " . $this->domain . ";\n";
        $configContent .= "    root \"" . $this->root_path . "\";\n";
        $configContent .= "    index index.html index.htm index.php;\n\n";
        $configContent .= "    location / {\n";
        $configContent .= "        try_files \$uri \$uri/ /index.php?\$query_string;\n";
        $configContent .= "    }\n\n";

        // 如果指定了PHP版本，添加PHP处理器配置
        if ($this->php_version_id) {
            $phpVersion = PhpVersion::find($this->php_version_id);
            if ($phpVersion) {
                $configContent .= "    location ~ \\.php$ {\n";
                $configContent .= "        fastcgi_pass 127.0.0.1:9000;\n";
                $configContent .= "        fastcgi_index index.php;\n";
                $configContent .= "        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;\n";
                $configContent .= "        include fastcgi_params;\n";
                $configContent .= "    }\n\n";
            }
        }

        $configContent .= "    error_log logs/" . $this->domain . "-error.log;\n";
        $configContent .= "    access_log logs/" . $this->domain . "-access.log;\n";
        $configContent .= "}";

        // 保存配置文件
        $configPath = $nginxService->config_path . '/sites-available/' . $this->domain . '.conf';
        file_put_contents($configPath, $configContent);

        // 创建符号链接到sites-enabled目录
        $enabledPath = $nginxService->config_path . '/sites-enabled/' . $this->domain . '.conf';
        symlink($configPath, $enabledPath);

        // 更新配置路径
        $this->config_path = $configPath;
        $this->save();

        // 重启Nginx服务
        $nginxService->restart();

        return true;
    }

    // 删除网站配置
    public function deleteConfig()
    {
        if (!$this->config_path || !file_exists($this->config_path)) {
            // 记录日志
            \App\Models\ActivityLog::logWebsiteAction($this, '删除配置', false);
            return false;
        }

        // 删除配置文件
        unlink($this->config_path);

        // 删除符号链接
        $enabledPath = str_replace('sites-available', 'sites-enabled', $this->config_path);
        if (file_exists($enabledPath)) {
            unlink($enabledPath);
        }

        // 重启服务
        if ($this->server_type === self::SERVER_TYPE_APACHE) {
            $service = Service::where('type', Service::TYPE_APACHE)->first();
        } elseif ($this->server_type === self::SERVER_TYPE_NGINX) {
            $service = Service::where('type', Service::TYPE_NGINX)->first();
        }

        if ($service) {
            $service->restart();
        }

        // 记录日志
        \App\Models\ActivityLog::logWebsiteAction($this, '删除配置', true);

        return true;
    }

    // 修改hosts文件
    public function updateHostsFile()
    {
        try {
            // 读取hosts文件
            $hostsPath = 'C:\Windows\System32\drivers\etc\hosts';
            $hostsContent = file_get_contents($hostsPath);

            // 检查域名是否已存在
            $domainLine = "127.0.0.1 " . $this->domain;
            if (strpos($hostsContent, $this->domain) === false) {
                // 添加域名到hosts文件
                $hostsContent .= "\n" . $domainLine;
                file_put_contents($hostsPath, $hostsContent);
            }

            // 记录日志
            \App\Models\ActivityLog::logWebsiteAction($this, '更新hosts文件', true);

            return true;
        } catch (\Exception $e) {
            // 记录日志
            \App\Models\ActivityLog::logWebsiteAction($this, '更新hosts文件', false);
            \App\Models\ActivityLog::logSystem('更新hosts文件失败: ' . $e->getMessage(), ['domain' => $this->domain], \App\Models\ActivityLog::TYPE_ERROR);

            return false;
        }
    }
}
