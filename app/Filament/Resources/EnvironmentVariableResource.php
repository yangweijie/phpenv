<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnvironmentVariableResource\Pages;
use App\Models\EnvironmentVariable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class EnvironmentVariableResource extends Resource
{
    protected static ?string $model = EnvironmentVariable::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationLabel = '环境变量管理';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = '系统设置';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('变量名')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('value')
                    ->label('变量值')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label('变量类型')
                    ->options(EnvironmentVariable::getTypes())
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('描述')
                    ->maxLength(65535),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('变量名')
                    ->searchable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('变量值')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('type')
                    ->label('变量类型')
                    ->formatStateUsing(fn (string $state): string => EnvironmentVariable::getTypes()[$state] ?? $state),
                Tables\Columns\TextColumn::make('description')
                    ->label('描述')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Action::make('apply')
                    ->label('应用')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn (EnvironmentVariable $record) => $record->setVariable()),
                Action::make('remove')
                    ->label('移除')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->action(fn (EnvironmentVariable $record) => $record->removeVariable())
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
            'index' => Pages\ListEnvironmentVariables::route('/'),
            'create' => Pages\CreateEnvironmentVariable::route('/create'),
            'edit' => Pages\EditEnvironmentVariable::route('/{record}/edit'),
        ];
    }
}
