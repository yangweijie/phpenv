<?php

namespace App\Filament\Resources\PhpExtensionResource\Pages;

use App\Filament\Resources\PhpExtensionResource;
use App\Models\PhpExtension;
use App\Models\PhpVersion;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListPhpExtensions extends ListRecords
{
    protected static string $resource = PhpExtensionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('scan_extensions')
                ->label('扫描扩展')
                ->icon('heroicon-o-magnifying-glass')
                ->form([
                    Forms\Components\Select::make('php_version_id')
                        ->label('PHP版本')
                        ->options(PhpVersion::all()->pluck('version', 'id'))
                        ->required()
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $phpVersion = PhpVersion::find($data['php_version_id']);
                    
                    if (!$phpVersion) {
                        $this->notify('danger', '未找到PHP版本');
                        return;
                    }
                    
                    $extensions = $phpVersion->getInstalledExtensions();
                    $count = 0;
                    
                    foreach ($extensions as $extension) {
                        // 检查扩展是否已存在
                        $exists = PhpExtension::where('name', $extension)
                            ->where('php_version_id', $phpVersion->id)
                            ->exists();
                        
                        if (!$exists) {
                            PhpExtension::create([
                                'name' => $extension,
                                'php_version_id' => $phpVersion->id,
                                'file_name' => $extension . '.dll',
                                'status' => false,
                            ]);
                            $count++;
                        }
                    }
                    
                    $this->notify('success', "成功扫描到 {$count} 个扩展");
                }),
        ];
    }
}
