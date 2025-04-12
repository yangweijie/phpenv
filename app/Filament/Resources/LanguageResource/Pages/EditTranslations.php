<?php

namespace App\Filament\Resources\LanguageResource\Pages;

use App\Filament\Resources\LanguageResource;
use App\Models\Language;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\File;

class EditTranslations extends Page
{
    protected static string $resource = LanguageResource::class;

    protected static string $view = 'filament.resources.language-resource.pages.edit-translations';
    
    public ?array $data = [];
    
    public $record;
    
    public $selectedFile = 'app';
    
    public function mount($record): void
    {
        $this->record = Language::findOrFail($record);
        $this->loadTranslations();
    }
    
    public function loadTranslations(): void
    {
        $translations = $this->record->getLanguageFileContent($this->selectedFile);
        $this->form->fill($this->flattenArray($translations));
    }
    
    public function form(Form $form): Form
    {
        $schema = [];
        $translations = $this->record->getLanguageFileContent($this->selectedFile);
        $flattenedTranslations = $this->flattenArray($translations);
        
        foreach ($flattenedTranslations as $key => $value) {
            $schema[] = Forms\Components\TextInput::make($key)
                ->label($key)
                ->required();
        }
        
        return $form
            ->schema([
                Forms\Components\Section::make('翻译')
                    ->schema($schema)
                    ->columns(1),
            ]);
    }
    
    public function getAvailableFiles(): array
    {
        return $this->record->getAvailableLanguageFiles();
    }
    
    public function changeFile($file): void
    {
        $this->selectedFile = $file;
        $this->loadTranslations();
    }
    
    public function save(): void
    {
        $data = $this->form->getState();
        $translations = $this->unflattenArray($data);
        
        try {
            $this->record->saveLanguageFileContent($translations, $this->selectedFile);
            
            Notification::make()
                ->title('翻译已保存')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('保存翻译失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $prefix . $key . '.'));
            } else {
                $result[$prefix . $key] = $value;
            }
        }
        
        return $result;
    }
    
    private function unflattenArray(array $array): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            $keys = explode('.', $key);
            $temp = &$result;
            
            foreach ($keys as $i => $k) {
                if ($i === count($keys) - 1) {
                    $temp[$k] = $value;
                } else {
                    if (!isset($temp[$k]) || !is_array($temp[$k])) {
                        $temp[$k] = [];
                    }
                    
                    $temp = &$temp[$k];
                }
            }
        }
        
        return $result;
    }
}
