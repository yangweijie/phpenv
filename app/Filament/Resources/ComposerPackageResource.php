<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComposerPackageResource\Pages;
use App\Models\ComposerPackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class ComposerPackageResource extends Resource
{
    protected static ?string $model = ComposerPackage::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Composer管理';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationGroup = 'PHP管理';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('包名称')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('version')
                    ->label('版本')
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('描述')
                    ->maxLength(65535),
                Forms\Components\TextInput::make('type')
                    ->label('类型')
                    ->maxLength(255),
                Forms\Components\TextInput::make('project_path')
                    ->label('项目路径')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_global')
                    ->label('全局安装')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('包名称')
                    ->searchable(),
                Tables\Columns\TextColumn::make('version')
                    ->label('版本')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('描述')
                    ->limit(50),
                Tables\Columns\TextColumn::make('type')
                    ->label('类型'),
                Tables\Columns\IconColumn::make('is_global')
                    ->label('全局安装')
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
                Action::make('install')
                    ->label('安装')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (ComposerPackage $record) {
                        $output = $record->install();
                        return redirect()->back()->with('info', $output);
                    }),
                Action::make('update')
                    ->label('更新')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (ComposerPackage $record) {
                        $output = $record->update();
                        return redirect()->back()->with('info', $output);
                    }),
                Action::make('uninstall')
                    ->label('卸载')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->action(function (ComposerPackage $record) {
                        $output = $record->uninstall();
                        return redirect()->back()->with('info', $output);
                    })
                    ->requiresConfirmation(),
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
            'index' => Pages\ListComposerPackages::route('/'),
            'create' => Pages\CreateComposerPackage::route('/create'),
            'edit' => Pages\EditComposerPackage::route('/{record}/edit'),
        ];
    }
}
