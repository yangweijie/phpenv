<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // 角色常量
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_DEVELOPER = 'developer';
    const ROLE_VIEWER = 'viewer';

    // 获取角色列表
    public static function getRoles()
    {
        return [
            self::ROLE_ADMIN => '管理员',
            self::ROLE_MANAGER => '经理',
            self::ROLE_DEVELOPER => '开发者',
            self::ROLE_VIEWER => '查看者',
        ];
    }

    // 关联用户
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // 关联权限
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    // 检查是否有权限
    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            return $this->permissions->contains('slug', $permission);
        }
        
        return $this->permissions->contains($permission);
    }

    // 分配权限
    public function assignPermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->first();
        }
        
        if (!$permission) {
            return false;
        }
        
        if (!$this->hasPermission($permission)) {
            $this->permissions()->attach($permission);
        }
        
        return true;
    }

    // 移除权限
    public function removePermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->first();
        }
        
        if (!$permission) {
            return false;
        }
        
        $this->permissions()->detach($permission);
        
        return true;
    }

    // 同步权限
    public function syncPermissions($permissions)
    {
        if (is_array($permissions)) {
            $permissionIds = [];
            
            foreach ($permissions as $permission) {
                if (is_string($permission)) {
                    $permission = Permission::where('slug', $permission)->first();
                }
                
                if ($permission) {
                    $permissionIds[] = $permission->id;
                }
            }
            
            $this->permissions()->sync($permissionIds);
        } else {
            $this->permissions()->sync($permissions);
        }
        
        return true;
    }

    // 设置为默认角色
    public function setAsDefault()
    {
        // 首先将所有角色设置为非默认状态
        self::query()->update(['is_default' => false]);
        
        // 设置当前角色为默认状态
        $this->is_default = true;
        $this->save();
        
        // 记录日志
        ActivityLog::logSystem(
            "设置 [{$this->name}] 为默认角色", 
            ['slug' => $this->slug], 
            ActivityLog::TYPE_SUCCESS
        );
        
        return true;
    }

    // 初始化默认角色
    public static function initializeDefaultRoles()
    {
        // 创建管理员角色
        $admin = self::firstOrCreate(
            ['slug' => self::ROLE_ADMIN],
            [
                'name' => '管理员',
                'description' => '系统管理员，拥有所有权限',
                'is_default' => false,
            ]
        );
        
        // 创建经理角色
        $manager = self::firstOrCreate(
            ['slug' => self::ROLE_MANAGER],
            [
                'name' => '经理',
                'description' => '项目经理，拥有大部分权限',
                'is_default' => false,
            ]
        );
        
        // 创建开发者角色
        $developer = self::firstOrCreate(
            ['slug' => self::ROLE_DEVELOPER],
            [
                'name' => '开发者',
                'description' => '开发人员，拥有开发相关权限',
                'is_default' => true,
            ]
        );
        
        // 创建查看者角色
        $viewer = self::firstOrCreate(
            ['slug' => self::ROLE_VIEWER],
            [
                'name' => '查看者',
                'description' => '只有查看权限',
                'is_default' => false,
            ]
        );
        
        return [
            self::ROLE_ADMIN => $admin,
            self::ROLE_MANAGER => $manager,
            self::ROLE_DEVELOPER => $developer,
            self::ROLE_VIEWER => $viewer,
        ];
    }
}
