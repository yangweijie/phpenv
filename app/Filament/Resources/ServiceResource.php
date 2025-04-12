<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-server';

    protected static ?string $navigationLabel = '服务管理';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = '服务管理';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('服务名称')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label('服务类型')
                    ->options(Service::getTypes())
                    ->required(),
                Forms\Components\TextInput::make('path')
                    ->label('服务路径')
                    ->maxLength(255),
                Forms\Components\TextInput::make('port')
                    ->label('端口')
                    ->maxLength(255),
                Forms\Components\Toggle::make('auto_start')
                    ->label('自动启动')
                    ->default(false),
                Forms\Components\TextInput::make('config_path')
                    ->label('配置文件路径')
                    ->maxLength(255),
                Forms\Components\TextInput::make('log_path')
                    ->label('日志文件路径')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('服务名称')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('服务类型')
                    ->formatStateUsing(fn (string $state): string => Service::getTypes()[$state] ?? $state),
                Tables\Columns\IconColumn::make('status')
                    ->label('状态')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('port')
                    ->label('端口'),
                Tables\Columns\IconColumn::make('auto_start')
                    ->label('自动启动')
                    ->boolean(),
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
                //
            ])
            ->actions([
                Action::make('start')
                    ->label('启动')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (Service $record): bool => !$record->status)
                    ->action(fn (Service $record) => $record->start()),
                Action::make('stop')
                    ->label('停止')
                    ->icon('heroicon-o-stop')
                    ->color('danger')
                    ->visible(fn (Service $record): bool => $record->status)
                    ->action(fn (Service $record) => $record->stop()),
                Action::make('restart')
                    ->label('重启')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (Service $record): bool => $record->status)
                    ->action(fn (Service $record) => $record->restart()),
                Action::make('check')
                    ->label('检查状态')
                    ->icon('heroicon-o-check')
                    ->action(fn (Service $record) => $record->checkStatus()),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
