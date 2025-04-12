<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 创建默认管理员用户
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        // 运行其他种子文件
        $this->call([
            ServiceSeeder::class,
            PhpVersionSeeder::class,
            EnvironmentVariableSeeder::class,
            WebsiteSeeder::class,
            SettingSeeder::class,
            LanguageSeeder::class,
            RolePermissionSeeder::class,
        ]);
    }
}
