<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;
    
    protected function afterCreate(): void
    {
        // 尝试创建项目
        try {
            Project::createProject($this->record->toArray());
            
            Notification::make()
                ->title('项目创建成功')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('项目创建失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
