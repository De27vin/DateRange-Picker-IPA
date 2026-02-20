<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Searchable\Searchable;
use App\Searchable\SearchResult;
use App\Exceptions\UcpException;

/**
 * Class DeviceComment
 * 
 * @property int $dc_id
 * @property int|null $dc_device_id
 * @property int|null $dc_user_id
 * @property int|null $dc_session_id
 * @property string|null $dc_text
 * @property string|null $dc_link
 * @property Carbon|null $dc_created
 * 
 * @property Device|null $device
 * @property User|null $user
 * @property Session|null $session
 *
 * @package App\Models
 */
class DeviceComment extends Model implements Searchable
{
	protected $table = 'device_comments';
	protected $primaryKey = 'dc_id';
	public $timestamps = false;
	protected $appends = ['author'];
	protected $casts = [
		'dc_device_id' => 'int',
		'dc_user_id' => 'int',
		'dc_created' => 'datetime'
	];

	protected $fillable = [
		'dc_session_id',
		'dc_device_id',
		'dc_user_id',
		'dc_text',
		'dc_link',
		'dc_created'
	];

	public function device()
	{
		return $this->belongsTo(Device::class, 'dc_device_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'dc_user_id');
	}

    public function session()
	{
		return $this->belongsTo(Session::class, 'dc_session_id');
	}

	public function getAuthorAttribute()
	{
		$name = '';
		if(!is_null($this->dc_user_id)){
			$user = User::where('user_id','=',$this->dc_user_id)->first();
			if(!is_null($user)){
				$name = $user->name;
			}
		}
		return $name;
	}

    public function getSearchResult(): SearchResult
    {
        return new \App\Searchable\SearchResult(
            $this,
            $this->dc_id,
        );
    }

    /** @deprecated every usage of this function should be checked and removed */
	public function addData(Array $data = null)
	{
		if($data == null){
			return null;
		}
		try{
			$this->dc_device_id = $data['device_id'];
			$this->dc_user_id = $data['user_id'];
			$this->dc_text = $data['text'];
			$this->dc_link = $data['link'];
			$this->save();
			return $this;
        } catch(\Throwable $e){
            session()->flash('error','storing data for device failed');
        }
	}

}
