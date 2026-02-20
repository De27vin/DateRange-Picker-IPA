<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Searchable\Searchable;
use App\Searchable\SearchResult;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DeviceLabel
 *
 * @property int $dl_id
 * @property int $dl_account_id
 * @property int $dl_dlg_id
 * @property string $dl_name
 * @property int $dl_order
 *
 * @property Account $account
 * @property DeviceLabelGroup $group
 * @property Collection|Setting[] $settings
 * @property Collection|DeviceSite[] $device_sites
 *
 * @package App\Models
 */
class DeviceLabel extends Model implements Searchable
{
	protected $table = 'device_labels';
	protected $primaryKey = 'dl_id';
    public $timestamps = false;

    protected $casts = [
        'dl_account_id' => 'int',
        'dl_dlg_id' => 'int',
        'dl_name' => 'string',
        'dl_order' => 'int',
    ];

    protected $fillable = [
		'dl_account_id',
		'dl_dlg_id',
		'dl_name',
		'dl_order',
	];


	public function account()
	{
		return $this->belongsTo(Account::class, 'dl_account_id');
	}

    public function group()
	{
		return $this->belongsTo(DeviceLabelGroup::class, 'dl_dlg_id');
	}

    public function device_label_settings()
    {
        return $this->hasMany(DeviceLabelSetting::class, 'dls_dl_id', 'dl_id');
    }

	public function device_sites()
	{
		return $this->belongsToMany(DeviceSite::class, 'device_labels_sites', 'dld_dl_id', 'dld_ds_id');
	}

    public function getSearchResult(): SearchResult
    {
        return new SearchResult(
            $this,
            $this->dl_name
        );
    }
}
