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
 * Class DeviceLabelGroup
 *
 * @property int $dlg_id
 * @property int $dlg_account_id
 * @property string $dlg_name
 * @property int $dlg_order
 *
 * @property Collection|DeviceLabel[] $labels
 * @property Account $account
 *
 * @package App\Models
 */
class DeviceLabelGroup extends Model implements Searchable
{
	protected $table = 'device_label_groups';
    protected $primaryKey = 'dlg_id';
    public $timestamps = false;


	protected $casts = [
		'dlg_id' => 'int',
		'dlg_account_id' => 'int',
		'dlg_name' => 'string',
		'dlg_order' => 'int',
	];

	public function labels()
	{
		return $this->hasMany(DeviceLabel::class, 'dl_dlg_id');
	}

	public function account()
	{
		return $this->belongsTo(Account::class, 'dlg_account_id');
	}

    public function getSearchResult(): SearchResult
    {
        return new SearchResult(
            $this,
            $this->dlg_name
        );
    }
}
