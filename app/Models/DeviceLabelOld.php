<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

/**
 * @deprecated
 * Class DeviceLabel
 * 
 * @property int $dl_id
 * @property int|null $dl_parent_id
 * @property int $dl_account_id
 * @property string $dl_name
 * @property int|null $dl_group_1
 * @property int|null $dl_group_2
 * @property int|null $dl_group_3
 * @property int|null $dl_group_4
 * @property int|null $dl_group_5
 * @property int|null $dl_group_6
 * @property int|null $dl_depth
 * @property string|null $dl_tree
 * @property int|null $dl_left
 * @property int|null $dl_right
 * 
 * @property Account $account
 * @property DeviceLabelOld|null $device_label
 * @property Collection|Setting[] $settings
 * @property Collection|DeviceLabelOld[] $device_labels
 * @property Collection|Device[] $devices
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class DeviceLabelOld extends Model
{
    use NodeTrait;

	protected $table = 'device_labels';
	protected $primaryKey = 'dl_id';
	public $timestamps = false;

	protected $casts = [
		'dl_parent_id' => 'int',
		'dl_account_id' => 'int',
		'dl_group_1' => 'int',
		'dl_group_2' => 'int',
		'dl_group_3' => 'int',
		'dl_group_4' => 'int',
		'dl_group_5' => 'int',
		'dl_group_6' => 'int',
		'dl_depth' => 'int',
		'dl_left' => 'int',
		'dl_right' => 'int'
	];

	protected $fillable = [
		'dl_parent_id',
		'dl_account_id',
		'dl_name',
		'dl_group_1',
		'dl_group_2',
		'dl_group_3',
		'dl_group_4',
		'dl_group_5',
		'dl_group_6',
		'dl_depth',
		'dl_tree',
		'dl_left',
		'dl_right'
	];
	
    /**
     * The name of default lft column.
     */
    const LFT = 'dl_left';

    /**
     * The name of default rgt column.
     */
    const RGT = 'dl_right';

    /**
     * The name of default parent id column.
     */
    const PARENT_ID = 'dl_parent_id';


    public function getLftName()
    {
        return 'dl_left';
    }

    public function getRgtName()
    {
        return 'dl_right';
    }

    public function getParentIdName()
    {
        return 'dl_parent_id';
    }

    // Specify parent id attribute mutator
    public function setDlParentIdAttribute($value)
    {
        $this->setParentIdAttribute($value);
    }

	public function account()
	{
		return $this->belongsTo(Account::class, 'dl_account_id');
	}

	public function device_label()
	{
		return $this->belongsTo(DeviceLabelOld::class, 'dl_parent_id');
	}

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function device_label_settings()
    {
        return $this->hasMany('App\Models\DeviceLabelSetting', 'dls_dl_id', 'dl_id');
    }

	public function device_labels()
	{
		return $this->hasMany(DeviceLabelOld::class, 'dl_parent_id');
	}

	public function devices()
	{
		return $this->belongsToMany(Device::class, 'device_labels_devices', 'dld_dl_id', 'dld_device_id');
	}

	public function users()
	{
		return $this->belongsToMany(User::class, 'device_labels_users', 'dlu_dl_id', 'dlu_user_id');
	}
}
