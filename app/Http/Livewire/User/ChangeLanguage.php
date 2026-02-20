<?php
namespace App\Http\Livewire\User;

use App\Traits\TranslationsTrait;
use Livewire\Component;
use App\Models\Language;

class ChangeLanguage extends Component
{
    use TranslationsTrait;
    public array $languageNames = [];

    public $languagesFromDB;
    public $languagesFromJson;

    protected $listeners = [
        'updateLanguageSwitcher'
    ];

    public function mount()
    {
        $this->languageNames = [
            "de" => __('German'),
            "fr" => __('French'),
            "en" => __('English'),
            "it" => __('Italian'),
            "es" => __('Spanish'),
            "pt" => __('Portuguese'),
        ];

        $this->languagesFromDB = Language::get()->all();
        $this->languagesFromJson = $this->getProfileData()['languages'];
    }

    public function render()
    {
        return view('livewire.user.change-language');
    }
}