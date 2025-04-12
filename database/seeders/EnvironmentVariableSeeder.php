<?php

namespace Database\Seeders;

use App\Models\EnvironmentVariable;
use Illuminate\Database\Seeder;

class EnvironmentVariableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 创建PHP相关环境变量
        EnvironmentVariable::create([
            'name' => 'PHP_HOME',
            'value' => 'C:\\php81',
            'type' => EnvironmentVariable::TYPE_SYSTEM,
            'description' => 'PHP安装目录',
        ]);

        // 创建Composer相关环境变量
        EnvironmentVariable::create([
            'name' => 'COMPOSER_HOME',
            'value' => 'C:\\Users\\Administrator\\AppData\\Roaming\\Composer',
            'type' => EnvironmentVariable::TYPE_SYSTEM,
            'description' => 'Composer配置目录',
        ]);

        // 创建PATH变量
        EnvironmentVariable::create([
            'name' => 'PATH',
            'value' => 'C:\\php81',
            'type' => EnvironmentVariable::TYPE_PATH,
            'description' => 'PHP执行路径',
        ]);

        // 创建TEMP变量
        EnvironmentVariable::create([
            'name' => 'TEMP',
            'value' => 'C:\\Windows\\Temp',
            'type' => EnvironmentVariable::TYPE_SYSTEM,
            'description' => '临时文件目录',
        ]);

        // 创建TMP变量
        EnvironmentVariable::create([
            'name' => 'TMP',
            'value' => 'C:\\Windows\\Temp',
            'type' => EnvironmentVariable::TYPE_SYSTEM,
            'description' => '临时文件目录',
        ]);
    }
}
