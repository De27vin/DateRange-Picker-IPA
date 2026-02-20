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

    public $openTab = 'users';
    public $showDeleteModal = false;

    public $basicRoles;
    public $optionalRoles;
    public $accessAllowed;
    public $authBasicRoles;
    public $authOptionalRoles;
    public $editedBinaryRoles = 0;
    public $authBinaryRoles = 0;

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
        }
        if($this->page == 'edit'){
            $rules = Arr::add($rules, 'editUser.firstname', 'required|alpha');
            $rules = Arr::add($rules, 'editUser.lastname', 'required|alpha');
            $rules = Arr::add($rules, 'editUser.email', 'required|email|unique:emails,email_address|unique:invites,invite_email');
            $rules = Arr::add($rules, 'editUser.ext', '');
            $rules = Arr::add($rules, 'emails', 'nullable|email|unique:emails,email_address|unique:invites,invite_email');
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
        $this->invites = $this->prepareUserData( Invite::where('invite_account_id', '=', Auth::user()->account->account_id)->orderByDesc('invite_created')->get() )->toArray();
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
                if(Arr::exists($this->optionalRoles, $role->role_type)){
                    $optionalRoles[] = $role->role_type;
                }
                $item->phone = (isset($item->user_ext) ? $item->user_ext : $item->invite_ext);
            }
            $item->optionalRoles = $optionalRoles;
            return $item;
        });
    }

    public function resetRolesData()
    {
        $this->basicRoles = (new Role)->without('users_roles')->whereIn('role_type', ['site','admin','user'])->get()->pluck(0, 'role_type')->all();
        $this->optionalRoles = (new Role)->without('users_roles')->whereIn('role_type', ['agent','mobile'])->get()->pluck(0, 'role_type')->all();
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
        $this->basicRoles[$type] = 1;
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
            $invitation->invite_account_id     = Auth::user()->account->account_id;
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

            $url = URL::temporarySignedRoute(
                'join', now()->addMinutes(30), ['token' => $token, 'lang' => session('locale', 'en')]
            );
        
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
            foreach ($this->basicRoles as $type => $state) {
                if($state == 1){
                    $role = Role::query()->where('role_type','=',$type)->first();
                    $user->roles()->attach($role->role_id, ['ur_account_id' => $account_id]);
                }
            }
            foreach ($this->optionalRoles as $type => $state) {
                if($state == 1){
                    $role = Role::query()->where('role_type','=',$type)->first();
                    $user->roles()->attach($role->role_id, ['ur_account_id' => $account_id]);
                }
            }
            if($this->editUser['hasLogin']){
                $user->roles()->attach($this->loginId, ['ur_account_id' => $account_id]);
            }
            DB::commit();
        } catch(\Exception $e){
            DB::rollback();
        }
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

}