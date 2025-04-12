<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 创建Apache服务
        Service::create([
            'name' => 'Apache HTTP Server',
            'type' => Service::TYPE_APACHE,
            'status' => false,
            'path' => 'C:\\xampp\\apache\\bin\\httpd.exe',
            'port' => '80',
            'auto_start' => true,
            'config_path' => 'C:\\xampp\\apache\\conf',
            'log_path' => 'C:\\xampp\\apache\\logs',
        ]);

        // 创建MySQL服务
        Service::create([
            'name' => 'MySQL Database',
            'type' => Service::TYPE_MYSQL,
            'status' => false,
            'path' => 'C:\\xampp\\mysql\\bin\\mysqld.exe',
            'port' => '3306',
            'auto_start' => true,
            'config_path' => 'C:\\xampp\\mysql\\bin',
            'log_path' => 'C:\\xampp\\mysql\\data',
        ]);

        // 创建Nginx服务
        Service::create([
            'name' => 'Nginx Web Server',
            'type' => Service::TYPE_NGINX,
            'status' => false,
            'path' => 'C:\\nginx\\nginx.exe',
            'port' => '8080',
            'auto_start' => false,
            'config_path' => 'C:\\nginx\\conf',
            'log_path' => 'C:\\nginx\\logs',
        ]);

        // 创建Redis服务
        Service::create([
            'name' => 'Redis Server',
            'type' => Service::TYPE_REDIS,
            'status' => false,
            'path' => 'C:\\redis\\redis-server.exe',
            'port' => '6379',
            'auto_start' => false,
            'config_path' => 'C:\\redis',
            'log_path' => 'C:\\redis\\logs',
        ]);
    }
}
