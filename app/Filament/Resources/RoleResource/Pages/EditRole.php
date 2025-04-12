<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use App\Models\Permission;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => $this->record->users()->count() === 0),
        ];
    }
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // 获取角色的权限
        $permissions = $this->record->permissions;
        
        // 按组分类权限
        $systemPermissions = $permissions->where('group', Permission::GROUP_SYSTEM)->pluck('id')->toArray();
        $servicesPermissions = $permissions->where('group', Permission::GROUP_SERVICES)->pluck('id')->toArray();
        $phpPermissions = $permissions->where('group', Permission::GROUP_PHP)->pluck('id')->toArray();
        $websitesPermissions = $permissions->where('group', Permission::GROUP_WEBSITES)->pluck('id')->toArray();
        $projectsPermissions = $permissions->where('group', Permission::GROUP_PROJECTS)->pluck('id')->toArray();
        $backupsPermissions = $permissions->where('group', Permission::GROUP_BACKUPS)->pluck('id')->toArray();
        $usersPermissions = $permissions->where('group', Permission::GROUP_USERS)->pluck('id')->toArray();
        
        // 添加到表单数据
        $data['system_permissions'] = $systemPermissions;
        $data['services_permissions'] = $servicesPermissions;
        $data['php_permissions'] = $phpPermissions;
        $data['websites_permissions'] = $websitesPermissions;
        $data['projects_permissions'] = $projectsPermissions;
        $data['backups_permissions'] = $backupsPermissions;
        $data['users_permissions'] = $usersPermissions;
        
        return $data;
    }
    
    protected function afterSave(): void
    {
        // 获取表单数据
        $data = $this->form->getState();
        
        // 合并所有权限
        $permissionIds = [];
        
        foreach (['system_permissions', 'services_permissions', 'php_permissions', 'websites_permissions', 'projects_permissions', 'backups_permissions', 'users_permissions'] as $key) {
            if (isset($data[$key])) {
                $permissionIds = array_merge($permissionIds, $data[$key]);
            }
        }
        
        // 同步权限
        $this->record->permissions()->sync($permissionIds);
    }
}
