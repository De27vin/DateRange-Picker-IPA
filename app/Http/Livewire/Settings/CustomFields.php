<?php

namespace App\Http\Livewire\Settings;

use App\Helpers\GroupCache;
use App\Models\CustomFieldConfig;
use App\Models\CustomFieldValue;
use App\Models\Language;
use App\Traits\TranslationsTrait;
use App\Traits\TrimInputs;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Validation\Rule;

class CustomFields extends Component
{
    use TranslationsTrait;
    use TrimInputs;

    public $accountId = null;

    public $currentCustomFieldsSite = [];
    public $currentCustomFieldsDevice = [];

    public $newCustomFieldsSite = [];
    public $newCustomFieldsDevice = [];

    public $languages = [];
    public $icons = [];

    public $showIconModal = false;
    public $iconModalTarget = null;

    public $showDeleteModal = false;
    public $deleteModalTarget = null;

    public $showParrotAppSwitchModal = false;
    public $parrotAppSwitchTarget = null;
    public $currentParrotAppField = null;

    public $showQrCodeSwitchModal = false;
    public $qrCodeSwitchTarget = null;
    public $currentQrCodeField = null;

    public $isProcessingToggle = false;

    public function mount()
    {
        if (!session('account.id')) {
            \Log::error('session account id not found in ChangePassword component');
            abort(500);
        }
        $this->accountId = session('account.id');
        $this->languages = Language::where('language_enabled', 1)->pluck('language_code')->toArray();
        $this->icons = json_decode(file_get_contents(config_path('f7icons.json')), true);

        $this->setupFieldSet(['Site', 'Device']);
        $this->findCurrentParrotAppField();
        $this->findCurrentQrCodeField();
    }

    public function rules($target)
    {
        $rules = [
            'currentCustomFields'.$target.'.*.name' => [
                'required',
            ],
            'currentCustomFields'.$target.'.*.icon' => [
                'required',
                Rule::in($this->icons)
            ],
            'newCustomFields'.$target.'.*.name' => [
                'required',
                Rule::unique('custom_field_configs', 'cfc_name')
                    ->where('cfc_account_id', $this->accountId)
            ],
            'newCustomFields'.$target.'.*.icon' => [
                'required',
                Rule::in($this->icons)
            ],
        ];

        foreach ($this->{'currentCustomFields'.$target} as $key => $current) {
            $rules['currentCustomFields'.$target.'.'.$key.'.name'][] = Rule::unique('custom_field_configs', 'cfc_name')
                ->ignore($current['id'], 'cfc_id')
                ->where('cfc_account_id', $this->accountId);
        }

        return $rules;
    }

    public function messages($target)
    {
        return [
            'currentCustomFields'.$target.'.*.name.required' => __('Field name is required'),
            'currentCustomFields'.$target.'.*.name.unique'   => __('Field name already exists'),
            'currentCustomFields'.$target.'.*.icon.required' => __('Icon is required'),
            'currentCustomFields'.$target.'.*.icon.in'       => __('Given icon is not defined'),
            'newCustomFields'.$target.'.*.name.required'     => __('Field name is required'),
            'newCustomFields'.$target.'.*.name.unique'       => __('Field name already exists'),
            'newCustomFields'.$target.'.*.icon.required'     => __('Icon is required'),
            'newCustomFields'.$target.'.*.icon.in'           => __('Given icon is not defined'),
        ];
    }

    public function render()
    {
        return view('livewire.settings.custom-fields');
    }

    public function deleteCurrentField($key, $target, $ask = true)
    {
        $currentId = $this->{'currentCustomFields'.$target}[$key]['id'];
        $currentCustomConfig = CustomFieldConfig::where([
            ['cfc_id', $currentId],
            ['cfc_account_id', session('account.id')],
        ])->first();
        $currentCustomValues = CustomFieldValue::where('cfv_cfc_id', $currentId)->get();

        if ($currentCustomValues->count() && $ask) {
            $this->showDeleteModal = true;
            $this->deleteModalTarget = [$key, $target];
            return;
        }

        DB::beginTransaction();
        try {
            // Remove from profile data if needed
            $profileData = $this->getProfileData();
            if (empty($profileData['custom_fields'])) {
                $profileData['custom_fields'] = [];
            }
            $customData =& $profileData['custom_fields'];

            if (!empty($customData[$currentId])) {
                unset($customData[$currentId]);
                $this->saveProfileData($profileData);
            }

            // Delete custom field values and config record
            CustomFieldValue::where('cfv_cfc_id', $currentId)->delete();
            $currentCustomConfig->delete();

            DB::commit();
            // Flush custom field configs cache after deletion
            GroupCache::forgetGroup('cfg');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Error on deleting custom fields', ['e' => $e]);
            $this->notify('error', __('Error on deleting custom fields'));
            $this->showDeleteModal = false;
            $this->deleteModalTarget = null;
            return;
        }

        $this->setupFieldSet();
        $this->findCurrentParrotAppField();
        $this->findCurrentQrCodeField();
        $this->notify('success', __('Custom field deleted'));
        $this->showDeleteModal = false;
        $this->deleteModalTarget = null;
    }

    public function deleteNewField($key, $target)
    {
        unset($this->{'newCustomFields'.$target}[$key]);
    }

    public function insertNewField($target)
    {
        $this->{'newCustomFields'.$target}[] = [
            'name'      => null,
            'is_device' => $target === 'Device',
        ];
    }

    public function toggleFieldRequired($key, $state, $target)
    {
        if ($this->isProcessingToggle) {
            return;
        }

        $this->isProcessingToggle = true;

        if (!empty($this->{$state.'CustomFields'.$target}[$key])) {
            $this->{$state.'CustomFields'.$target}[$key]['required'] = empty($this->{$state.'CustomFields'.$target}[$key]['required']);

            // Immediately save the toggle state for existing fields
            if ($state === 'current') {
                $this->saveToggleState($key, $target, 'required');
                $this->notify('success', __('Required field setting saved'));
            } else {
                $this->notify('info', __('Remember to click Update to save new fields'));
            }
        }

        $this->isProcessingToggle = false;
    }

    public function toggleFieldDashboard($key, $state, $target)
    {
        if ($this->isProcessingToggle) {
            return;
        }

        $this->isProcessingToggle = true;

        $limit = 1;
        $countNewSite = count(array_filter($this->newCustomFieldsSite, fn($field) => !empty($field['dashboard'])));
        $countNewDevice = count(array_filter($this->newCustomFieldsDevice, fn($field) => !empty($field['dashboard'])));
        $countCurrentSite = count(array_filter($this->currentCustomFieldsSite, fn($field) => !empty($field['dashboard'])));
        $countCurrentDevice = count(array_filter($this->currentCustomFieldsDevice, fn($field) => !empty($field['dashboard'])));

        $count = $countNewSite + $countNewDevice + $countCurrentSite + $countCurrentDevice;

        if (!empty($this->{$state.'CustomFields'.$target}[$key])) {
            if (empty($this->{$state.'CustomFields'.$target}[$key]['dashboard'])) {

                if ($count >= $limit) {
                    $this->notify('error', __('Only :limit field in total can be set for display in compact view. Remove display compact view for other fields.', ['limit' => $limit]));
                    $this->isProcessingToggle = false;
                    return;
                }

                $this->{$state.'CustomFields'.$target}[$key]['dashboard'] = true;
            } else {
                $this->{$state.'CustomFields'.$target}[$key]['dashboard'] = false;
            }

            // Immediately save the toggle state for existing fields
            if ($state === 'current') {
                $this->saveToggleState($key, $target, 'dashboard');
                $this->notify('success', __('Compact view setting saved'));
            } else {
                $this->notify('info', __('Remember to click Update to save new fields'));
            }
        }

        $this->isProcessingToggle = false;
    }

    public function toggleFieldEquipment($key, $state, $target)
    {
        if ($this->isProcessingToggle) {
            return;
        }

        $this->isProcessingToggle = true;

        $limit = 2;
        $countNewSite = count(array_filter($this->newCustomFieldsSite, fn($field) => !empty($field['equipment'])));
        $countNewDevice = count(array_filter($this->newCustomFieldsDevice, fn($field) => !empty($field['equipment'])));
        $countCurrentSite = count(array_filter($this->currentCustomFieldsSite, fn($field) => !empty($field['equipment'])));
        $countCurrentDevice = count(array_filter($this->currentCustomFieldsDevice, fn($field) => !empty($field['equipment'])));

        $countSite = $countNewSite + $countCurrentSite;
        $countDevice = $countNewDevice + $countCurrentDevice;

        if (!empty($this->{$state.'CustomFields'.$target}[$key])) {
            if (empty($this->{$state.'CustomFields'.$target}[$key]['equipment'])) {

                if (${'count'.$target} >= $limit) {
                    $this->notify('error', __('Only :limit fields in total per :target can have Equipment view option set on. Remove display in equipment view for other fields.', ['limit' => $limit, 'target' => $target]));
                    $this->isProcessingToggle = false;
                    return;
                }

                $this->{$state.'CustomFields'.$target}[$key]['equipment'] = true;
            } else {
                $this->{$state.'CustomFields'.$target}[$key]['equipment'] = false;
            }

            // Immediately save the toggle state for existing fields
            if ($state === 'current') {
                $this->saveToggleState($key, $target, 'equipment');
                $this->notify('success', __('Equipment view setting saved'));
            } else {
                $this->notify('info', __('Remember to click Update to save new fields'));
            }
        }

        $this->isProcessingToggle = false;
    }

    public function toggleFieldParrotApp($key, $state, $target)
    {
        if ($this->isProcessingToggle) {
            return;
        }

        $this->isProcessingToggle = true;

        if (!empty($this->{$state.'CustomFields'.$target}[$key])) {
            if (empty($this->{$state.'CustomFields'.$target}[$key]['parrot_app'])) {
                // Check if another field already has parrot app enabled
                if ($this->currentParrotAppField &&
                    ($this->currentParrotAppField['key'] !== $key ||
                        $this->currentParrotAppField['state'] !== $state ||
                        $this->currentParrotAppField['target'] !== $target)) {

                    // Show confirmation modal
                    $this->parrotAppSwitchTarget = [$key, $state, $target];
                    $this->showParrotAppSwitchModal = true;
                    $this->isProcessingToggle = false;
                    return;
                }

                // Enable it directly if no other field has it
                $this->{$state.'CustomFields'.$target}[$key]['parrot_app'] = true;
                $this->currentParrotAppField = [
                    'key' => $key,
                    'state' => $state,
                    'target' => $target,
                    'name' => $this->{$state.'CustomFields'.$target}[$key]['name']
                ];
            } else {
                // Disable it
                $this->{$state.'CustomFields'.$target}[$key]['parrot_app'] = false;
                $this->currentParrotAppField = null;
            }

            // Immediately save the toggle state for existing fields
            if ($state === 'current') {
                $this->saveToggleState($key, $target, 'parrot_app');
                $this->notify('success', __('Parrot App display setting saved'));
            } else {
                $this->notify('info', __('Remember to click Update to save new fields'));
            }
        }

        $this->isProcessingToggle = false;
    }

    public function toggleFieldQrCode($key, $state, $target)
    {
        if ($this->isProcessingToggle) {
            return;
        }

        // QR Code toggle is only available for Device fields
        if ($target !== 'Device') {
            return;
        }

        $this->isProcessingToggle = true;

        if (!empty($this->{$state.'CustomFields'.$target}[$key])) {
            if (empty($this->{$state.'CustomFields'.$target}[$key]['qr_code'])) {
                // Check if another field already has qr code enabled
                if ($this->currentQrCodeField &&
                    ($this->currentQrCodeField['key'] !== $key ||
                        $this->currentQrCodeField['state'] !== $state ||
                        $this->currentQrCodeField['target'] !== $target)) {

                    // Show confirmation modal
                    $this->qrCodeSwitchTarget = [$key, $state, $target];
                    $this->showQrCodeSwitchModal = true;
                    $this->isProcessingToggle = false;
                    return;
                }

                // Enable it directly if no other field has it
                $this->{$state.'CustomFields'.$target}[$key]['qr_code'] = true;
                $this->currentQrCodeField = [
                    'key' => $key,
                    'state' => $state,
                    'target' => $target,
                    'name' => $this->{$state.'CustomFields'.$target}[$key]['name']
                ];
            } else {
                // Disable it
                $this->{$state.'CustomFields'.$target}[$key]['qr_code'] = false;
                $this->currentQrCodeField = null;
            }

            // Immediately save the toggle state for existing fields
            if ($state === 'current') {
                $this->saveToggleState($key, $target, 'qr_code');
                $this->notify('success', __('QR Code setting saved'));
            } else {
                $this->notify('info', __('Remember to click Update to save new fields'));
            }
        }

        $this->isProcessingToggle = false;
    }

    public function confirmParrotAppSwitch()
    {
        if (!$this->parrotAppSwitchTarget) return;

        [$key, $state, $target] = $this->parrotAppSwitchTarget;

        // Disable on current field
        if ($this->currentParrotAppField) {
            $currentKey = $this->currentParrotAppField['key'];
            $currentState = $this->currentParrotAppField['state'];
            $currentTarget = $this->currentParrotAppField['target'];

            if (!empty($this->{$currentState.'CustomFields'.$currentTarget}[$currentKey])) {
                $this->{$currentState.'CustomFields'.$currentTarget}[$currentKey]['parrot_app'] = false;

                // Save the change immediately if it's a current field
                if ($currentState === 'current') {
                    $this->saveToggleState($currentKey, $currentTarget, 'parrot_app');
                }
            }
        }

        // Enable on new field
        $this->{$state.'CustomFields'.$target}[$key]['parrot_app'] = true;
        $this->currentParrotAppField = [
            'key' => $key,
            'state' => $state,
            'target' => $target,
            'name' => $this->{$state.'CustomFields'.$target}[$key]['name']
        ];

        // Save the change immediately if it's a current field
        if ($state === 'current') {
            $this->saveToggleState($key, $target, 'parrot_app');
            $this->notify('success', __('Parrot App display switched successfully'));
        } else {
            $this->notify('info', __('Parrot App display switched. Remember to click Update to save new fields'));
        }

        $this->showParrotAppSwitchModal = false;
        $this->parrotAppSwitchTarget = null;
    }

    public function cancelParrotAppSwitch()
    {
        $this->showParrotAppSwitchModal = false;
        $this->parrotAppSwitchTarget = null;
        $this->isProcessingToggle = false;
    }

    public function confirmQrCodeSwitch()
    {
        if (!$this->qrCodeSwitchTarget) return;

        [$key, $state, $target] = $this->qrCodeSwitchTarget;

        // Disable on current field
        if ($this->currentQrCodeField) {
            $currentKey = $this->currentQrCodeField['key'];
            $currentState = $this->currentQrCodeField['state'];
            $currentTarget = $this->currentQrCodeField['target'];

            if (!empty($this->{$currentState.'CustomFields'.$currentTarget}[$currentKey])) {
                $this->{$currentState.'CustomFields'.$currentTarget}[$currentKey]['qr_code'] = false;

                // Save the change immediately if it's a current field
                if ($currentState === 'current') {
                    $this->saveToggleState($currentKey, $currentTarget, 'qr_code');
                }
            }
        }

        // Enable on new field
        $this->{$state.'CustomFields'.$target}[$key]['qr_code'] = true;
        $this->currentQrCodeField = [
            'key' => $key,
            'state' => $state,
            'target' => $target,
            'name' => $this->{$state.'CustomFields'.$target}[$key]['name']
        ];

        // Save the change immediately if it's a current field
        if ($state === 'current') {
            $this->saveToggleState($key, $target, 'qr_code');
            $this->notify('success', __('QR Code setting switched successfully'));
        } else {
            $this->notify('info', __('QR Code setting switched. Remember to click Update to save new fields'));
        }

        $this->showQrCodeSwitchModal = false;
        $this->qrCodeSwitchTarget = null;
    }

    public function cancelQrCodeSwitch()
    {
        $this->showQrCodeSwitchModal = false;
        $this->qrCodeSwitchTarget = null;
        $this->isProcessingToggle = false;
    }

    private function saveToggleState($key, $target, $toggleType)
    {
        try {
            $profileData = $this->getProfileData();
            if (empty($profileData['custom_fields'])) {
                $profileData['custom_fields'] = [];
            }

            $fieldId = $this->{'currentCustomFields'.$target}[$key]['id'];
            $profileData['custom_fields'][$fieldId][$toggleType] = $this->{'currentCustomFields'.$target}[$key][$toggleType] ?? false;

            $this->saveProfileData($profileData);
            GroupCache::forgetGroup('cfg');
        } catch (\Throwable $e) {
            \Log::error('Error saving toggle state', ['error' => $e->getMessage()]);
        }
    }

    private function findCurrentParrotAppField()
    {
        $this->currentParrotAppField = null;

        // Check current Site fields
        foreach ($this->currentCustomFieldsSite as $key => $field) {
            if (!empty($field['parrot_app'])) {
                $this->currentParrotAppField = [
                    'key' => $key,
                    'state' => 'current',
                    'target' => 'Site',
                    'name' => $field['name']
                ];
                return;
            }
        }

        // Check current Device fields
        foreach ($this->currentCustomFieldsDevice as $key => $field) {
            if (!empty($field['parrot_app'])) {
                $this->currentParrotAppField = [
                    'key' => $key,
                    'state' => 'current',
                    'target' => 'Device',
                    'name' => $field['name']
                ];
                return;
            }
        }

        // Check new Site fields
        foreach ($this->newCustomFieldsSite as $key => $field) {
            if (!empty($field['parrot_app'])) {
                $this->currentParrotAppField = [
                    'key' => $key,
                    'state' => 'new',
                    'target' => 'Site',
                    'name' => $field['name']
                ];
                return;
            }
        }

        // Check new Device fields
        foreach ($this->newCustomFieldsDevice as $key => $field) {
            if (!empty($field['parrot_app'])) {
                $this->currentParrotAppField = [
                    'key' => $key,
                    'state' => 'new',
                    'target' => 'Device',
                    'name' => $field['name']
                ];
                return;
            }
        }
    }

    private function findCurrentQrCodeField()
    {
        $this->currentQrCodeField = null;

        // Only check Device fields (QR code is only available for devices)
        foreach ($this->currentCustomFieldsDevice as $key => $field) {
            if (!empty($field['qr_code'])) {
                $this->currentQrCodeField = [
                    'key' => $key,
                    'state' => 'current',
                    'target' => 'Device',
                    'name' => $field['name']
                ];
                return;
            }
        }

        // Check new Device fields
        foreach ($this->newCustomFieldsDevice as $key => $field) {
            if (!empty($field['qr_code'])) {
                $this->currentQrCodeField = [
                    'key' => $key,
                    'state' => 'new',
                    'target' => 'Device',
                    'name' => $field['name']
                ];
                return;
            }
        }
    }

    public function showIconModal($target)
    {
        $this->iconModalTarget = $target;
        $this->showIconModal = true;

        $currentIcon = null;
        if (isset($target[0]) && isset($target[1]) && isset($target[2])) {
            if (!empty($this->{$target[1].'CustomFields'.$target[2]}[$target[0]]['icon'])) {
                $currentIcon = $this->{$target[1].'CustomFields'.$target[2]}[$target[0]]['icon'];
            }
        }

        $this->iconModalTarget['current_icon'] = $currentIcon;
    }

    public function chooseIcon($icon)
    {
        $key   = $this->iconModalTarget[0] ?? null;
        $state = $this->iconModalTarget[1] ?? null;
        $target= $this->iconModalTarget[2] ?? null;

        if (is_null($key) || is_null($state) || is_null($target)) return;

        $this->{$state.'CustomFields'.$target}[$key]['icon'] = $icon;

        $this->iconModalTarget = null;
        $this->showIconModal = false;
    }

    public function saveCustomFields($target)
    {
        $this->validate($this->rules($target), $this->messages($target));

        DB::beginTransaction();
        try {
            $profileData = $this->getProfileData();
            if (empty($profileData['custom_fields'])) {
                $profileData['custom_fields'] = [];
            }
            $profileDataFields =& $profileData['custom_fields'];

            // Save current fields
            foreach ($this->{'currentCustomFields'.$target} as $currentField) {
                $currentField = $this->trimStringsInArrayRecursively($currentField);
                $currentCustomConfig = CustomFieldConfig::find($currentField['id']);
                $currentCustomConfig->cfc_name = $currentField['name'];
                $currentCustomConfig->cfc_is_device = $currentField['is_device'];
                $currentCustomConfig->save();
                $profileDataFields[$currentField['id']] = $currentField;
            }

            // Save new fields
            foreach ($this->{'newCustomFields'.$target} as $newField) {
                $newField = $this->trimStringsInArrayRecursively($newField);
                $newCustomConfig = CustomFieldConfig::create([
                    'cfc_name'       => $newField['name'],
                    'cfc_account_id' => $this->accountId,
                    'cfc_is_device'  => $newField['is_device'],
                ]);
                $profileDataFields[$newCustomConfig->cfc_id] = $newField;
            }

            $this->saveProfileData($profileData);
            DB::commit();
            GroupCache::forgetGroup('cfg');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Error on saving custom fields', ['e' => $e]);
            $this->notify('error', __('Error on saving custom fields'));
            return;
        }

        $this->setupFieldSet([$target]);
        $this->findCurrentParrotAppField();
        $this->findCurrentQrCodeField();
        $this->notify('success', __(':target custom fields saved', ['target' => $target]));
    }

    private function setupFieldSet(array $resetNewTargets = [])
    {
        $this->iconModalTarget = null;
        $this->showIconModal = false;
        $this->currentCustomFieldsSite = [];
        $this->currentCustomFieldsDevice = [];

        foreach ($resetNewTargets as $newTarget) {
            $this->{'newCustomFields'.$newTarget} = [];
        }

        $profileDataFields = [];
        if (!empty($this->getProfileData()['custom_fields'])) {
            $profileDataFields = $this->getProfileData()['custom_fields'];
        }

        // Retrieve custom field configs from cache (TTL 60000 seconds)
        $cacheKey = __CLASS__.__METHOD__.$this->accountId;
        $cachedConfigs = GroupCache::remember('cfg', $cacheKey, 60000, function() {
            return CustomFieldConfig::where('cfc_account_id', session('account.id'))->get()->toArray();
        });

        foreach ($cachedConfigs as $config) {
            $currentField = [
                'id'   => $config['cfc_id'],
                'name' => $config['cfc_name'],
                'is_device' => $config['cfc_is_device'],
            ];

            if (!empty($profileDataFields[$config['cfc_id']])) {
                $currentField = $currentField + $profileDataFields[$config['cfc_id']];
            }

            if (empty($config['cfc_is_device'])) {
                $this->currentCustomFieldsSite[] = $currentField;
            } else {
                $this->currentCustomFieldsDevice[] = $currentField;
            }
        }
    }
}