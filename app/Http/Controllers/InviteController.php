<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Models\Account;
use App\Models\Invite;
use App\Models\Locale;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\App;

/**
 * Class InviteController
 *
 * @package App\Http\Controllers
 */
class InviteController extends Controller
{

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \App\Models\Invite
     */
    protected $invite;


    public function join(Request $request)
    {
        $lang = $request->query('lang') ?? 'en';
        Session::put('locale', $lang);
        App::setLocale($lang);

        if($invitedUser = Invite::where('invite_token', '=', $request->token)->first()) {
            if($invitedUser->status == 'successful'){
                // $this->notify(trans('This invitation already processed'));
                app(\App\Services\UserContextService::class)->logoutActiveUser();
                return redirect(route('login'));
            }
            // TODO: temporary solution for liftcare subtenants - remove after implementing granular permissions system
            return view('users.create',[
                'token' => $request->token,
                'tag' => $request->query('tag')
            ]);
        }
    }

    public function accept(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|confirmed|min:6'
        ], [
            'password.required' => __('Password is required'),
            'password.confirmed' => __('Password confirmation does not match'),
            'password.min' => __('Password must be at least 6 characters')
        ]);

        if($validator->fails()) {
            return Redirect::back()
                ->withErrors($validator)
                ->withInput();
        }

        // TODO: temporary solution for liftcare subtenants - remove after implementing granular permissions system
        $urTag = null;
        \Log::info('InviteController accept - checking for tag parameter', [
            'has_tag' => $request->has('tag'),
            'tag_value' => $request->query('tag') ? 'present' : 'missing'
        ]);

        if ($request->has('tag')) {
            try {
                $urTag = decrypt($request->tag);
                \Log::info('Successfully decrypted ur_tag', ['urTag' => $urTag]);
            } catch (\Throwable $e) {
                \Log::error('Failed to decrypt ur_tag from invitation', ['error' => $e->getMessage()]);
            }
        } else {
            \Log::info('No tag parameter in request');
        }

        $defaultLocaleId = Language::where(['language_code' => session('locale', 'en'), 'language_enabled' => 1])->first()?->language_default_id ?? null;
        if($invitedUser = Invite::where('invite_token', '=', $request->token)->first()) {
            try {
                DB::beginTransaction();
                $password = bcrypt($request->password);
                $user = User::create([
                    'user_firstname' => $invitedUser->invite_firstname,
                    'user_lastname' => $invitedUser->invite_lastname,
                    'user_ext' => $invitedUser->invite_ext,
                    'user_timezone' => 'Europe/Zurich',
                    'user_password' => $password,
                    'user_lastpw' => time(),
                    'user_locale_id' => $defaultLocaleId,
                ]);
                // $user->id = $user->user_id;
                $user->save();

                $inserted = DB::table('emails')->insert([
                    'email_user_id' => $user->user_id,
                    'email_address' => $invitedUser->invite_email
                ]);

                $account = Account::query()->where('account_id','=',$invitedUser->invite_account_id)->first();
                $siteRole = $invitedUser->roles->firstWhere('role_type','site');
                $otherRoles = $invitedUser->roles->where('role_type', '!=', 'site');

                if ($siteRole) {
                    DB::table('users_roles')->insert([
                        'ur_user_id' => $user->user_id,
                        'ur_role_id' => $siteRole->role_id,
                        'ur_account_id' => null
                    ]);
                }

                foreach($otherRoles as $role) {
                    $roleData = [
                        'ur_user_id' => $user->user_id,
                        'ur_role_id' => $role->role_id,
                        'ur_account_id' => $account->account_id
                    ];

                    if ($urTag) {
                        $roleData['ur_tag'] = $urTag;
                        \Log::info('Adding ur_tag to role data', ['role_type' => $role->role_type, 'urTag' => $urTag]);
                    }

                    \Log::info('Inserting role to users_roles', ['roleData' => $roleData, 'role_type' => $role->role_type]);

                    try {
                        DB::table('users_roles')->insert($roleData);
                    } catch (\Throwable $e) {
                        // Column ur_tag doesn't exist - try without it (backward compatibility)
                        if ($urTag && isset($roleData['ur_tag'])) {
                            unset($roleData['ur_tag']);
                            DB::table('users_roles')->insert($roleData);
                        } else {
                            throw $e;
                        }
                    }
                }

                // Refresh user to get all roles
                $user = $user->fresh();

                // Create SignalWire SIP endpoint if user has mandown role
                if ($user->hasMandownRole()) {
                    $primaryEmail = $user->getPrimaryEmail();

                    if (empty($primaryEmail)) {
                        \Log::error('Cannot create SIP endpoint: user has no email address', [
                            'user_id' => $user->user_id
                        ]);
                    } else {
                        $signalWireService = new \App\Services\SignalWireService();
                        if (!$signalWireService->createSipEndpoint($user, $request->password)) {
                            \Log::error('Failed to create SIP endpoint for new user with mandown role', [
                                'user_id' => $user->user_id
                            ]);
                        }
                    }
                }

                $invitedUser->delete();
                DB::commit();
            } catch(\Throwable $e){
                DB::rollback();
                \Log::error('Failed to accept invitation', ['error' => $e->getMessage()]);
            }
        }
        app(\App\Services\UserContextService::class)->logoutActiveUser();
        return redirect(route('login'));
    }

}
