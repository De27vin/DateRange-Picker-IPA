<?php
namespace App\Http\Livewire\Settings;

use App\Services\RolesService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\Models\Role;
use App\Models\UsersRole;
use App\Models\User;
use App\Models\Invite;
use App\Models\Email;
use App\Traits\AccountsTrait;
use App\Notifications\Invite as InviteNotification;

class Users extends Component
{
    use AccountsTrait;

    public $users;
    public $siteUsers;
    public $loginId;
    public $editingUserId;
    public $page; // list | edit | add - default = list
    public $authHasRoleSite;
    public $selected = [];
    public $newUser;
    public $editUser;
    public $emails;
    public $confirmPasswordForMandown;

    public $openTab = 'users';
    public $showDeleteModal = false;

    public $basicRoles;
    public $optionalRoles;
    public $accessAllowed;
    public $authBasicRoles;
    public $authOptionalRoles;
    public $editedBinaryRoles = 0;
    public $authBinaryRoles = 0;
    // TODO: temporary solution for liftcare subtenants - remove after implementing granular permissions system
    public $subtenantTag = '';

    // protected $rules = [
    //     'newUser.firstname' => 'required|alpha',
    //     'newUser.lastname'  => 'required|alpha',
    //     'newUser.email'     => 'required|email|unique:emails,email_address|unique:invites,invite_email',
    //     'newUser.ext'       => '',
    //     'emails'            => '',
    // ];

    private RolesService $rolesService;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->rolesService = new RolesService();
    }

    protected function rules()
    {
        $rules = [];
        if($this->page == 'add'){
            $rules = Arr::add($rules, 'newUser.firstname', 'required|alpha');
            $rules = Arr::add($rules, 'newUser.lastname', 'required|alpha');
            $rules = Arr::add($rules, 'newUser.email', 'required|email|unique:emails,email_address|unique:invites,invite_email');
            $rules = Arr::add($rules, 'newUser.ext', '');

            // Sub-tenant validation for liftcare account
            if ($this->isSubtenantUser()) {
                $rules = Arr::add($rules, 'subtenantTag', 'required|string|max:90');
                // Mobile role is required for sub-tenant users
                if (empty($this->optionalRoles['mobile'])) {
                    $this->addError('optionalRoles.mobile', __('Mobile role is required for Sub-tenant users'));
                }
            }
        }
        if($this->page == 'edit'){
            $rules = Arr::add($rules, 'editUser.firstname', 'required|alpha');
            $rules = Arr::add($rules, 'editUser.lastname', 'required|alpha');
            $rules = Arr::add($rules, 'editUser.email', 'required|email|unique:emails,email_address|unique:invites,invite_email');
            $rules = Arr::add($rules, 'editUser.ext', '');
            $rules = Arr::add($rules, 'emails', 'nullable|email|unique:emails,email_address|unique:invites,invite_email');

            // Sub-tenant validation for liftcare account
            if ($this->isSubtenantUser()) {
                $rules = Arr::add($rules, 'subtenantTag', 'required|string|max:90');
                // Mobile role is required for sub-tenant users
                if (empty($this->optionalRoles['mobile'])) {
                    $this->addError('optionalRoles.mobile', __('Mobile role is required for Sub-tenant users'));
                }
            }
        }
        return Arr::dot($rules);
    }

    public function mount()
    {
        $this->freshAuthenticatedRoles();
        $this->resetRolesData();
        $this->loginId = Role::query()->where('role_type','=','login')->first()->role_id;
//        $this->authHasRoleSite = Auth::user()->hasRole('site');
        $this->updateUsers();
    }

    public function render()
    {
        $this->freshAuthenticatedRoles();

        switch ($this->page) {
            case 'list':
                return view('livewire.settings.users');
            case 'edit':
                return view('livewire.settings.users-edit');
            case 'add':
                return view('livewire.settings.users-add');

            default:
                return view('livewire.settings.users');
        }

    }

    private function freshAuthenticatedRoles()
    {
        $roles = $this->rolesService->getUserRoleStates(Auth::user());
        $this->authBasicRoles = $roles['basicRoles'];
        $this->authOptionalRoles = $roles['optionalRoles'];
        $this->authBinaryRoles = $this->rolesService->getBasicRolesBinary($this->authBasicRoles);
    }

    private function freshEditedRoles()
    {
        if (empty($this->editUser['user_id'])) {
            return;
        }
        $user = User::find($this->editUser['user_id']);
        $roles = $this->rolesService->getUserRoleStates($user);
        $this->basicRoles = $roles['basicRoles'];
        $this->optionalRoles = $roles['optionalRoles'];
        $this->editedBinaryRoles = $this->rolesService->getBasicRolesBinary($this->authBasicRoles);
    }

    public function updateUsers()
    {
        $this->authHasRoleSite = Auth::user()->hasRole('site');
        $this->users = $this->prepareUserData( $this->getUsersByAccount() )->toArray();
        if($this->authHasRoleSite){
            $this->siteUsers = $this->prepareUserData( $this->getSiteUsers() )->toArray();
        } else {
            $this->siteUsers = [];
        }
        $this->invites = $this->prepareUserData( Invite::where('invite_account_id', '=', $this->userContext->getSessionAccountId())->orderByDesc('invite_created')->get() )->toArray();
        // dd($this->invites);
        $this->resetRolesData();
        $this->editingUserId = null;
        $this->editUser = null;
        $this->emails = null;
        $this->page = 'list';
    }

    public function prepareUserData($users)
    {
        return $users->map(function($item,$key){
            if(isset($item->invite_state)){
                if( Carbon::parse($item->invite_expire)->isPast() && $item->invite_state != 'successful') {
                    $item->invite_state = 'expired';
                    $item->save();
                }
                switch ($item->invite_state) {
                    case 'pending':
                        $item->badgecolor = 'primary';
                        break;
                    case 'successful':
                        $item->badgecolor = 'success';
                        break;
                    case 'expired':
                        $item->badgecolor = 'danger';
                        break;
                    case 'canceled':
                        $item->badgecolor = 'canceled';
                        break;
                    default:
                        $item->badgecolor = 'primary';
                        break;
                }
            }
            $item->allowLogin = 0;
            $item->allowAdmin = 0;
            $item->allowSite = 0;
            $item->allowUser = 0;
            $optionalRoles = [];
            foreach ($item->roles as $role) {
                if($role->role_type == 'login'){
                    $item->allowLogin = 1;
                }
                if($role->role_type == 'admin'){
                    $item->allowAdmin = 1;
                }
                if($role->role_type == 'site'){
                    $item->allowSite = 1;
                }
                if($role->role_type == 'user'){
                    $item->allowUser = 1;
                }
                if(Arr::exists($this->optionalRoles, $role->role_type)){
                    $optionalRoles[] = $role->role_type;
                }
                $item->phone = (isset($item->user_ext) ? $item->user_ext : $item->invite_ext);
            }
            $item->isSubtenant = ($item instanceof \App\Models\User)
                ? $item->isSubtenantUser()
                : (!$item->allowAdmin && !$item->allowSite && !$item->allowUser);

            $item->optionalRoles = $optionalRoles;
            return $item;
        });
    }

    public function resetRolesData()
    {
        $this->basicRoles = (new Role)->without('users_roles')->whereIn('role_type', ['site','admin','user'])->get()->pluck(0, 'role_type')->all();
        $this->optionalRoles = (new Role)->without('users_roles')->whereIn('role_type', ['agent','mobile','mandown'])->get()->pluck(0, 'role_type')->all();
    }

    public function toggleActiveState($userId)
    {
        $editUser = User::find($userId);
        if($editUser->hasRole('site')){
            $this->toggleSiteUserState($userId);
        } else {
            $this->toggleUserState($userId);
        }
        $this->updateUsers();
    }

    public function toggleSiteUserState($userId)
    {
        $hasLogin = UsersRole::query()
            ->where('ur_user_id','=',$userId)
            ->where('ur_role_id','=',$this->loginId)
            ->first();

        if($hasLogin != null){
            $deleted = DB::table('users_roles')
                ->where('ur_user_id', '=', $userId)
                ->where('ur_role_id','=',$this->loginId)
                ->delete();
         } else {
            $inserted = DB::table('users_roles')
                ->insert([
                    'ur_user_id' => $userId,
                    'ur_account_id' => null,
                    'ur_role_id' => $this->loginId
                ]);
        }
    }

    public function toggleUserState($userId)
    {
        $hasLogin = UsersRole::query()
            ->where('ur_user_id','=',$userId)
            ->where('ur_account_id','=',session('account.id'))
            ->where('ur_role_id','=',$this->loginId)
            ->first();
            
        if($hasLogin != null){
            $deleted = DB::table('users_roles')
                ->where('ur_user_id', '=', $userId)
                ->where('ur_account_id','=',session('account.id'))
                ->where('ur_role_id','=',$this->loginId)
                ->delete();
         } else {
            $inserted = DB::table('users_roles')
                ->insert([
                    'ur_user_id' => $userId,
                    'ur_account_id' => session('account.id'),
                    'ur_role_id' => $this->loginId
                ]);
        }
    }

    public function addUser()
    {
        $this->basicRoles['user'] = 1;
        $this->accessAllowed     = 1;
        $this->newUser           = $this->makeBlankInvite()->toArray();
        $this->subtenantTag      = '';

        $optionalRoles = $this->optionalRoles;
        foreach ($optionalRoles as $type => $state) {
            $this->optionalRoles[$type] = 0;
        }

        $this->page = 'add';
    }

    public function makeBlankInvite()
    {
        return Invite::make([
            'firstname' => '', 
            'lastname' => '', 
            'state' => 'pending',
            'email' => '',
            'ext' => ''
        ]);
    }

    public function updateUserType($type)
    {
        foreach ($this->basicRoles as $key => $value) {
            $this->basicRoles[$key] = 0;
        }

        if ($type === 'subtenant') {
            // Sub-tenant is not a real role - keep all basicRoles as 0
            // This represents a user with no hierarchical role
            return;
        }

        $this->basicRoles[$type] = 1;
    }

    public function toggleBasicRole($type)
    {
        if (isset($this->basicRoles[$type])) {
            $this->basicRoles[$type] = abs($this->basicRoles[$type] - 1);
        }
    }

    public function updateOptionalRole($type)
    {
        if (isset($this->optionalRoles[$type])) {
            $this->optionalRoles[$type] = abs($this->optionalRoles[$type] - 1);
        }
    }

    public function saveInvite()
    {
        if (!$this->validate()) {

        }

        $token = Str::random(50);
        $invitation = new Invite();
        try{
            $invitation->invite_email          = $this->newUser['email'];
            $invitation->invite_firstname      = $this->newUser['firstname'];
            $invitation->invite_lastname       = $this->newUser['lastname'];
            $invitation->invite_ext            = (Arr::has($this->newUser,'ext') ? $this->newUser['ext'] : null);
            $invitation->invite_token          = $token;
            $invitation->invite_expire         = Carbon::now()->addHours(48);
            $invitation->invite_account_id     = $this->userContext->getSessionAccountId();
            $invitation->save();
            foreach ($this->basicRoles as $type => $state) {
                if($state == 1){
                    $role = Role::query()->where('role_type','=',$type)->first();
                    $invitation->roles()->attach($role->role_id);
                }
            }
            foreach ($this->optionalRoles as $type => $state) {
                if($state == 1){
                    $role = Role::query()->where('role_type','=',$type)->first();
                    $invitation->roles()->attach($role->role_id);
                }
            }
            if($this->accessAllowed){
                $invitation->roles()->attach($this->loginId);
            }

            $urlParams = [
                'token' => $token,
                'lang' => session('locale', 'en')
            ];

            if ($this->isSubtenantUser()) {
                \Log::info('Sub-tenant user detected in saveInvite', [
                    'subtenantTag' => $this->subtenantTag,
                    'basicRoles' => $this->basicRoles,
                    'optionalRoles' => $this->optionalRoles
                ]);
                $urlParams['tag'] = encrypt(trim($this->subtenantTag));
            } else {
                \Log::info('NOT sub-tenant user in saveInvite', [
                    'account_slug' => $this->userContext->getCurrentAccount()?->account_slug,
                    'basicRoles' => $this->basicRoles,
                    'optionalRoles' => $this->optionalRoles
                ]);
            }

            $url = URL::temporarySignedRoute('join', $invitation->invite_expire, $urlParams);

            try{

                // TODO: this is done wrong bc if mail fails - the invitation in db still remains
                Notification::route('mail', $invitation->invite_email)->notify(new InviteNotification($url));
            } catch(\Exception $e){
                \Log::error($e->getMessage());
                $this->notify('error', __('Sending Invitation email failed'));
            }

        } catch(\Exception $e){
            \Log::error($e->getMessage());
            $this->notify('error', __('Sending Invitation email failed'));
        }
        $this->updateUsers();
        $this->openTab = 'invitations';
    }

    public function cancelInvite()
    {
        $this->updateUsers();
    }

    public function editUser($user_id)
    {
        try{
            $user = User::find($user_id);
            $roles = $this->rolesService->getUserRoleStates($user);
            $this->basicRoles = $roles['basicRoles'];
            $this->optionalRoles = $roles['optionalRoles'];
            $this->editedBinaryRoles = $this->rolesService->getBasicRolesBinary($this->basicRoles);
            $account = $this->userContext->getCurrentAccount();
            $this->subtenantTag = $user->getSubtenantTag($account->account_id) ?? '';

        } catch(\Exception $e){
            $this->notify('error', __('Error'));
        }
        $this->editUser = $user->toArray();
        $this->emails = [''];
        // ray($this->editUser['emails']);
        $this->page = 'edit';
    }

    public function addFieldForEmail()
    {
        array_push($this->emails,'');
    }

    public function removeEmail($index)
    {
        unset($this->emails[$index]);
        $this->emails = array_values($this->emails);
    }

    public function removeExistingEmail($index)
    {
        unset($this->editUser['emails'][$index]);
        $this->editUser['emails'] = array_values($this->editUser['emails']);
    }
    public function updateUser()
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($this->editUser['user_id']);

            // Capture old state BEFORE any changes for SignalWire sync
            $hadMandownRole = $user->hasMandownRole();
            $oldPrimaryEmail = $user->getPrimaryEmail(); // CRITICAL: capture before email changes
            $oldSipUsername = $oldPrimaryEmail ? hash('sha256', $oldPrimaryEmail) : null;
            $oldUserName = $user->name; // For detecting caller_id changes

            \Log::info('User update started', [
                'user_id' => $user->user_id,
                'had_mandown' => $hadMandownRole,
                'old_primary_email' => $oldPrimaryEmail,
                'old_sip_username' => $oldSipUsername
            ]);

            $willHaveMandownRole = isset($this->optionalRoles['mandown']) && $this->optionalRoles['mandown'] == 1;
            $isAddingMandownRole = !$hadMandownRole && $willHaveMandownRole;

            // Validate primary email exists before allowing mandown role
            if ($isAddingMandownRole && empty($oldPrimaryEmail)) {
                $this->addError('confirmPasswordForMandown', __('Cannot enable ManDown role: user has no email address'));
                DB::rollback();
                return;
            }

            // Check if email will change for existing Mandown user (BEFORE saving changes)
            // We need to detect this early to require password
            $willChangeEmail = false;
            if ($hadMandownRole && !empty($oldPrimaryEmail)) {
                // Check if primary email is being modified
                $primaryEmailRecord = $user->emails()->orderBy('email_id', 'asc')->first();
                if ($primaryEmailRecord) {
                    $editedPrimaryEmail = Arr::first($this->editUser['emails'], function ($item) use ($primaryEmailRecord) {
                        return ($primaryEmailRecord->email_id == $item['email_id']);
                    }, null);
                    if ($editedPrimaryEmail && trim($editedPrimaryEmail['email_address']) !== $oldPrimaryEmail) {
                        $willChangeEmail = true;
                    }
                }
            }

            // If adding mandown role OR changing email, require password confirmation
            if (($isAddingMandownRole || $willChangeEmail) && empty($this->confirmPasswordForMandown)) {
                $errorMsg = $isAddingMandownRole
                    ? __('Password confirmation is required to enable ManDown role')
                    : __('Password confirmation is required to update SIP endpoint after email change');
                $this->addError('confirmPasswordForMandown', $errorMsg);
                DB::rollback();
                return;
            }

            // Verify password if provided
            if (($isAddingMandownRole || $willChangeEmail) && !empty($this->confirmPasswordForMandown)) {
                if (!\Illuminate\Support\Facades\Hash::check($this->confirmPasswordForMandown, $user->user_password)) {
                    $this->addError('confirmPasswordForMandown', __('Invalid password'));
                    DB::rollback();
                    return;
                }
            }

            $user->update([
                'user_firstname' => trim($this->editUser['user_firstname']),
                'user_lastname'  => trim($this->editUser['user_lastname']),
                'user_ext'       => trim($this->editUser['user_ext']),
            ]);

            foreach ($user->emails as $key => $value) {
                $editedEmail = Arr::first($this->editUser['emails'], function ($item, $key) use ($value) {
                    return ($value->email_id == $item['email_id']);
                }, null);
                if($editedEmail == null){
                    // delete email from database
                    $deleted = DB::table('emails')->where('email_id','=',$value->email_id)->delete();
                } else {
                    // update
                    $affected = DB::table('emails')
                        ->where('email_id', $value->email_id)
                        ->update(['email_address' => trim($editedEmail['email_address'])]);
                }
            }
            foreach ($this->emails as $email) {
                if($email != ''){
                    DB::table('emails')->insert([
                        'email_address' => trim($email),
                        'email_user_id' => $user->user_id
                    ]);
                }
            }
            if($this->basicRoles['site'] == 1){
                $account_id = null;
            } else {
                $account_id = session('account.id');
            }
            $user->roles()->detach();

            $pivotData = ['ur_account_id' => $account_id];
            if ($this->isSubtenantUser()) {
                $pivotData['ur_tag'] = trim($this->subtenantTag);
            }

            // Helper to attach role with backward compatibility for ur_tag column
            $attachRole = function($roleId) use ($user, &$pivotData) {
                try {
                    $user->roles()->attach($roleId, $pivotData);
                } catch (\Throwable $e) {
                    // Column ur_tag doesn't exist - try without it (backward compatibility)
                    if (isset($pivotData['ur_tag'])) {
                        unset($pivotData['ur_tag']);
                        $user->roles()->attach($roleId, $pivotData);
                    } else {
                        throw $e;
                    }
                }
            };

            foreach ($this->basicRoles as $type => $state) {
                if($state == 1){
                    $role = Role::query()->where('role_type','=',$type)->first();
                    $attachRole($role->role_id);
                }
            }
            foreach ($this->optionalRoles as $type => $state) {
                if($state == 1){
                    $role = Role::query()->where('role_type','=',$type)->first();
                    $attachRole($role->role_id);
                }
            }
            if($this->editUser['hasLogin']){
                $attachRole($this->loginId);
            }

            // Refresh user to get updated roles and emails
            $user = $user->fresh();
            $nowHasMandownRole = $user->hasMandownRole();
            $newPrimaryEmail = $user->getPrimaryEmail();
            $newSipUsername = $newPrimaryEmail ? hash('sha256', $newPrimaryEmail) : null;
            $emailChanged = $oldPrimaryEmail !== $newPrimaryEmail;
            $nameChanged = $oldUserName !== $user->name;

            \Log::info('User update - after save', [
                'user_id' => $user->user_id,
                'now_has_mandown' => $nowHasMandownRole,
                'new_primary_email' => $newPrimaryEmail,
                'new_sip_username' => $newSipUsername,
                'email_changed' => $emailChanged
            ]);

            // SignalWire SIP endpoint management
            $signalWireService = new \App\Services\SignalWireService();

            if (!$hadMandownRole && $nowHasMandownRole) {
                $this->notify('success', __('User successfully updated'));
                if (!$signalWireService->createSipEndpoint($user, $this->confirmPasswordForMandown)) {
                    $this->notify('warning', __('SIP endpoint creation failed'));
                } else {
                    $this->notify('success', __('SIP endpoint created successfully'));
                }
            } elseif ($hadMandownRole && !$nowHasMandownRole) {
                $this->notify('success', __('User successfully updated'));
                if (!$signalWireService->deleteSipEndpointByUsername($oldSipUsername)) {
                    $this->notify('warning', __('SIP endpoint deletion failed'));
                } else {
                    $this->notify('success', __('SIP endpoint deleted successfully'));
                }
            } elseif ($hadMandownRole && $nowHasMandownRole) {
                if ($emailChanged && $oldSipUsername && $newSipUsername) {
                    \Log::info('Primary email changed for Mandown user, recreating SIP endpoint', [
                        'user_id' => $user->user_id,
                        'old_email' => $oldPrimaryEmail,
                        'new_email' => $newPrimaryEmail
                    ]);

                    if (!$signalWireService->deleteSipEndpointByUsername($oldSipUsername)) {
                        \Log::warning('Failed to delete old SIP endpoint after email change', [
                            'user_id' => $user->user_id,
                            'old_username' => $oldSipUsername
                        ]);
                        $this->notify('warning', __('Failed to delete old SIP endpoint'));
                    }

                    $this->notify('success', __('User successfully updated'));
                    if (!$signalWireService->createSipEndpoint($user, $this->confirmPasswordForMandown)) {
                        $this->notify('error', __('SIP endpoint recreation failed'));
                    } else {
                        $this->notify('success', __('SIP endpoint created successfully'));
                    }
                } else {
                    if ($nameChanged) {
                        $this->notify('success', __('User successfully updated'));
                        if (!$signalWireService->updateSipEndpoint($user)) {
                            $this->notify('warning', __('SIP endpoint sync failed'));
                        } else {
                            $this->notify('success', __('SIP endpoint updated successfully'));
                        }
                    }
                }
            }

            if (!$hadMandownRole && !$nowHasMandownRole) {
                $this->notify('success', __('User successfully updated'));
            }

            DB::commit();
        } catch(\Exception $e){
            DB::rollback();
            \Log::error('Failed to update user', ['error' => $e->getMessage()]);
            $this->notify('error', __('Failed to update user'));
        }

        $this->confirmPasswordForMandown = null;
        $this->updateUsers();
    }

    public function refreshInvite($invite_id)
    {
        try{
            $invite = Invite::find($invite_id);
            $invite->invite_expire = Carbon::now()->addHours(48);
            $invite->invite_state = 'pending';
            $invite->save();
        } catch(\Exception $e){
            $this->notify('error', __('Error'));
        }
        $this->updateUsers();
    }

    public function deleteSelectedUser()
    {
        if(!is_null($this->editUser)){
            try {
                DB::beginTransaction();

                $user = User::findOrFail($this->editUser['user_id']);

                // Delete SignalWire SIP endpoint if user has mandown role
                if ($user->hasMandownRole()) {
                    $signalWireService = new \App\Services\SignalWireService();
                    if (!$signalWireService->deleteSipEndpoint($user)) {
                        \Log::warning('Failed to delete SIP endpoint for user being deleted', [
                            'user_id' => $user->user_id
                        ]);
                    }
                }

                foreach ($this->editUser['emails'] as $email) {
                    $deleted = Email::where('email_id','=',$email['email_id'])->delete();
                }
                foreach ($this->editUser['roles'] as $role) {
                    $deleted = UsersRole::query()
                        ->where('ur_role_id','=',$role['role_id'])
                        ->where('ur_user_id','=',$this->editUser['user_id'])
                        ->delete();
                }
                $deleted = User::where('user_id', $this->editUser['user_id'])->delete();
                DB::commit();
                $this->notify('success', __('User successfully deleted'));
            } catch(\Exception $e){
                DB::rollback();
                \Log::error('Failed to delete user', ['error' => $e->getMessage()]);
                $this->notify('error', __('Delete of user data failed'));
            }
            $this->showDeleteModal = false;
            $this->cancelInvite();
            $this->editUser = null;
        }
    }


    public function deleteInvite($invite_id)
    {
        try{
            $invite = Invite::find($invite_id);
            $inviteRoles = $invite->roles()->get();
            $invite->roles()->detach();
            $deleted = DB::table('invites')->where('invite_id', '=', $invite->invite_id)->delete();
        } catch(\Exception $e){
            $this->notify('error', __('Error'));
        }
        $this->updateUsers();
    }

    // TODO: temporary solution for liftcare subtenants
    private function isSubtenantUser(): bool
    {
        if (!$this->isSubtenantAccount()) {
            return false;
        }

        // Show sub-tenant field when no hierarchical role is selected
        $hasHierarchicalRole = ($this->basicRoles['site'] ?? 0)
            || ($this->basicRoles['admin'] ?? 0)
            || ($this->basicRoles['user'] ?? 0);

        return !$hasHierarchicalRole;
    }

    // TODO: temporary solution for liftcare subtenants
    private function isSubtenantAccount(): bool
    {
        $account = $this->userContext->getCurrentAccount();
        return $account && in_array($account->account_slug, config('ucp.subtenant.accounts', []));
    }

}