<?php

namespace App\Filament\Resources\ComposerPackageResource\Pages;

use App\Filament\Resources\ComposerPackageResource;
use App\Models\ComposerPackage;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListComposerPackages extends ListRecords
{
    protected static string $resource = ComposerPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('import_global_packages')
                ->label('导入全局包')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $packages = ComposerPackage::getGlobalPackages();
                    $count = 0;
                    
                    foreach ($packages as $package) {
                        // 检查包是否已存在
                        $exists = ComposerPackage::where('name', $package['name'])
                            ->where('is_global', true)
                            ->exists();
                        
                        if (!$exists) {
                            ComposerPackage::create([
                                'name' => $package['name'],
                                'version' => $package['version'],
                                'description' => $package['description'] ?? '',
                                'type' => $package['type'] ?? '',
                                'project_path' => '',
                                'is_global' => true,
                            ]);
                            $count++;
                        }
                    }
                    
                    $this->notify('success', "成功导入 {$count} 个全局包");
                }),
            Action::make('search_packages')
                ->label('搜索包')
                ->icon('heroicon-o-magnifying-glass')
                ->form([
                    Forms\Components\TextInput::make('keyword')
                        ->label('关键词')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $results = ComposerPackage::searchPackages($data['keyword']);
                    session(['search_results' => $results]);
                    return redirect()->back()->with('info', '搜索完成，找到 ' . count($results) . ' 个包');
                }),
        ];
    }
}
