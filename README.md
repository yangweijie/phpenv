# PHP开发集成环境客户端

一个基于Laravel、Filament和NativePHP的PHP开发环境管理工具，提供图形化界面管理PHP开发环境。

## 功能特点

### 托盘管理模块
- 系统托盘图标，显示服务状态
- 快捷控制Apache、MySQL等服务的启动和停止
- 状态显示：绿色表示正常，黄色表示警告，红色表示错误

### 多版本管理模块
- 支持多种PHP版本共存
- 提供版本切换功能
- 自动配置相应版本的环境变量

### 环境变量管理模块
- 提供可视化环境变量配置界面
- 支持自动配置和手动配置
- 提供常见环境变量的模板

### Composer管理模块
- 集成Composer工具
- 提供依赖管理界面
- 支持自动更新和卸载包

### PHP扩展管理模块
- 提供图形界面管理PHP扩展
- 支持一键安装常用扩展
- 提供扩展文档和示例

### Web网站构建模块
- 提供虚拟主机配置界面
- 支持文件上传和管理
- 提供网站模板和示例

## 技术架构

### 前端技术
- FilamentPHP - 提供强大的管理界面框架

### 后端技术
- NativePHP/Laravel - 提供桌面应用程序支持
- 与系统API交互，管理服务和环境变量

### 数据存储
- 使用SQLite存储用户配置
- 提供数据备份和恢复功能

## 安装说明

### 系统要求
- Windows 10/11
- PHP 8.2+
- Composer

### 安装步骤

1. 克隆仓库
```
git clone https://github.com/yourusername/phpenv.git
cd phpenv
```

2. 安装依赖
```
composer install
```

3. 配置环境
```
cp .env.example .env
php artisan key:generate
```

4. 创建数据库
```
php artisan migrate --seed
```

5. 启动应用
```
php artisan serve
```

6. 启动NativePHP应用
```
php artisan native:serve
```

## 使用说明

### 服务管理
- 在服务管理页面可以启动、停止和重启各种服务
- 可以添加新的服务和配置现有服务

### PHP版本管理
- 在PHP版本管理页面可以添加、切换和配置PHP版本
- 可以通过命令行快速切换PHP版本：`php artisan php:switch 8.1`

### 环境变量管理
- 在环境变量管理页面可以添加、编辑和删除环境变量
- 支持导入系统环境变量

### Composer管理
- 在Composer管理页面可以安装、更新和卸载Composer包
- 支持全局包和项目包管理

### PHP扩展管理
- 在PHP扩展管理页面可以启用、禁用和配置PHP扩展
- 支持扫描已安装扩展

### Web网站管理
- 在Web网站管理页面可以创建、配置和管理网站
- 支持自动配置虚拟主机和hosts文件

## 命令行工具

- `php artisan services:autostart` - 启动所有设置为自动启动的服务
- `php artisan services:check` - 检查所有服务的运行状态
- `php artisan php:switch [version]` - 切换PHP版本

## 许可证

本项目采用MIT许可证。
