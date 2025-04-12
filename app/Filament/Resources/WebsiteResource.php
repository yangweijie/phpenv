<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebsiteResource\Pages;
use App\Models\Website;
use App\Models\PhpVersion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class WebsiteResource extends Resource
{
    protected static ?string $model = Website::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationLabel = 'Web网站管理';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationGroup = '网站管理';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('网站名称')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('domain')
                    ->label('域名')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('root_path')
                    ->label('根目录')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('server_type')
                    ->label('服务器类型')
                    ->options(Website::getServerTypes())
                    ->required(),
                Forms\Components\Toggle::make('status')
                    ->label('状态')
                    ->default(false),
                Forms\Components\Select::make('php_version_id')
                    ->label('PHP版本')
                    ->options(PhpVersion::all()->pluck('version', 'id'))
                    ->searchable()
                    ->nullable(),
                Forms\Components\TextInput::make('config_path')
                    ->label('配置文件路径')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('网站名称')
                    ->searchable(),
                Tables\Columns\TextColumn::make('domain')
                    ->label('域名')
                    ->searchable(),
                Tables\Columns\TextColumn::make('root_path')
                    ->label('根目录')
                    ->searchable(),
                Tables\Columns\TextColumn::make('server_type')
                    ->label('服务器类型')
                    ->formatStateUsing(fn (string $state): string => Website::getServerTypes()[$state] ?? $state),
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
                Tables\Filters\SelectFilter::make('server_type')
                    ->label('服务器类型')
                    ->options(Website::getServerTypes()),
                Tables\Filters\SelectFilter::make('php_version_id')
                    ->label('PHP版本')
                    ->options(PhpVersion::all()->pluck('version', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Action::make('create_config')
                    ->label('创建配置')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->action(fn (Website $record) => $record->createConfig()),
                Action::make('update_hosts')
                    ->label('更新hosts')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->action(fn (Website $record) => $record->updateHostsFile()),
                Action::make('delete_config')
                    ->label('删除配置')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->action(fn (Website $record) => $record->deleteConfig())
                    ->requiresConfirmation(),
                Action::make('open_website')
                    ->label('打开网站')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Website $record): string => 'http://' . $record->domain)
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListWebsites::route('/'),
            'create' => Pages\CreateWebsite::route('/create'),
            'edit' => Pages\EditWebsite::route('/{record}/edit'),
        ];
    }
}
