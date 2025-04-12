<?php

namespace App\Filament\Resources\EnvironmentVariableResource\Pages;

use App\Filament\Resources\EnvironmentVariableResource;
use App\Models\EnvironmentVariable;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListEnvironmentVariables extends ListRecords
{
    protected static string $resource = EnvironmentVariableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('import_system_variables')
                ->label('导入系统变量')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $variables = EnvironmentVariable::getSystemVariables();
                    $count = 0;
                    
                    foreach ($variables as $variable) {
                        // 检查变量是否已存在
                        $exists = EnvironmentVariable::where('name', $variable['name'])
                            ->where('type', $variable['type'])
                            ->exists();
                        
                        if (!$exists) {
                            EnvironmentVariable::create($variable);
                            $count++;
                        }
                    }
                    
                    $this->notify('success', "成功导入 {$count} 个系统变量");
                }),
        ];
    }
}
