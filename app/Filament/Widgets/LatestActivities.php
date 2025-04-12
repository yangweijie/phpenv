<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestActivities extends BaseWidget
{
    protected static ?int $sort = 4;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ActivityLog::query()->latest()->limit(10)
            )
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
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
