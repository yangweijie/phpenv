<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BackupResource\Pages;
use App\Models\Backup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\File;

class BackupResource extends Resource
{
    protected static ?string $model = Backup::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    
    protected static ?string $navigationLabel = '备份管理';
    
    protected static ?string $navigationGroup = '系统设置';
    
    protected static ?int $navigationSort = 70;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('备份名称')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('path')
                    ->label('备份路径')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('size')
                    ->label('备份大小')
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label('备份类型')
                    ->options(Backup::getTypes())
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('备份描述')
                    ->maxLength(65535),
                Forms\Components\Toggle::make('status')
                    ->label('状态')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('备份名称')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('备份类型')
                    ->formatStateUsing(fn (string $state): string => Backup::getTypes()[$state] ?? $state),
                Tables\Columns\TextColumn::make('size')
                    ->label('备份大小')
                    ->formatStateUsing(fn (Backup $record): string => $record->getFormattedSize()),
                Tables\Columns\TextColumn::make('description')
                    ->label('备份描述')
                    ->limit(50),
                Tables\Columns\IconColumn::make('status')
                    ->label('状态')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新时间')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('备份类型')
                    ->options(Backup::getTypes()),
                Tables\Filters\TernaryFilter::make('status')
                    ->label('状态'),
            ])
            ->actions([
                Action::make('restore')
                    ->label('恢复')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('确认恢复备份')
                    ->modalDescription('您确定要恢复此备份吗？这将覆盖当前的数据。')
                    ->modalSubmitActionLabel('确认恢复')
                    ->action(function (Backup $record) {
                        try {
                            $record->restore();
                            return redirect()->back()->with('success', '备份恢复成功');
                        } catch (\Exception $e) {
                            return redirect()->back()->with('error', '备份恢复失败: ' . $e->getMessage());
                        }
                    })
                    ->visible(fn (Backup $record): bool => File::exists($record->path) && $record->status),
                Action::make('download')
                    ->label('下载')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Backup $record) {
                        try {
                            return $record->download();
                        } catch (\Exception $e) {
                            return redirect()->back()->with('error', '备份下载失败: ' . $e->getMessage());
                        }
                    })
                    ->visible(fn (Backup $record): bool => File::exists($record->path)),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->action(function (Backup $record) {
                        $record->deleteBackup();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->deleteBackup();
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListBackups::route('/'),
            'create' => Pages\CreateBackup::route('/create'),
            'view' => Pages\ViewBackup::route('/{record}'),
        ];
    }
}
