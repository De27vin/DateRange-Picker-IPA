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
                Auth::logout();
                return redirect(route('login'));
            }
            return view('users.create',[
                'token' => $request->token
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
                    DB::table('users_roles')->insert([
                        'ur_user_id' => $user->user_id,
                        'ur_role_id' => $role->role_id,
                        'ur_account_id' => $account->account_id
                    ]);
                }

                $invitedUser->delete();
                DB::commit();
            } catch(\Throwable $e){
                DB::rollback();
            }
        }
        Auth::logout();
        return redirect(route('login'));
    }

}
