<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PluginResource\Pages;
use App\Models\Plugin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class PluginResource extends Resource
{
    protected static ?string $model = Plugin::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    
    protected static ?string $navigationLabel = '插件管理';
    
    protected static ?string $navigationGroup = '系统设置';
    
    protected static ?int $navigationSort = 50;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('插件名称')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->label('插件标识符')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('插件描述')
                    ->maxLength(65535),
                Forms\Components\TextInput::make('version')
                    ->label('插件版本')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('author')
                    ->label('作者')
                    ->maxLength(255),
                Forms\Components\TextInput::make('website')
                    ->label('网站')
                    ->url()
                    ->maxLength(255),
                Forms\Components\Toggle::make('status')
                    ->label('状态')
                    ->default(false),
                Forms\Components\KeyValue::make('settings')
                    ->label('设置')
                    ->keyLabel('键')
                    ->valueLabel('值')
                    ->columnSpan(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('插件名称')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('插件标识符')
                    ->searchable(),
                Tables\Columns\TextColumn::make('version')
                    ->label('版本')
                    ->sortable(),
                Tables\Columns\TextColumn::make('author')
                    ->label('作者')
                    ->searchable(),
                Tables\Columns\IconColumn::make('status')
                    ->label('状态')
                    ->boolean(),
                Tables\Columns\TextColumn::make('installed_at')
                    ->label('安装时间')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新时间')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('status')
                    ->label('状态'),
            ])
            ->actions([
                Action::make('activate')
                    ->label('激活')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Plugin $record): bool => !$record->status)
                    ->action(function (Plugin $record) {
                        try {
                            $record->activate();
                            return redirect()->back()->with('success', '插件已激活');
                        } catch (\Exception $e) {
                            return redirect()->back()->with('error', '激活插件失败: ' . $e->getMessage());
                        }
                    }),
                Action::make('deactivate')
                    ->label('停用')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Plugin $record): bool => $record->status)
                    ->action(function (Plugin $record) {
                        try {
                            $record->deactivate();
                            return redirect()->back()->with('success', '插件已停用');
                        } catch (\Exception $e) {
                            return redirect()->back()->with('error', '停用插件失败: ' . $e->getMessage());
                        }
                    }),
                Action::make('settings')
                    ->label('设置')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url(fn (Plugin $record): string => route('filament.admin.resources.plugins.settings', $record))
                    ->visible(fn (Plugin $record): bool => $record->status && $record->getSettingsForm() !== null),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->action(function (Plugin $record) {
                        try {
                            $record->uninstall();
                            return redirect()->back()->with('success', '插件已卸载');
                        } catch (\Exception $e) {
                            return redirect()->back()->with('error', '卸载插件失败: ' . $e->getMessage());
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                try {
                                    $record->uninstall();
                                } catch (\Exception $e) {
                                    // 忽略错误
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlugins::route('/'),
            'create' => Pages\CreatePlugin::route('/create'),
            'view' => Pages\ViewPlugin::route('/{record}'),
            'edit' => Pages\EditPlugin::route('/{record}/edit'),
            'settings' => Pages\PluginSettings::route('/{record}/settings'),
        ];
    }
}
