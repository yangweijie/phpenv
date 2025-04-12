<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 初始化默认角色
        $roles = Role::initializeDefaultRoles();
        
        // 初始化默认权限
        $permissions = Permission::initializeDefaultPermissions();
        
        // 为角色分配默认权限
        Permission::assignDefaultPermissions($roles);
        
        // 为现有用户分配角色
        $adminRole = $roles[Role::ROLE_ADMIN];
        $developerRole = $roles[Role::ROLE_DEVELOPER];
        
        // 将第一个用户设为管理员
        $admin = User::first();
        if ($admin) {
            $admin->role_id = $adminRole->id;
            $admin->save();
        }
        
        // 创建一个开发者用户
        User::firstOrCreate(
            ['email' => 'developer@example.com'],
            [
                'name' => 'Developer',
                'password' => bcrypt('password'),
                'role_id' => $developerRole->id,
                'is_active' => true,
            ]
        );
    }
}
