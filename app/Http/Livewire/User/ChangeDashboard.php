<?php

namespace App\Http\Livewire\User;

use App\Services\ChartsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChangeDashboard extends Component
{
    public string $scope = ChartsService::SCOPE_DASHBOARD;
    public array $settings = [];

    private ChartsService $settingsService;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->settingsService = app(ChartsService::class);
    }

    public function mount(string $scope = ChartsService::SCOPE_DASHBOARD)
    {
        if (!session('account.id') || !Auth::check()) {
            \Log::error('Required data not found in ChangeDashboard component');
            abort(500);
        }

        $this->scope = in_array($scope, ChartsService::SCOPES, true)
            ? $scope
            : ChartsService::SCOPE_DASHBOARD;
        $this->settings = $this->settingsService->getUserDefaults($this->scope);
    }

    public function render()
    {
        return view('livewire.user.change-dashboard');
    }

    public function updateDashboardSettings()
    {
        $this->settings = $this->settingsService->saveUserDefaults($this->settings, $this->scope);
        $this->notify('success', __('Dashboard defaults updated'));
    }

    public function updateChartsSettings()
    {
        $this->settings = $this->settingsService->saveUserDefaults($this->settings, $this->scope);
        $this->notify('success', __('Charts defaults updated'));
    }

    public function resetDashboardSettings()
    {
        $this->settings = $this->settingsService->resetUserDefaults($this->scope);
        $this->notify('success', __('Dashboard defaults reset'));
    }

    public function resetChartsSettings()
    {
        $this->settings = $this->settingsService->resetUserDefaults($this->scope);
        $this->notify('success', __('Charts defaults reset'));
    }

    public function cancelDashboardSettings()
    {
        $this->settings = $this->settingsService->getUserDefaults($this->scope);
    }

    public function cancelChartsSettings()
    {
        $this->settings = $this->settingsService->getUserDefaults($this->scope);
    }
}
