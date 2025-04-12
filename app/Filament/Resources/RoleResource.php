<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Models\Role;
use App\Models\Permission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationLabel = '角色管理';
    
    protected static ?string $navigationGroup = '用户管理';
    
    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('角色名称')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->label('角色标识')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('角色描述')
                    ->maxLength(65535),
                Forms\Components\Toggle::make('is_default')
                    ->label('默认角色')
                    ->default(false),
                Forms\Components\Section::make('权限')
                    ->schema([
                        Forms\Components\Tabs::make('Permissions')
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('系统管理')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('system_permissions')
                                            ->label('系统管理权限')
                                            ->options(function () {
                                                return Permission::where('group', Permission::GROUP_SYSTEM)
                                                    ->pluck('name', 'id')
                                                    ->toArray();
                                            })
                                            ->columns(2)
                                            ->gridDirection('row'),
                                    ]),
                                Forms\Components\Tabs\Tab::make('服务管理')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('services_permissions')
                                            ->label('服务管理权限')
                                            ->options(function () {
                                                return Permission::where('group', Permission::GROUP_SERVICES)
                                                    ->pluck('name', 'id')
                                                    ->toArray();
                                            })
                                            ->columns(2)
                                            ->gridDirection('row'),
                                    ]),
                                Forms\Components\Tabs\Tab::make('PHP管理')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('php_permissions')
                                            ->label('PHP管理权限')
                                            ->options(function () {
                                                return Permission::where('group', Permission::GROUP_PHP)
                                                    ->pluck('name', 'id')
                                                    ->toArray();
                                            })
                                            ->columns(2)
                                            ->gridDirection('row'),
                                    ]),
                                Forms\Components\Tabs\Tab::make('网站管理')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('websites_permissions')
                                            ->label('网站管理权限')
                                            ->options(function () {
                                                return Permission::where('group', Permission::GROUP_WEBSITES)
                                                    ->pluck('name', 'id')
                                                    ->toArray();
                                            })
                                            ->columns(2)
                                            ->gridDirection('row'),
                                    ]),
                                Forms\Components\Tabs\Tab::make('项目管理')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('projects_permissions')
                                            ->label('项目管理权限')
                                            ->options(function () {
                                                return Permission::where('group', Permission::GROUP_PROJECTS)
                                                    ->pluck('name', 'id')
                                                    ->toArray();
                                            })
                                            ->columns(2)
                                            ->gridDirection('row'),
                                    ]),
                                Forms\Components\Tabs\Tab::make('备份管理')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('backups_permissions')
                                            ->label('备份管理权限')
                                            ->options(function () {
                                                return Permission::where('group', Permission::GROUP_BACKUPS)
                                                    ->pluck('name', 'id')
                                                    ->toArray();
                                            })
                                            ->columns(2)
                                            ->gridDirection('row'),
                                    ]),
                                Forms\Components\Tabs\Tab::make('用户管理')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('users_permissions')
                                            ->label('用户管理权限')
                                            ->options(function () {
                                                return Permission::where('group', Permission::GROUP_USERS)
                                                    ->pluck('name', 'id')
                                                    ->toArray();
                                            })
                                            ->columns(2)
                                            ->gridDirection('row'),
                                    ]),
                            ])
                            ->columnSpan('full'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('角色名称')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('角色标识')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('角色描述')
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_default')
                    ->label('默认角色')
                    ->boolean(),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('用户数量')
                    ->counts('users'),
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
                Action::make('set_default')
                    ->label('设为默认')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (Role $record): bool => !$record->is_default)
                    ->action(fn (Role $record) => $record->setAsDefault()),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Role $record): bool => $record->users()->count() === 0),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()->isAdmin()),
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withCount('users');
    }
}
