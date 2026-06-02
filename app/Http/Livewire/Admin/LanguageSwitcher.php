<?php
namespace App\Http\Livewire\Admin;

use App\Http\Livewire\UcpComponent;
use App\Models\Language;

class LanguageSwitcher extends UcpComponent
{
    protected $listeners = [
        'updateLanguageSwitcher'
    ];

    public function mount()
    {
        $this->updateLanguageSwitcher();
    }

    public function render()
    {
        return view('livewire.admin.language-switcher');
    }

    public function updateLanguageSwitcher()
    {
        // $this->translations = $this->getProfileData();
        // $this->languagesfromDB = Language::all()->toArray();
        if(session('languages') != null){
            $this->languages = session('languages');
        } else {
            $enabledCodes = app(\App\Services\LanguageService::class)->getEnabledLanguages();
            $this->languages = array_fill_keys($enabledCodes, true);
        }
    }
}