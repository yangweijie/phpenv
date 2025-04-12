<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Illuminate\Support\Facades\File;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('open_project')
                ->label('打开项目')
                ->icon('heroicon-o-folder-open')
                ->color('success')
                ->action(function () {
                    try {
                        $this->record->openProject();
                        $this->notify('success', '项目已打开');
                    } catch (\Exception $e) {
                        $this->notify('danger', '打开项目失败: ' . $e->getMessage());
                    }
                })
                ->visible(fn (): bool => File::exists($this->record->path)),
            Actions\Action::make('open_website')
                ->label('打开网站')
                ->icon('heroicon-o-globe-alt')
                ->color('info')
                ->action(function () {
                    try {
                        $this->record->openWebsite();
                        $this->notify('success', '网站已打开');
                    } catch (\Exception $e) {
                        $this->notify('danger', '打开网站失败: ' . $e->getMessage());
                    }
                })
                ->visible(fn (): bool => $this->record->website_id !== null),
            Actions\EditAction::make(),
        ];
    }
    
    public function getProjectSize()
    {
        return $this->record->getFormattedSize();
    }
    
    public function getFileCount()
    {
        return $this->record->getFileCount();
    }
    
    public function getDirectoryCount()
    {
        return $this->record->getDirectoryCount();
    }
    
    public function getLastModified()
    {
        $timestamp = $this->record->getLastModified();
        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
    }
    
    public function getComposerDependencies()
    {
        return $this->record->getComposerDependencies();
    }
    
    public function getGitInfo()
    {
        return $this->record->getGitInfo();
    }
}
