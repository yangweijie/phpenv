<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationLabel = '系统设置';
    
    protected static ?string $navigationGroup = '系统设置';
    
    protected static ?int $navigationSort = 80;

    protected static string $view = 'filament.pages.settings';
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $settings = Setting::all()->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->value];
        })->toArray();
        
        $this->form->fill($settings);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('settings')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('常规设置')
                            ->icon('heroicon-o-home')
                            ->schema([
                                Forms\Components\TextInput::make('app_name')
                                    ->label('应用名称')
                                    ->required(),
                                Forms\Components\TextInput::make('app_description')
                                    ->label('应用描述'),
                                Forms\Components\TextInput::make('app_version')
                                    ->label('应用版本')
                                    ->disabled(),
                                Forms\Components\TextInput::make('app_author')
                                    ->label('应用作者'),
                                Forms\Components\TextInput::make('app_email')
                                    ->label('联系邮箱')
                                    ->email(),
                            ]),
                        Forms\Components\Tabs\Tab::make('服务设置')
                            ->icon('heroicon-o-server')
                            ->schema([
                                Forms\Components\Toggle::make('services_auto_start')
                                    ->label('启动时自动启动服务')
                                    ->helperText('应用启动时自动启动设置为自动启动的服务'),
                                Forms\Components\TextInput::make('services_check_interval')
                                    ->label('服务状态检查间隔（分钟）')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(60),
                                Forms\Components\Toggle::make('services_notification')
                                    ->label('服务状态变化通知')
                                    ->helperText('服务状态变化时发送通知'),
                            ]),
                        Forms\Components\Tabs\Tab::make('PHP设置')
                            ->icon('heroicon-o-code-bracket')
                            ->schema([
                                Forms\Components\TextInput::make('php_default_version')
                                    ->label('默认PHP版本'),
                                Forms\Components\Toggle::make('php_auto_switch')
                                    ->label('根据项目自动切换PHP版本')
                                    ->helperText('根据项目配置自动切换PHP版本'),
                                Forms\Components\Toggle::make('php_extensions_auto_enable')
                                    ->label('自动启用常用PHP扩展')
                                    ->helperText('自动启用常用PHP扩展'),
                            ]),
                        Forms\Components\Tabs\Tab::make('安全设置')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Forms\Components\Toggle::make('security_log_actions')
                                    ->label('记录所有操作')
                                    ->helperText('记录所有用户操作'),
                                Forms\Components\TextInput::make('security_log_retention')
                                    ->label('日志保留天数')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(365),
                                Forms\Components\Toggle::make('security_backup_enabled')
                                    ->label('启用自动备份')
                                    ->helperText('启用自动备份功能'),
                                Forms\Components\TextInput::make('security_backup_interval')
                                    ->label('备份间隔（天）')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(30),
                            ]),
                    ])
                    ->columnSpan('full'),
            ]);
    }
    
    public function save(): void
    {
        $data = $this->form->getState();
        
        foreach ($data as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            
            if ($setting) {
                $setting->update([
                    'value' => $value,
                ]);
            }
        }
        
        Notification::make()
            ->title('设置已保存')
            ->success()
            ->send();
    }
}
