<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use App\Models\PhpVersion;
use App\Models\Website;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\File;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';
    
    protected static ?string $navigationLabel = '项目管理';
    
    protected static ?string $navigationGroup = '项目管理';
    
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('项目名称')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('path')
                    ->label('项目路径')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label('项目类型')
                    ->options(Project::getTypes())
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('项目描述')
                    ->maxLength(65535),
                Forms\Components\Select::make('php_version_id')
                    ->label('PHP版本')
                    ->options(PhpVersion::all()->pluck('version', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('website_id')
                    ->label('关联网站')
                    ->options(Website::all()->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\TextInput::make('git_repository')
                    ->label('Git仓库')
                    ->maxLength(255),
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
                    ->label('项目名称')
                    ->searchable(),
                Tables\Columns\TextColumn::make('path')
                    ->label('项目路径')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('项目类型')
                    ->formatStateUsing(fn (string $state): string => Project::getTypes()[$state] ?? $state),
                Tables\Columns\TextColumn::make('phpVersion.version')
                    ->label('PHP版本')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('website.name')
                    ->label('关联网站')
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->label('状态')
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
                Tables\Filters\SelectFilter::make('type')
                    ->label('项目类型')
                    ->options(Project::getTypes()),
                Tables\Filters\SelectFilter::make('php_version_id')
                    ->label('PHP版本')
                    ->options(PhpVersion::all()->pluck('version', 'id'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('website_id')
                    ->label('关联网站')
                    ->options(Website::all()->pluck('name', 'id'))
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('status')
                    ->label('状态'),
            ])
            ->actions([
                Action::make('open_project')
                    ->label('打开项目')
                    ->icon('heroicon-o-folder-open')
                    ->color('success')
                    ->action(function (Project $record) {
                        try {
                            $record->openProject();
                            return redirect()->back()->with('success', '项目已打开');
                        } catch (\Exception $e) {
                            return redirect()->back()->with('error', '打开项目失败: ' . $e->getMessage());
                        }
                    })
                    ->visible(fn (Project $record): bool => File::exists($record->path)),
                Action::make('open_website')
                    ->label('打开网站')
                    ->icon('heroicon-o-globe-alt')
                    ->color('info')
                    ->action(function (Project $record) {
                        try {
                            $record->openWebsite();
                            return redirect()->back()->with('success', '网站已打开');
                        } catch (\Exception $e) {
                            return redirect()->back()->with('error', '打开网站失败: ' . $e->getMessage());
                        }
                    })
                    ->visible(fn (Project $record): bool => $record->website_id !== null),
                Action::make('view_info')
                    ->label('查看信息')
                    ->icon('heroicon-o-information-circle')
                    ->url(fn (Project $record): string => route('filament.admin.resources.projects.view', $record))
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
