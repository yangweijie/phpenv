<?php

namespace App\Livewire;

use App\Models\Language;
use Livewire\Component;

class LanguageSwitcher extends Component
{
    public $currentLocale;
    
    public function mount()
    {
        $this->currentLocale = app()->getLocale();
    }
    
    public function switchLanguage($locale)
    {
        Language::setCurrentLanguage($locale);
        $this->currentLocale = $locale;
        
        $this->dispatch('refresh-navigation-menu');
        $this->dispatch('refresh-page');
    }
    
    public function render()
    {
        $languages = Language::where('is_active', true)->get();
        
        return view('livewire.language-switcher', [
            'languages' => $languages,
        ]);
    }
}
