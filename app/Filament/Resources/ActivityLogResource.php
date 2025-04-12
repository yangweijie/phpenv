<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static ?string $navigationLabel = '活动日志';
    
    protected static ?string $navigationGroup = '系统设置';
    
    protected static ?int $navigationSort = 90;
    
    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('description')
                    ->label('描述')
                    ->required()
                    ->maxLength(65535),
                Forms\Components\TextInput::make('subject_type')
                    ->label('对象类型')
                    ->maxLength(255),
                Forms\Components\TextInput::make('subject_id')
                    ->label('对象ID')
                    ->numeric(),
                Forms\Components\TextInput::make('causer_type')
                    ->label('操作者类型')
                    ->maxLength(255),
                Forms\Components\TextInput::make('causer_id')
                    ->label('操作者ID')
                    ->numeric(),
                Forms\Components\KeyValue::make('properties')
                    ->label('属性')
                    ->keyLabel('键')
                    ->valueLabel('值')
                    ->columnSpan(2),
                Forms\Components\Select::make('log_type')
                    ->label('日志类型')
                    ->options(ActivityLog::getTypes())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('时间')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('描述')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('log_type')
                    ->label('类型')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ActivityLog::getTypes()[$state] ?? $state)
                    ->color(fn (ActivityLog $record): string => $record->getTypeColor()),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('对象类型')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '系统';
                        
                        $parts = explode('\\', $state);
                        return end($parts);
                    }),
                Tables\Columns\TextColumn::make('subject_id')
                    ->label('对象ID')
                    ->formatStateUsing(fn ($state) => $state ?: '-'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新时间')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('log_type')
                    ->label('日志类型')
                    ->options(ActivityLog::getTypes()),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('开始日期'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('结束日期'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn($query) => $query->whereDate('created_at', '>=', $data['created_from']),
                            )
                            ->when(
                                $data['created_until'],
                                fn($query) => $query->whereDate('created_at', '<=', $data['created_until']),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }
}
