<?php

namespace App\Http\Livewire\User;

use App\Services\ChartsSettingsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChangeCharts extends Component
{
    public array $chartsSettings = [];

    private ChartsSettingsService $chartsSettingsService;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->chartsSettingsService = new ChartsSettingsService();
    }

    public function mount()
    {
        if (!session('account.id') || !Auth::check()) {
            \Log::error('Required data not found in ChangeCharts component');
            abort(500);
        }

        $this->chartsSettings = $this->chartsSettingsService->getUserDefaults();
    }

    public function render()
    {
        return view('livewire.user.change-charts');
    }

    public function updateChartsSettings()
    {
        $this->chartsSettings = $this->chartsSettingsService->saveUserDefaults($this->chartsSettings);
        $this->notify('success', __('Charts defaults updated'));
    }

    public function resetChartsSettings()
    {
        $this->chartsSettings = $this->chartsSettingsService->resetUserDefaults();
        $this->notify('success', __('Charts defaults reset'));
    }

    public function cancelChartsSettings()
    {
        $this->chartsSettings = $this->chartsSettingsService->getUserDefaults();
    }
}
