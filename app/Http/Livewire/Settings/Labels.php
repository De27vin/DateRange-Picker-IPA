<?php

namespace App\Http\Livewire\Settings;

use App\Helpers\GroupCache;
use App\Models\DeviceLabel;
use App\Models\DeviceLabelGroup;
use App\Services\SettingsService;
use App\Traits\Validation\ValidatesWithNotifies;
use Livewire\Component;
use App\Models\DeviceLabelOld;
use App\Models\DeviceLabelSetting;
use App\Models\Setting;
use App\Models\Language;
use App\Traits\FreeswitchApiTrait;
use App\Traits\TranslationsTrait;
use App\Http\Livewire\DataTable\WithCachedRows;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class Labels extends Component
{
    use WithCachedRows;
    use FreeswitchApiTrait;
    use TranslationsTrait;
    use ValidatesWithNotifies;

    public $breadcrumb       = ['UCP', 'Settings', 'Groups'];
    public $accountId;
    public $translations;
    public $groups; // will be loaded from cache
    public $editing;
    public $selectedGroup;
    public $selectedNodeName;
    public $groupSettings;
    public $customSettingList;
    public $showDeleteModal;
    public $messages;
    public $advancedSettings;

    // New properties
    public $selectedLabel;
    public $labelSettings;
    public $settings;
    public $showDeleteModalGroup;
    public $showDeleteModalLabel;
    public $showAddGroup;
    public $showAddLabel;

    private SettingsService $settingsService;

    protected $listeners = [
        'cancelSettings',
        'onSortOrderChangeGroup' => 'handleSortOrderChangeGroup',
        'onSortOrderChangeLabel' => 'handleSortOrderChangeLabel',
    ];

    protected $rules = [
        'selectedGroup'         => '',
        'selectedLabel'         => '',
        'selectedGroup.dlg_name'=> 'required',
        'selectedLabel.dl_name' => 'required',
        'editing.name'          => 'required|unique:groups',
        'translations'          => '',
        'editing.dl_id'         => '',
        'editing.group_name'    => 'required|unique:groups',
    ];

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->settingsService = new SettingsService();
    }

    public function mount()
    {
        $this->editing = [];
        $this->initData();
    }

    public function initData()
    {
        $this->customSettingList = [
            'call_dtmf_tx_gain'  => 'Defines the DTMF gain in dB (value must be between -10 to 0)',
            'call_dtmf_tx_mark'  => 'Defines the duration DMTF in milliseconds (value must be between 50 to 500)',
            'call_dtmf_tx_space' => 'Defines the space between DMTF in milliseconds (value must be between 50 to 500)',
            'call_recording'     => 'Activate/deactivate call recording',
            'device_initial_rx_ignore' => 'Ignore initial DTMF ( OFF = not ignored; ON = ignored)',
        ];

        $this->locale       = session('locale', 'en');
        $this->accountId    = session('account.id');
        $this->languages    = Language::where('language_enabled', '=', true)->get()->pluck('language_code')->all();
        $this->translations = $this->getTranslations(['settings' => 'device', 'initial' => '', 'call' => 'call']);
        $this->canWriteSettings = Auth::user()->is_admin;

        $this->resetStateFresh();
    }

    public function resetStateFresh()
    {
        $this->resetState();
        $this->freshItems();
    }

    // Reset state properties
    public function resetState()
    {
        $this->editing = [];
        $this->editing['dlg_id']   = null;
        $this->editing['dlg_name'] = null;
        $this->editing['dl_id']    = null;
        $this->editing['dl_name']  = null;
        $this->selectedGroup       = null;
        $this->selectedLabel       = null;
        $this->groupSettings       = null;
        $this->showAddGroup        = null;
        $this->showAddLabel        = null;
        $this->showDeleteModalGroup= null;
        $this->showDeleteModalLabel= null;
        $this->settings            = [];
        $this->messages            = [];
        return null;
    }

    private function freshItems()
    {
        $cacheKey = __CLASS__.__METHOD__.$this->accountId;
        $this->groups = GroupCache::remember('labels', $cacheKey, 6000, function() {
            return DeviceLabelGroup::query()
                ->where('dlg_account_id', $this->accountId)
                ->orderBy('dlg_order')
                ->with(['labels' => function ($query) {
                    $query->orderBy('dl_order');
                }])
                ->get();
        });

        \Log::info('freshItems loaded', [
            'groups' => $this->groups->toArray(),
        ]);
    }

    public function render()
    {
        return view('livewire.settings.labels');
    }

    public function handleSortOrderChangeGroup($sortOrder, $previousSortOrder, $name, $from, $to)
    {
        \Log::info('handleSortOrderChangeGroup called', [
            'sortOrder'        => $sortOrder,
            'previousSortOrder'=> $previousSortOrder,
            'name'             => $name,
            'from'             => $from,
            'to'               => $to,
        ]);

        if ($from !== 'groups' || $to !== 'groups') {
            $this->notify('warning', __('Dragging between groups and labels is not allowed'));
            return;
        }
        $newOrder = array_values(array_map(fn($val) => str_replace('group-', '', $val), $sortOrder));

        DB::beginTransaction();
        try {
            $tempOrderValue = 1000000;
            DeviceLabelGroup::whereIn('dlg_id', $newOrder)
                ->update(['dlg_order' => DB::raw('dlg_order + ' . $tempOrderValue)]);

            foreach ($newOrder as $position => $dlg_id) {
                DeviceLabelGroup::where('dlg_id', $dlg_id)
                    ->update(['dlg_order' => $position + 1]);
            }

            DB::commit();
            $this->notify('success', __('Group order changed'));
            GroupCache::forgetGroup('labels');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e);
            $this->notify('error', __('Error on changing group order'));
        }

        $this->resetStateFresh();
    }

    public function handleSortOrderChangeLabel($sortOrder, $previousSortOrder, $name, $from, $to)
    {
        \Log::info('handleSortOrderChangeLabel called', [
            'sortOrder'        => $sortOrder,
            'previousSortOrder'=> $previousSortOrder,
            'name'             => $name,
            'from'             => $from,
            'to'               => $to,
        ]);

        $fromGroupId = str_replace('labels-', '', $from);
        $toGroupId   = str_replace('labels-', '', $to);
        $currentGroupId = str_replace('labels-', '', $name);

        $sortOrderLabelIds = array_map(function ($sortKey) {
            return str_replace('label-', '', $sortKey);
        }, $sortOrder);

        if ($fromGroupId !== $toGroupId) {
            if ($currentGroupId == $toGroupId) {
                DB::beginTransaction();
                try {
                    foreach ($sortOrderLabelIds as $position => $dl_id) {
                        DeviceLabel::where('dl_id', $dl_id)
                            ->update([
                                'dl_dlg_id' => $toGroupId,
                                'dl_order'  => $position + 100000000,
                            ]);
                    }
                    foreach ($sortOrderLabelIds as $position => $dl_id) {
                        DeviceLabel::where('dl_id', $dl_id)
                            ->update([
                                'dl_dlg_id' => $toGroupId,
                                'dl_order'  => $position + 1,
                            ]);
                    }

                    DB::commit();
                    $this->notify('success', __('Label moved to new group'));
                    GroupCache::forgetGroup('labels');
                } catch (\Throwable $e) {
                    DB::rollBack();
                    \Log::error($e);
                    $this->notify('error', __('Error moving label to new group'));
                }
            } elseif ($currentGroupId == $fromGroupId) {
                DB::beginTransaction();
                try {
                    foreach ($sortOrderLabelIds as $position => $dl_id) {
                        DeviceLabel::where('dl_id', $dl_id)
                            ->update(['dl_order' => $position + 100000000]);
                    }
                    foreach ($sortOrderLabelIds as $position => $dl_id) {
                        DeviceLabel::where('dl_id', $dl_id)
                            ->update(['dl_order' => $position + 1]);
                    }

                    DB::commit();
                    $this->notify('success', __('Source group labels reordered'));
                    GroupCache::forgetGroup('labels');
                } catch (\Throwable $e) {
                    DB::rollBack();
                    \Log::error($e);
                    $this->notify('error', __('Error reordering labels in source group'));
                }
            }
        } else {
            DB::beginTransaction();
            try {
                foreach ($sortOrderLabelIds as $position => $dl_id) {
                    DeviceLabel::where('dl_id', $dl_id)
                        ->update(['dl_order' => $position + 100000000]);
                }
                foreach ($sortOrderLabelIds as $position => $dl_id) {
                    DeviceLabel::where('dl_id', $dl_id)
                        ->update(['dl_order' => $position + 1]);
                }

                DB::commit();
                $this->notify('success', __('Label order updated within group'));
                GroupCache::forgetGroup('labels');
            } catch (\Throwable $e) {
                DB::rollBack();
                \Log::error($e);
                $this->notify('error', __('Error updating label order within group'));
            }
        }

        $this->resetStateFresh();
    }

    public function toggleGroup($groupId)
    {
        if (!$groupId) { return false; }

        if (!isset($this->editing)) {
            $this->editing = [];
        }

        $this->selectedLabel = null;
        $this->editing['dl_id'] = null;
        $editingGroupId = $this->editing['dlg_id'];

        if ($editingGroupId === $groupId) {
            return $this->resetState();
        }

        $this->editing['dlg_id'] = $groupId;

        $this->selectedGroup = DeviceLabelGroup::query()
            ->where('dlg_id', $groupId)
            ->first();

        $this->settings = null;
        $this->labelSettings = null;
    }

    public function toggleLabel($labelId)
    {
        if (!$labelId) { return false; }

        $this->selectedGroup = null;
        $this->editing['dlg_id'] = null;
        $editingLabelId = $this->editing['dl_id'];

        if ($editingLabelId === $labelId) {
            return $this->resetState();
        }

        $this->editing['dl_id'] = $labelId;
        $this->selectedLabel = DeviceLabel::query()
            ->where('dl_id', $labelId)
            ->first();

        if ($this->selectedLabel) {
            $settings = $this->settingsService->getLabelSettings($this->selectedLabel);
            $this->advancedSettings = $this->settingsService->prepareSettingsForView(
                SettingsService::LABEL,
                $settings
            );
        }
    }

    public function onShowDeleteModalGroup()
    {
        if (!$this->selectedGroup) {
            return;
        }

        $this->selectedGroup = DeviceLabelGroup::withCount('labels')->findOrFail($this->selectedGroup->dlg_id);

        $this->messages = [];
        if ($this->selectedGroup->labels_count > 0) {
            $this->messages[] = trans(':group has :count labels. Deleting the group will also delete these labels.', [
                'group' => $this->selectedGroup->dlg_name,
                'count' => $this->selectedGroup->labels_count
            ]);
        }

        $this->showDeleteModalGroup = true;
    }

    public function deleteGroup()
    {
        DB::beginTransaction();
        try {
            $group = DeviceLabelGroup::with('labels')->findOrFail($this->selectedGroup->dlg_id);

            // Delete associated labels and their settings
            foreach ($group->labels as $label) {
                $label->device_sites()->detach();
                DB::table('device_label_settings')->where('dls_dl_id', $label->dl_id)->delete();
                DB::table('device_labels')->where('dl_id', $label->dl_id)->delete();
            }

            // Delete the group
            DB::table('device_label_groups')->where('dlg_id', $group->dlg_id)->delete();

            DB::commit();
            $this->notify('success', __('Group and its labels deleted'));
            GroupCache::forgetGroup('labels');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e, ['Caught']);
            $this->notify('error', __('Error occurred on group delete'));
        }

        $this->resetStateFresh();
    }

    public function onShowDeleteModalLabel()
    {
        if (!$this->selectedLabel) {
            return;
        }

        $this->selectedLabel = DeviceLabel::withCount('device_sites')->findOrFail($this->selectedLabel->dl_id);

        $this->messages = [];
        if ($this->selectedLabel->device_sites_count > 0) {
            $this->messages[] = trans(':label is used by :count devices', [
                'label' => $this->selectedLabel->dl_name,
                'count' => $this->selectedLabel->device_sites_count
            ]);
        }

        $this->showDeleteModalLabel = true;
    }

    public function deleteLabel()
    {
        DB::beginTransaction();
        try {
            $label = DeviceLabel::withCount('device_sites')->findOrFail($this->selectedLabel->dl_id);

            $label->device_sites()->detach();
            DB::table('device_label_settings')->where('dls_dl_id', $label->dl_id)->delete();
            DB::table('device_labels')->where('dl_id', $label->dl_id)->delete();

            DB::commit();
            $this->notify('success', __('Label deleted'));
            GroupCache::forgetGroup('labels');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e, ['Caught']);
            $this->notify('error', __('Error occurred on label delete'));
        }

        $this->resetStateFresh();
    }

    public function updateGroup()
    {
        if (!$this->selectedGroup) {
            $this->notify('warning', __('No active group found'));
            return null;
        }

        $this->selectedGroup->dlg_name = trim($this->selectedGroup->dlg_name);

        $this->validateWithNotify(['selectedGroup.dlg_name' => 'required|unique:device_label_groups,dlg_name,'.$this->selectedGroup->dlg_id.',dlg_id'], [
            'selectedGroup.dlg_name.required' => __('Field name is required'),
            'selectedGroup.dlg_name.unique'   => __('Field name already exists'),
        ]);

        $editedGroup = DeviceLabelGroup::findOrFail($this->selectedGroup->dlg_id);

        if ($this->selectedGroup->dlg_name === $editedGroup->dlg_name) {
            return null;
        }

        $editedGroup->dlg_name = $this->selectedGroup->dlg_name;
        $editedGroup->save();

        $this->notify('success', __('Group name updated'));
        GroupCache::forgetGroup('labels');
        $this->freshItems();
    }

    public function updateLabel()
    {
        if (!$this->selectedLabel) {
            $this->notify('warning', trans('No active label found'));
            return null;
        }

        $this->selectedLabel->dl_name = trim($this->selectedLabel->dl_name);

        $this->validateWithNotify(
            ['selectedLabel.dl_name' => [
                'required',
                Rule::unique('device_labels', 'dl_name')
                    ->where('dl_dlg_id', $this->selectedLabel->dl_dlg_id)
                    ->ignore($this->selectedLabel->dl_id, 'dl_id')
            ]],
            [
                'selectedLabel.dl_name.required' => __('Field name is required'),
                'selectedLabel.dl_name.unique'   => __('Field name already exists'),
            ]
        );

        $editedLabel = DeviceLabel::findOrFail($this->selectedLabel->dl_id);

        if ($this->selectedLabel->dl_name !== $editedLabel->dl_name) {
            $editedLabel->dl_name = $this->selectedLabel->dl_name;
            $editedLabel->save();
            $this->notify('success', __('Label name updated'));
            GroupCache::forgetGroup('labels');
        }

        $this->updateLabelSettings();
        $this->freshItems();
    }

    public function insertGroup()
    {
        $this->editing['group_name'] = trim($this->editing['group_name']);

        $this->validateWithNotify(
            ['editing.group_name' => 'required|unique:device_label_groups,dlg_name'],
            [
            'editing.group_name.required' => __('Field name is required'),
            'editing.group_name.unique'   => __('Field name already exists')
            ]
        );

        $existingGroupsMaxOrder = DeviceLabelGroup::where('dlg_account_id', $this->accountId)->get()
            ->pluck('dlg_order')->max() ?? 0;

        $group = new DeviceLabelGroup();
        $group->dlg_name = $this->editing['group_name'];
        $group->dlg_account_id = $this->accountId;
        $group->dlg_order = $existingGroupsMaxOrder + 1;
        $group->save();

        GroupCache::forgetGroup('labels');
        $this->freshItems();
        $this->showAddGroup = false;
    }

    public function insertLabel()
    {
        if (!$this->selectedGroup) {
            $this->notify('warning', trans('No active group found'));
            return null;
        }

        $this->editing['label_name'] = trim($this->editing['label_name']);

        $this->validateWithNotify(
            ['editing.label_name' => [
                'required',
                Rule::unique('device_labels', 'dl_name')->where('dl_dlg_id', $this->selectedGroup->dlg_id)
            ]],
            [
                'editing.label_name.required' => __('Field name is required'),
                'editing.label_name.unique'   => __('Field name already exists'),
            ]
        );

        $existingLabelsMaxOrder = DeviceLabel::where([
            'dl_account_id' => $this->accountId,
            'dl_dlg_id'     => $this->selectedGroup->dlg_id
        ])->get()->pluck('dl_order')->max() ?? 0;

        $label = new DeviceLabel();
        $label->dl_name = $this->editing['label_name'];
        $label->dl_account_id = $this->accountId;
        $label->dl_order = $existingLabelsMaxOrder + 1;
        $label->dl_dlg_id = $this->selectedGroup->dlg_id;
        $label->save();

        GroupCache::forgetGroup('labels');
        $this->freshItems();
        $this->showAddLabel = false;
    }

    public function cancelModal()
    {
        $this->showDeleteModal = false;
    }

    public function updateLabelSettings()
    {
        if (!$this->selectedLabel) {
            $this->notify('warning', __('No active label found'));
            return null;
        }

        $updated = $this->settingsService->updateLabelSettings(
            $this->selectedLabel,
            collect($this->advancedSettings)
        );

        if ($updated) {
            $this->notify('success', __('Label settings updated'));
            $this->makeFsReload($this->selectedLabel->dl_id);
        } else {
            $this->notify('error', __('Error occurred while updating label settings'));
        }

        $settings = $this->settingsService->getLabelSettings($this->selectedLabel);
        $this->advancedSettings = $this->settingsService->prepareSettingsForView(
            SettingsService::LABEL,
            $settings
        );
    }

    public function makeFsReload($id = null)
    {
        if ($id != null) {
            if ($result = $this->fsMake('ucp del label ' . $id, false, true)) {
                $this->notify('success', __('ucp reload label command processed'));
            } else {
                $this->notify('error', __('ucp reload label command failed'));
            }
        }
    }
}
