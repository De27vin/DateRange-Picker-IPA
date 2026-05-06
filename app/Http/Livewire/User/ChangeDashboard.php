<?php

namespace App\Http\Livewire\User;

use App\Services\DashboardWidgetSettingsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChangeDashboard extends Component
{
    public array $dashboardWidgetSettings = [];

    private DashboardWidgetSettingsService $dashboardWidgetSettingsService;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->dashboardWidgetSettingsService = new DashboardWidgetSettingsService();
    }

    public function mount()
    {
        if (!session('account.id') || !Auth::check()) {
            \Log::error('Required data not found in ChangeDashboard component');
            abort(500);
        }

        $this->dashboardWidgetSettings = $this->dashboardWidgetSettingsService->getUserDefaults();
    }

    public function render()
    {
        return view('livewire.user.change-dashboard');
    }

    public function updateDashboardSettings()
    {
        $this->dashboardWidgetSettings = $this->dashboardWidgetSettingsService->saveUserDefaults($this->dashboardWidgetSettings);
        $this->notify('success', __('Dashboard defaults updated'));
    }

    public function resetDashboardSettings()
    {
        $this->dashboardWidgetSettings = $this->dashboardWidgetSettingsService->resetUserDefaults();
        $this->notify('success', __('Dashboard defaults reset'));
    }

    public function cancelDashboardSettings()
    {
        $this->dashboardWidgetSettings = $this->dashboardWidgetSettingsService->getUserDefaults();
    }
}
