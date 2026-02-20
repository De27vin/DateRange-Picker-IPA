<?php

namespace App\Models;

use App\Notifications\CustomResetPassword;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements CanResetPassword
{
    use Notifiable;
	use HasApiTokens;
    use SoftDeletes;

	protected $table = 'users';
	protected $primaryKey = 'user_id';
	protected $appends = ['name', 'hasLogin', 'isAgent', 'isMobile', 'isUser', 'isAdmin', 'isSite'];

	// public $timestamps = false;
	public $with = ['emails','locale', 'roles'];
	public $prefix = 'user_';

    protected $rememberTokenName = 'user_remember_token';

    const CREATED_AT = 'user_created';
    const UPDATED_AT = 'user_modified';
    const DELETED_AT = 'user_deleted';

	protected $casts = [
		'user_gender_id' => 'int',
		'user_locale_id' => 'int',
		'user_created' => 'datetime',
		'user_modified' => 'datetime',
		'user_lastpw' => 'datetime',
		'user_lastlogin' => 'datetime',
		'user_logins' => 'int'
	];

	protected $hidden = [
		'user_password'
	];

	protected $fillable = [
		'user_gender_id',
		'user_locale_id',
		'user_timezone',
		'user_password',
		'user_firstname',
		'user_lastname',
		'user_ext',
		'user_created',
		'user_modified',
		'user_lastpw',
		'user_lastlogin',
		'user_lastip',
		'user_logins',
		'remember_token'
	];

    protected string $emailForPasswordReset;

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }

    public function setEmailForPasswordReset(string $email): void
    {
        $this->emailForPasswordReset = $email;
    }

    public function getEmailForPasswordReset(): ?string
    {
        if (!isset($this->emailForPasswordReset)) {
            return null;
        }

        return $this->emailForPasswordReset;
    }

    public function getEmailAttribute()
    {
        return $this->getEmailForPasswordReset();
    }

    public function gender()
	{
		return $this->belongsTo(Gender::class, 'user_gender_id');
	}

	public function locale()
	{
		return $this->belongsTo(Locale::class, 'user_locale_id');
	}

	public function device_comments()
	{
		return $this->hasMany(DeviceComment::class, 'dc_user_id');
	}

	public function device_labels()
	{
		return $this->belongsToMany(DeviceLabelOld::class, 'device_labels_users', 'dlu_user_id', 'dlu_dl_id');
	}

	public function emails()
	{
		return $this->hasMany(Email::class, 'email_user_id', 'user_id');
	}

	public function settings()
	{
		return $this->belongsToMany(Setting::class, 'user_settings', 'us_user_id', 'us_setting_id')
					->withPivot('us_id', 'us_value');
	}

	public function user_tokens()
	{
		return $this->hasMany(UserToken::class, 'ut_user_id');
	}

	public function roles()
	{
		return $this->belongsToMany(Role::class, 'users_roles', 'ur_user_id', 'ur_role_id')
					->withPivot('ur_account_id');
	}

	public function getPasswordAttribute()
	{
		return $this->getAttribute('user_password');
	}

	public function getNameAttribute()
	{
		return $this->getAttribute('user_firstname') . ' ' . $this->getAttribute('user_lastname');
	}
	
	public function getFirstNameAttribute()
	{
	    return $this->getAttribute('user_firstname');
	}
	
	public function getTimezoneAttribute()
	{
	    return $this->getAttribute('user_timezone');
	}

	public function getLastNameAttribute()
	{
	    return $this->getAttribute('user_lastname');
	}


	public function getIsSiteAttribute()
	{
		return !empty($this->roles->whereIn('role_type', ['site'])->first());
	}

	public function getIsAdminAttribute()
	{
		return !empty($this->roles->whereIn('role_type', ['admin', 'site'])->first());
	}

	public function getIsAgentAttribute()
	{
		return !empty($this->roles->whereIn('role_type', ['agent', 'site'])->first());
	}

	public function getIsMobileAttribute()
	{
		return !empty($this->roles->whereIn('role_type', ['mobile', 'site'])->first());
	}

	public function getIsUserAttribute()
	{
		return !empty($this->roles->whereIn('role_type', ['user', 'admin', 'site'])->first());
	}

	public function getHasLoginAttribute()
	{
		return !empty($this->roles->where('role_type', 'login')->first());
	}

	public function getAccountsAttribute()
	{
		$accountIds = $this->getAccountIds();
		return Account::select('account_id','account_name','account_translation','account_slug','account_enabled')->whereIn('account_id',$accountIds)->enabled()->get();
	}

	public function getAccountAttribute()
	{
		if(session('account.id') == null){
			return Redirect::to('/logout');
		} else {
			return Account::select('account_id','account_name','account_slug','account_enabled')->findOrFail(session('account.id'));
		}
	}

	public function hasRole($role)
	{
		return !empty($this->roles->where('role_type', $role)->first());
	}

	public function getAccountIds()
	{
		if( Auth::user()->hasRole('site') ){
			$accounts = Account::query()->where('account_enabled','=',1)->pluck('account_id')->toArray();
			return $accounts;
		} else {
	        $accounts = Auth::user()->roles->mapWithKeys(function ($item, $key) {
	            return [$item->pivot->ur_account_id => $item];
	        })->toArray();
	        return Arr::flatten(array_filter(array_keys($accounts)));
	    }
	}

	public static function guessUserTimezoneUsingAPI($ip)
	{
	    $ip = Http::get('https://ipecho.net/'. $ip .'/json');
	    if ($ip->json('timezone')) {
	        return $ip->json('timezone');
	    }
	    return null;
	}

	public function getRelatedUserIdsAttribute()
	{
		// dd(session()->get('account'));
		if($accountId = session()->get('account.id')){
			return UsersRole::where('ur_account_id','=',$accountId)->distinct()->select('ur_user_id')->pluck('ur_user_id')->toArray();
		} else {
			return [];
		}
	}

	public function getRelatedUsersAttribute()
	{
		return $this->whereIntegerInRaw('user_id',$this->relatedUserIds)->get();
	}

	public function scopeRelatedUsers($query)
	{
		return $query->whereIntegerInRaw('user_id',$this->relatedUserIds);
	}

    public function updateLoginStats(string $loginIp)
    {
        $this->user_lastlogin = time();
        $this->user_lastip = $loginIp;
        $this->user_logins = $this->user_logins + 1;
        $this->save();
    }
}
