<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhpVersionResource\Pages;
use App\Models\PhpVersion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class PhpVersionResource extends Resource
{
    protected static ?string $model = PhpVersion::class;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static ?string $navigationLabel = 'PHP版本管理';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'PHP管理';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('version')
                    ->label('PHP版本')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('path')
                    ->label('安装路径')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->label('当前激活')
                    ->default(false),
                Forms\Components\Toggle::make('is_default')
                    ->label('默认版本')
                    ->default(false),
                Forms\Components\TextInput::make('extensions_path')
                    ->label('扩展目录')
                    ->maxLength(255),
                Forms\Components\TextInput::make('php_ini_path')
                    ->label('php.ini路径')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('version')
                    ->label('PHP版本')
                    ->searchable(),
                Tables\Columns\TextColumn::make('path')
                    ->label('安装路径')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('当前激活')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\IconColumn::make('is_default')
                    ->label('默认版本')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('warning')
                    ->falseColor('gray'),
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
                Action::make('activate')
                    ->label('激活')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (PhpVersion $record): bool => !$record->is_active)
                    ->action(fn (PhpVersion $record) => $record->activate()),
                Action::make('set_default')
                    ->label('设为默认')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (PhpVersion $record): bool => !$record->is_default)
                    ->action(fn (PhpVersion $record) => $record->setAsDefault()),
                Action::make('view_info')
                    ->label('查看信息')
                    ->icon('heroicon-o-information-circle')
                    ->action(function (PhpVersion $record) {
                        // 显示PHP版本信息
                        $info = $record->getVersionInfo();
                        return redirect()->back()->with('info', $info);
                    }),
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
            'index' => Pages\ListPhpVersions::route('/'),
            'create' => Pages\CreatePhpVersion::route('/create'),
            'edit' => Pages\EditPhpVersion::route('/{record}/edit'),
        ];
    }
}
