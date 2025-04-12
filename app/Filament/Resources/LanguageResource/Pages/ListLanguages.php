<?php

namespace App\Filament\Resources\LanguageResource\Pages;

use App\Filament\Resources\LanguageResource;
use App\Models\Language;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms;
use Filament\Notifications\Notification;

class ListLanguages extends ListRecords
{
    protected static string $resource = LanguageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('copy_language')
                ->label('复制语言')
                ->icon('heroicon-o-document-duplicate')
                ->form([
                    Forms\Components\Select::make('source_code')
                        ->label('源语言')
                        ->options(Language::all()->pluck('name', 'code'))
                        ->required(),
                    Forms\Components\TextInput::make('target_code')
                        ->label('目标语言代码')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('target_name')
                        ->label('目标语言名称')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $data) {
                    try {
                        // 检查目标语言是否已存在
                        $exists = Language::where('code', $data['target_code'])->exists();
                        
                        if ($exists) {
                            Notification::make()
                                ->title('语言已存在')
                                ->body("语言代码 [{$data['target_code']}] 已存在")
                                ->danger()
                                ->send();
                                
                            return;
                        }
                        
                        // 创建新语言
                        $language = Language::create([
                            'name' => $data['target_name'],
                            'code' => $data['target_code'],
                            'is_default' => false,
                            'is_active' => true,
                            'direction' => Language::DIRECTION_LTR,
                        ]);
                        
                        // 复制语言文件
                        Language::copyLanguageFiles($data['source_code'], $data['target_code']);
                        
                        Notification::make()
                            ->title('语言复制成功')
                            ->success()
                            ->send();
                            
                        return redirect()->route('filament.admin.resources.languages.index');
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('语言复制失败')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
