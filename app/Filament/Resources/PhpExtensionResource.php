<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhpExtensionResource\Pages;
use App\Models\PhpExtension;
use App\Models\PhpVersion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class PhpExtensionResource extends Resource
{
    protected static ?string $model = PhpExtension::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationLabel = 'PHP扩展管理';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationGroup = 'PHP管理';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('扩展名称')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('version')
                    ->label('版本')
                    ->maxLength(255),
                Forms\Components\Toggle::make('status')
                    ->label('状态')
                    ->default(false),
                Forms\Components\Select::make('php_version_id')
                    ->label('PHP版本')
                    ->options(PhpVersion::all()->pluck('version', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\Textarea::make('description')
                    ->label('描述')
                    ->maxLength(65535),
                Forms\Components\TextInput::make('file_name')
                    ->label('文件名')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('扩展名称')
                    ->searchable(),
                Tables\Columns\TextColumn::make('version')
                    ->label('版本')
                    ->searchable(),
                Tables\Columns\IconColumn::make('status')
                    ->label('状态')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('phpVersion.version')
                    ->label('PHP版本')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('描述')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('file_name')
                    ->label('文件名')
                    ->searchable(),
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
                Tables\Filters\SelectFilter::make('php_version_id')
                    ->label('PHP版本')
                    ->options(PhpVersion::all()->pluck('version', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Action::make('enable')
                    ->label('启用')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (PhpExtension $record): bool => !$record->status)
                    ->action(fn (PhpExtension $record) => $record->enable()),
                Action::make('disable')
                    ->label('禁用')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (PhpExtension $record): bool => $record->status)
                    ->action(fn (PhpExtension $record) => $record->disable()),
                Action::make('check')
                    ->label('检查状态')
                    ->icon('heroicon-o-check')
                    ->action(fn (PhpExtension $record) => $record->checkStatus()),
                Action::make('view_info')
                    ->label('查看信息')
                    ->icon('heroicon-o-information-circle')
                    ->action(function (PhpExtension $record) {
                        // 显示扩展信息
                        $info = $record->getInfo();
                        return redirect()->back()->with('info', json_encode($info, JSON_PRETTY_PRINT));
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
            'index' => Pages\ListPhpExtensions::route('/'),
            'create' => Pages\CreatePhpExtension::route('/create'),
            'edit' => Pages\EditPhpExtension::route('/{record}/edit'),
        ];
    }
}
