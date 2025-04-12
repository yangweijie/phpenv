<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LanguageResource\Pages;
use App\Models\Language;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\File;

class LanguageResource extends Resource
{
    protected static ?string $model = Language::class;

    protected static ?string $navigationIcon = 'heroicon-o-language';
    
    protected static ?string $navigationLabel = '语言管理';
    
    protected static ?string $navigationGroup = '系统设置';
    
    protected static ?int $navigationSort = 60;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('语言名称')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->label('语言代码')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_default')
                    ->label('默认语言')
                    ->default(false),
                Forms\Components\Toggle::make('is_active')
                    ->label('激活状态')
                    ->default(true),
                Forms\Components\Select::make('direction')
                    ->label('文本方向')
                    ->options(Language::getDirections())
                    ->default(Language::DIRECTION_LTR)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('语言名称')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('语言代码')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label('默认语言')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('激活状态')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('direction')
                    ->label('文本方向')
                    ->formatStateUsing(fn (string $state): string => Language::getDirections()[$state] ?? $state),
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
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('默认语言'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('激活状态'),
            ])
            ->actions([
                Action::make('set_default')
                    ->label('设为默认')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (Language $record): bool => !$record->is_default)
                    ->action(fn (Language $record) => $record->setAsDefault()),
                Action::make('activate')
                    ->label('激活')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Language $record): bool => !$record->is_active)
                    ->action(fn (Language $record) => $record->activate()),
                Action::make('deactivate')
                    ->label('停用')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Language $record): bool => $record->is_active && !$record->is_default)
                    ->action(function (Language $record) {
                        try {
                            $record->deactivate();
                            return redirect()->back()->with('success', '语言已停用');
                        } catch (\Exception $e) {
                            return redirect()->back()->with('error', $e->getMessage());
                        }
                    }),
                Action::make('edit_translations')
                    ->label('编辑翻译')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Language $record): string => route('filament.admin.resources.languages.edit-translations', $record)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Language $record): bool => !$record->is_default),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if (!$record->is_default) {
                                    $record->delete();
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
            'index' => Pages\ListLanguages::route('/'),
            'create' => Pages\CreateLanguage::route('/create'),
            'edit' => Pages\EditLanguage::route('/{record}/edit'),
            'edit-translations' => Pages\EditTranslations::route('/{record}/translations'),
        ];
    }
}
