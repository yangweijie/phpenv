<?php

namespace Database\Seeders;

use App\Models\PhpVersion;
use Illuminate\Database\Seeder;

class PhpVersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 创建PHP 7.4版本
        PhpVersion::create([
            'version' => '7.4.33',
            'path' => 'C:\\php74',
            'is_active' => false,
            'is_default' => false,
            'extensions_path' => 'C:\\php74\\ext',
            'php_ini_path' => 'C:\\php74\\php.ini',
        ]);

        // 创建PHP 8.0版本
        PhpVersion::create([
            'version' => '8.0.30',
            'path' => 'C:\\php80',
            'is_active' => false,
            'is_default' => false,
            'extensions_path' => 'C:\\php80\\ext',
            'php_ini_path' => 'C:\\php80\\php.ini',
        ]);

        // 创建PHP 8.1版本
        PhpVersion::create([
            'version' => '8.1.27',
            'path' => 'C:\\php81',
            'is_active' => true,
            'is_default' => true,
            'extensions_path' => 'C:\\php81\\ext',
            'php_ini_path' => 'C:\\php81\\php.ini',
        ]);

        // 创建PHP 8.2版本
        PhpVersion::create([
            'version' => '8.2.17',
            'path' => 'C:\\php82',
            'is_active' => false,
            'is_default' => false,
            'extensions_path' => 'C:\\php82\\ext',
            'php_ini_path' => 'C:\\php82\\php.ini',
        ]);

        // 创建PHP 8.3版本
        PhpVersion::create([
            'version' => '8.3.5',
            'path' => 'C:\\php83',
            'is_active' => false,
            'is_default' => false,
            'extensions_path' => 'C:\\php83\\ext',
            'php_ini_path' => 'C:\\php83\\php.ini',
        ]);
    }
}
