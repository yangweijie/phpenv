<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'path',
        'type',
        'description',
        'php_version_id',
        'website_id',
        'git_repository',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // 项目类型常量
    const TYPE_LARAVEL = 'laravel';
    const TYPE_WORDPRESS = 'wordpress';
    const TYPE_SYMFONY = 'symfony';
    const TYPE_CODEIGNITER = 'codeigniter';
    const TYPE_THINKPHP = 'thinkphp';
    const TYPE_CUSTOM = 'custom';

    // 获取项目类型列表
    public static function getTypes()
    {
        return [
            self::TYPE_LARAVEL => 'Laravel',
            self::TYPE_WORDPRESS => 'WordPress',
            self::TYPE_SYMFONY => 'Symfony',
            self::TYPE_CODEIGNITER => 'CodeIgniter',
            self::TYPE_THINKPHP => 'ThinkPHP',
            self::TYPE_CUSTOM => '自定义项目',
        ];
    }

    // 关联PHP版本
    public function phpVersion()
    {
        return $this->belongsTo(PhpVersion::class);
    }

    // 关联网站
    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    // 创建项目
    public static function createProject($data)
    {
        // 创建项目目录
        if (!File::exists($data['path'])) {
            File::makeDirectory($data['path'], 0755, true);
        }

        // 根据项目类型创建不同的项目
        switch ($data['type']) {
            case self::TYPE_LARAVEL:
                return self::createLaravelProject($data);
            case self::TYPE_WORDPRESS:
                return self::createWordPressProject($data);
            case self::TYPE_SYMFONY:
                return self::createSymfonyProject($data);
            case self::TYPE_CODEIGNITER:
                return self::createCodeIgniterProject($data);
            case self::TYPE_THINKPHP:
                return self::createThinkPHPProject($data);
            case self::TYPE_CUSTOM:
            default:
                return self::createCustomProject($data);
        }
    }

    // 创建Laravel项目
    private static function createLaravelProject($data)
    {
        // 获取PHP版本
        $phpVersion = PhpVersion::find($data['php_version_id']);
        
        if (!$phpVersion) {
            throw new \Exception('未找到PHP版本');
        }
        
        // 使用Composer创建Laravel项目
        $command = $phpVersion->path . '\php.exe ' . 
                   'composer.phar create-project --prefer-dist laravel/laravel ' . 
                   $data['path'];
        
        exec($command, $output, $returnVar);
        
        if ($returnVar !== 0) {
            throw new \Exception('创建Laravel项目失败: ' . implode("\n", $output));
        }
        
        // 创建项目记录
        $project = self::create([
            'name' => $data['name'],
            'path' => $data['path'],
            'type' => self::TYPE_LARAVEL,
            'description' => $data['description'] ?? 'Laravel项目',
            'php_version_id' => $data['php_version_id'],
            'website_id' => $data['website_id'] ?? null,
            'git_repository' => $data['git_repository'] ?? null,
            'status' => true,
        ]);
        
        // 记录日志
        ActivityLog::logSystem(
            "创建Laravel项目 [{$project->name}] 成功", 
            ['path' => $project->path], 
            ActivityLog::TYPE_SUCCESS
        );
        
        return $project;
    }

    // 创建WordPress项目
    private static function createWordPressProject($data)
    {
        // 获取PHP版本
        $phpVersion = PhpVersion::find($data['php_version_id']);
        
        if (!$phpVersion) {
            throw new \Exception('未找到PHP版本');
        }
        
        // 下载WordPress
        $wpUrl = 'https://wordpress.org/latest.zip';
        $zipFile = $data['path'] . '/wordpress.zip';
        
        file_put_contents($zipFile, file_get_contents($wpUrl));
        
        // 解压WordPress
        $zip = new \ZipArchive;
        if ($zip->open($zipFile) === TRUE) {
            $zip->extractTo($data['path']);
            $zip->close();
            
            // 移动文件
            $files = File::allFiles($data['path'] . '/wordpress');
            foreach ($files as $file) {
                $newPath = $data['path'] . '/' . $file->getRelativePathname();
                File::copy($file->getPathname(), $newPath);
            }
            
            // 删除临时文件
            File::deleteDirectory($data['path'] . '/wordpress');
            File::delete($zipFile);
        } else {
            throw new \Exception('解压WordPress失败');
        }
        
        // 创建项目记录
        $project = self::create([
            'name' => $data['name'],
            'path' => $data['path'],
            'type' => self::TYPE_WORDPRESS,
            'description' => $data['description'] ?? 'WordPress项目',
            'php_version_id' => $data['php_version_id'],
            'website_id' => $data['website_id'] ?? null,
            'git_repository' => $data['git_repository'] ?? null,
            'status' => true,
        ]);
        
        // 记录日志
        ActivityLog::logSystem(
            "创建WordPress项目 [{$project->name}] 成功", 
            ['path' => $project->path], 
            ActivityLog::TYPE_SUCCESS
        );
        
        return $project;
    }

    // 创建Symfony项目
    private static function createSymfonyProject($data)
    {
        // 获取PHP版本
        $phpVersion = PhpVersion::find($data['php_version_id']);
        
        if (!$phpVersion) {
            throw new \Exception('未找到PHP版本');
        }
        
        // 使用Composer创建Symfony项目
        $command = $phpVersion->path . '\php.exe ' . 
                   'composer.phar create-project symfony/skeleton ' . 
                   $data['path'];
        
        exec($command, $output, $returnVar);
        
        if ($returnVar !== 0) {
            throw new \Exception('创建Symfony项目失败: ' . implode("\n", $output));
        }
        
        // 创建项目记录
        $project = self::create([
            'name' => $data['name'],
            'path' => $data['path'],
            'type' => self::TYPE_SYMFONY,
            'description' => $data['description'] ?? 'Symfony项目',
            'php_version_id' => $data['php_version_id'],
            'website_id' => $data['website_id'] ?? null,
            'git_repository' => $data['git_repository'] ?? null,
            'status' => true,
        ]);
        
        // 记录日志
        ActivityLog::logSystem(
            "创建Symfony项目 [{$project->name}] 成功", 
            ['path' => $project->path], 
            ActivityLog::TYPE_SUCCESS
        );
        
        return $project;
    }

    // 创建CodeIgniter项目
    private static function createCodeIgniterProject($data)
    {
        // 获取PHP版本
        $phpVersion = PhpVersion::find($data['php_version_id']);
        
        if (!$phpVersion) {
            throw new \Exception('未找到PHP版本');
        }
        
        // 使用Composer创建CodeIgniter项目
        $command = $phpVersion->path . '\php.exe ' . 
                   'composer.phar create-project codeigniter4/appstarter ' . 
                   $data['path'];
        
        exec($command, $output, $returnVar);
        
        if ($returnVar !== 0) {
            throw new \Exception('创建CodeIgniter项目失败: ' . implode("\n", $output));
        }
        
        // 创建项目记录
        $project = self::create([
            'name' => $data['name'],
            'path' => $data['path'],
            'type' => self::TYPE_CODEIGNITER,
            'description' => $data['description'] ?? 'CodeIgniter项目',
            'php_version_id' => $data['php_version_id'],
            'website_id' => $data['website_id'] ?? null,
            'git_repository' => $data['git_repository'] ?? null,
            'status' => true,
        ]);
        
        // 记录日志
        ActivityLog::logSystem(
            "创建CodeIgniter项目 [{$project->name}] 成功", 
            ['path' => $project->path], 
            ActivityLog::TYPE_SUCCESS
        );
        
        return $project;
    }

    // 创建ThinkPHP项目
    private static function createThinkPHPProject($data)
    {
        // 获取PHP版本
        $phpVersion = PhpVersion::find($data['php_version_id']);
        
        if (!$phpVersion) {
            throw new \Exception('未找到PHP版本');
        }
        
        // 使用Composer创建ThinkPHP项目
        $command = $phpVersion->path . '\php.exe ' . 
                   'composer.phar create-project topthink/think ' . 
                   $data['path'];
        
        exec($command, $output, $returnVar);
        
        if ($returnVar !== 0) {
            throw new \Exception('创建ThinkPHP项目失败: ' . implode("\n", $output));
        }
        
        // 创建项目记录
        $project = self::create([
            'name' => $data['name'],
            'path' => $data['path'],
            'type' => self::TYPE_THINKPHP,
            'description' => $data['description'] ?? 'ThinkPHP项目',
            'php_version_id' => $data['php_version_id'],
            'website_id' => $data['website_id'] ?? null,
            'git_repository' => $data['git_repository'] ?? null,
            'status' => true,
        ]);
        
        // 记录日志
        ActivityLog::logSystem(
            "创建ThinkPHP项目 [{$project->name}] 成功", 
            ['path' => $project->path], 
            ActivityLog::TYPE_SUCCESS
        );
        
        return $project;
    }

    // 创建自定义项目
    private static function createCustomProject($data)
    {
        // 创建项目记录
        $project = self::create([
            'name' => $data['name'],
            'path' => $data['path'],
            'type' => self::TYPE_CUSTOM,
            'description' => $data['description'] ?? '自定义项目',
            'php_version_id' => $data['php_version_id'],
            'website_id' => $data['website_id'] ?? null,
            'git_repository' => $data['git_repository'] ?? null,
            'status' => true,
        ]);
        
        // 记录日志
        ActivityLog::logSystem(
            "创建自定义项目 [{$project->name}] 成功", 
            ['path' => $project->path], 
            ActivityLog::TYPE_SUCCESS
        );
        
        return $project;
    }

    // 打开项目
    public function openProject()
    {
        if (!File::exists($this->path)) {
            throw new \Exception('项目路径不存在');
        }
        
        // 在Windows上打开资源管理器
        exec('explorer "' . $this->path . '"');
        
        // 记录日志
        ActivityLog::logSystem(
            "打开项目 [{$this->name}]", 
            ['path' => $this->path], 
            ActivityLog::TYPE_INFO
        );
        
        return true;
    }

    // 打开项目网站
    public function openWebsite()
    {
        if (!$this->website) {
            throw new \Exception('项目未关联网站');
        }
        
        // 打开网站
        $url = 'http://' . $this->website->domain;
        exec('start ' . $url);
        
        // 记录日志
        ActivityLog::logSystem(
            "打开项目网站 [{$this->name}]", 
            ['url' => $url], 
            ActivityLog::TYPE_INFO
        );
        
        return true;
    }

    // 获取项目大小
    public function getSize()
    {
        if (!File::exists($this->path)) {
            return 0;
        }
        
        $size = 0;
        
        foreach (File::allFiles($this->path) as $file) {
            $size += $file->getSize();
        }
        
        return $size;
    }

    // 格式化项目大小
    public function getFormattedSize()
    {
        $size = $this->getSize();
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;
        
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }
        
        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    // 获取项目文件数量
    public function getFileCount()
    {
        if (!File::exists($this->path)) {
            return 0;
        }
        
        return count(File::allFiles($this->path));
    }

    // 获取项目目录数量
    public function getDirectoryCount()
    {
        if (!File::exists($this->path)) {
            return 0;
        }
        
        return count(File::directories($this->path, true));
    }

    // 获取项目最后修改时间
    public function getLastModified()
    {
        if (!File::exists($this->path)) {
            return null;
        }
        
        return File::lastModified($this->path);
    }

    // 获取项目的Composer依赖
    public function getComposerDependencies()
    {
        $composerJsonPath = $this->path . '/composer.json';
        
        if (!File::exists($composerJsonPath)) {
            return [];
        }
        
        $composerJson = json_decode(File::get($composerJsonPath), true);
        
        if (!$composerJson || !isset($composerJson['require'])) {
            return [];
        }
        
        return $composerJson['require'];
    }

    // 获取项目的Git信息
    public function getGitInfo()
    {
        if (!File::exists($this->path . '/.git')) {
            return null;
        }
        
        // 获取当前分支
        $command = 'cd ' . $this->path . ' && git branch --show-current';
        exec($command, $branchOutput, $branchReturnVar);
        
        // 获取最后一次提交
        $command = 'cd ' . $this->path . ' && git log -1 --pretty=format:"%h - %s (%cr)"';
        exec($command, $logOutput, $logReturnVar);
        
        // 获取远程仓库
        $command = 'cd ' . $this->path . ' && git remote -v';
        exec($command, $remoteOutput, $remoteReturnVar);
        
        if ($branchReturnVar !== 0 || $logReturnVar !== 0 || $remoteReturnVar !== 0) {
            return null;
        }
        
        return [
            'branch' => $branchOutput[0] ?? null,
            'last_commit' => $logOutput[0] ?? null,
            'remote' => $remoteOutput[0] ?? null,
        ];
    }
}
