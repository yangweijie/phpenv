<?php

namespace Database\Seeders;

use App\Models\Website;
use Illuminate\Database\Seeder;

class WebsiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 创建默认网站
        Website::create([
            'name' => '本地开发站点',
            'domain' => 'localhost',
            'root_path' => 'C:\\xampp\\htdocs',
            'server_type' => Website::SERVER_TYPE_APACHE,
            'status' => true,
            'php_version_id' => 3, // PHP 8.1
            'config_path' => 'C:\\xampp\\apache\\conf\\extra\\httpd-vhosts.conf',
        ]);

        // 创建Laravel项目网站
        Website::create([
            'name' => 'Laravel项目',
            'domain' => 'laravel.test',
            'root_path' => 'C:\\xampp\\htdocs\\laravel\\public',
            'server_type' => Website::SERVER_TYPE_APACHE,
            'status' => false,
            'php_version_id' => 3, // PHP 8.1
            'config_path' => null,
        ]);

        // 创建WordPress网站
        Website::create([
            'name' => 'WordPress站点',
            'domain' => 'wordpress.test',
            'root_path' => 'C:\\xampp\\htdocs\\wordpress',
            'server_type' => Website::SERVER_TYPE_APACHE,
            'status' => false,
            'php_version_id' => 3, // PHP 8.1
            'config_path' => null,
        ]);
    }
}
