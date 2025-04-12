<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'group',
    ];

    // 权限组常量
    const GROUP_SYSTEM = 'system';
    const GROUP_SERVICES = 'services';
    const GROUP_PHP = 'php';
    const GROUP_WEBSITES = 'websites';
    const GROUP_PROJECTS = 'projects';
    const GROUP_BACKUPS = 'backups';
    const GROUP_USERS = 'users';

    // 获取权限组列表
    public static function getGroups()
    {
        return [
            self::GROUP_SYSTEM => '系统管理',
            self::GROUP_SERVICES => '服务管理',
            self::GROUP_PHP => 'PHP管理',
            self::GROUP_WEBSITES => '网站管理',
            self::GROUP_PROJECTS => '项目管理',
            self::GROUP_BACKUPS => '备份管理',
            self::GROUP_USERS => '用户管理',
        ];
    }

    // 关联角色
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    // 初始化默认权限
    public static function initializeDefaultPermissions()
    {
        $permissions = [
            // 系统管理权限
            [
                'name' => '查看仪表盘',
                'slug' => 'view_dashboard',
                'description' => '查看系统仪表盘',
                'group' => self::GROUP_SYSTEM,
            ],
            [
                'name' => '查看系统信息',
                'slug' => 'view_system_info',
                'description' => '查看系统信息',
                'group' => self::GROUP_SYSTEM,
            ],
            [
                'name' => '管理系统设置',
                'slug' => 'manage_settings',
                'description' => '管理系统设置',
                'group' => self::GROUP_SYSTEM,
            ],
            [
                'name' => '查看活动日志',
                'slug' => 'view_activity_logs',
                'description' => '查看活动日志',
                'group' => self::GROUP_SYSTEM,
            ],
            [
                'name' => '管理活动日志',
                'slug' => 'manage_activity_logs',
                'description' => '管理活动日志',
                'group' => self::GROUP_SYSTEM,
            ],
            [
                'name' => '管理语言',
                'slug' => 'manage_languages',
                'description' => '管理系统语言',
                'group' => self::GROUP_SYSTEM,
            ],
            
            // 服务管理权限
            [
                'name' => '查看服务',
                'slug' => 'view_services',
                'description' => '查看服务列表',
                'group' => self::GROUP_SERVICES,
            ],
            [
                'name' => '创建服务',
                'slug' => 'create_services',
                'description' => '创建新服务',
                'group' => self::GROUP_SERVICES,
            ],
            [
                'name' => '编辑服务',
                'slug' => 'edit_services',
                'description' => '编辑服务信息',
                'group' => self::GROUP_SERVICES,
            ],
            [
                'name' => '删除服务',
                'slug' => 'delete_services',
                'description' => '删除服务',
                'group' => self::GROUP_SERVICES,
            ],
            [
                'name' => '启动服务',
                'slug' => 'start_services',
                'description' => '启动服务',
                'group' => self::GROUP_SERVICES,
            ],
            [
                'name' => '停止服务',
                'slug' => 'stop_services',
                'description' => '停止服务',
                'group' => self::GROUP_SERVICES,
            ],
            [
                'name' => '重启服务',
                'slug' => 'restart_services',
                'description' => '重启服务',
                'group' => self::GROUP_SERVICES,
            ],
            
            // PHP管理权限
            [
                'name' => '查看PHP版本',
                'slug' => 'view_php_versions',
                'description' => '查看PHP版本列表',
                'group' => self::GROUP_PHP,
            ],
            [
                'name' => '创建PHP版本',
                'slug' => 'create_php_versions',
                'description' => '创建新PHP版本',
                'group' => self::GROUP_PHP,
            ],
            [
                'name' => '编辑PHP版本',
                'slug' => 'edit_php_versions',
                'description' => '编辑PHP版本信息',
                'group' => self::GROUP_PHP,
            ],
            [
                'name' => '删除PHP版本',
                'slug' => 'delete_php_versions',
                'description' => '删除PHP版本',
                'group' => self::GROUP_PHP,
            ],
            [
                'name' => '激活PHP版本',
                'slug' => 'activate_php_versions',
                'description' => '激活PHP版本',
                'group' => self::GROUP_PHP,
            ],
            [
                'name' => '设置默认PHP版本',
                'slug' => 'set_default_php_versions',
                'description' => '设置默认PHP版本',
                'group' => self::GROUP_PHP,
            ],
            [
                'name' => '查看PHP扩展',
                'slug' => 'view_php_extensions',
                'description' => '查看PHP扩展列表',
                'group' => self::GROUP_PHP,
            ],
            [
                'name' => '管理PHP扩展',
                'slug' => 'manage_php_extensions',
                'description' => '管理PHP扩展',
                'group' => self::GROUP_PHP,
            ],
            [
                'name' => '查看Composer包',
                'slug' => 'view_composer_packages',
                'description' => '查看Composer包列表',
                'group' => self::GROUP_PHP,
            ],
            [
                'name' => '管理Composer包',
                'slug' => 'manage_composer_packages',
                'description' => '管理Composer包',
                'group' => self::GROUP_PHP,
            ],
            
            // 网站管理权限
            [
                'name' => '查看网站',
                'slug' => 'view_websites',
                'description' => '查看网站列表',
                'group' => self::GROUP_WEBSITES,
            ],
            [
                'name' => '创建网站',
                'slug' => 'create_websites',
                'description' => '创建新网站',
                'group' => self::GROUP_WEBSITES,
            ],
            [
                'name' => '编辑网站',
                'slug' => 'edit_websites',
                'description' => '编辑网站信息',
                'group' => self::GROUP_WEBSITES,
            ],
            [
                'name' => '删除网站',
                'slug' => 'delete_websites',
                'description' => '删除网站',
                'group' => self::GROUP_WEBSITES,
            ],
            [
                'name' => '管理网站配置',
                'slug' => 'manage_website_configs',
                'description' => '管理网站配置',
                'group' => self::GROUP_WEBSITES,
            ],
            
            // 项目管理权限
            [
                'name' => '查看项目',
                'slug' => 'view_projects',
                'description' => '查看项目列表',
                'group' => self::GROUP_PROJECTS,
            ],
            [
                'name' => '创建项目',
                'slug' => 'create_projects',
                'description' => '创建新项目',
                'group' => self::GROUP_PROJECTS,
            ],
            [
                'name' => '编辑项目',
                'slug' => 'edit_projects',
                'description' => '编辑项目信息',
                'group' => self::GROUP_PROJECTS,
            ],
            [
                'name' => '删除项目',
                'slug' => 'delete_projects',
                'description' => '删除项目',
                'group' => self::GROUP_PROJECTS,
            ],
            
            // 备份管理权限
            [
                'name' => '查看备份',
                'slug' => 'view_backups',
                'description' => '查看备份列表',
                'group' => self::GROUP_BACKUPS,
            ],
            [
                'name' => '创建备份',
                'slug' => 'create_backups',
                'description' => '创建新备份',
                'group' => self::GROUP_BACKUPS,
            ],
            [
                'name' => '恢复备份',
                'slug' => 'restore_backups',
                'description' => '恢复备份',
                'group' => self::GROUP_BACKUPS,
            ],
            [
                'name' => '删除备份',
                'slug' => 'delete_backups',
                'description' => '删除备份',
                'group' => self::GROUP_BACKUPS,
            ],
            
            // 用户管理权限
            [
                'name' => '查看用户',
                'slug' => 'view_users',
                'description' => '查看用户列表',
                'group' => self::GROUP_USERS,
            ],
            [
                'name' => '创建用户',
                'slug' => 'create_users',
                'description' => '创建新用户',
                'group' => self::GROUP_USERS,
            ],
            [
                'name' => '编辑用户',
                'slug' => 'edit_users',
                'description' => '编辑用户信息',
                'group' => self::GROUP_USERS,
            ],
            [
                'name' => '删除用户',
                'slug' => 'delete_users',
                'description' => '删除用户',
                'group' => self::GROUP_USERS,
            ],
            [
                'name' => '查看角色',
                'slug' => 'view_roles',
                'description' => '查看角色列表',
                'group' => self::GROUP_USERS,
            ],
            [
                'name' => '管理角色',
                'slug' => 'manage_roles',
                'description' => '管理角色',
                'group' => self::GROUP_USERS,
            ],
            [
                'name' => '查看权限',
                'slug' => 'view_permissions',
                'description' => '查看权限列表',
                'group' => self::GROUP_USERS,
            ],
            [
                'name' => '管理权限',
                'slug' => 'manage_permissions',
                'description' => '管理权限',
                'group' => self::GROUP_USERS,
            ],
        ];
        
        $createdPermissions = [];
        
        foreach ($permissions as $permission) {
            $createdPermissions[$permission['slug']] = self::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
        
        return $createdPermissions;
    }

    // 为角色分配默认权限
    public static function assignDefaultPermissions($roles)
    {
        $permissions = self::all()->keyBy('slug');
        
        // 管理员拥有所有权限
        if (isset($roles[Role::ROLE_ADMIN])) {
            $roles[Role::ROLE_ADMIN]->syncPermissions($permissions);
        }
        
        // 经理拥有除了系统管理和用户管理的大部分权限
        if (isset($roles[Role::ROLE_MANAGER])) {
            $managerPermissions = [
                'view_dashboard',
                'view_system_info',
                'view_activity_logs',
                
                'view_services',
                'create_services',
                'edit_services',
                'start_services',
                'stop_services',
                'restart_services',
                
                'view_php_versions',
                'create_php_versions',
                'edit_php_versions',
                'activate_php_versions',
                'set_default_php_versions',
                'view_php_extensions',
                'manage_php_extensions',
                'view_composer_packages',
                'manage_composer_packages',
                
                'view_websites',
                'create_websites',
                'edit_websites',
                'manage_website_configs',
                
                'view_projects',
                'create_projects',
                'edit_projects',
                
                'view_backups',
                'create_backups',
                'restore_backups',
                
                'view_users',
            ];
            
            $roles[Role::ROLE_MANAGER]->syncPermissions(
                $permissions->filter(function ($permission) use ($managerPermissions) {
                    return in_array($permission->slug, $managerPermissions);
                })
            );
        }
        
        // 开发者拥有开发相关权限
        if (isset($roles[Role::ROLE_DEVELOPER])) {
            $developerPermissions = [
                'view_dashboard',
                'view_system_info',
                
                'view_services',
                'start_services',
                'stop_services',
                'restart_services',
                
                'view_php_versions',
                'activate_php_versions',
                'view_php_extensions',
                'manage_php_extensions',
                'view_composer_packages',
                'manage_composer_packages',
                
                'view_websites',
                'create_websites',
                'edit_websites',
                'manage_website_configs',
                
                'view_projects',
                'create_projects',
                'edit_projects',
                
                'view_backups',
                'create_backups',
            ];
            
            $roles[Role::ROLE_DEVELOPER]->syncPermissions(
                $permissions->filter(function ($permission) use ($developerPermissions) {
                    return in_array($permission->slug, $developerPermissions);
                })
            );
        }
        
        // 查看者只有查看权限
        if (isset($roles[Role::ROLE_VIEWER])) {
            $viewerPermissions = [
                'view_dashboard',
                'view_system_info',
                'view_services',
                'view_php_versions',
                'view_php_extensions',
                'view_composer_packages',
                'view_websites',
                'view_projects',
                'view_backups',
            ];
            
            $roles[Role::ROLE_VIEWER]->syncPermissions(
                $permissions->filter(function ($permission) use ($viewerPermissions) {
                    return in_array($permission->slug, $viewerPermissions);
                })
            );
        }
    }
}
