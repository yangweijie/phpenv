<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use App\Models\Permission;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 合并所有权限
        $permissionIds = [];
        
        foreach (['system_permissions', 'services_permissions', 'php_permissions', 'websites_permissions', 'projects_permissions', 'backups_permissions', 'users_permissions'] as $key) {
            if (isset($data[$key])) {
                $permissionIds = array_merge($permissionIds, $data[$key]);
                unset($data[$key]);
            }
        }
        
        // 保存权限关联
        $this->record = parent::mutateFormDataBeforeCreate($data);
        
        if (!empty($permissionIds)) {
            $this->record->permissions()->attach($permissionIds);
        }
        
        return $data;
    }
}
