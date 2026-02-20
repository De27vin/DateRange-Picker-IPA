<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Exceptions\UcpException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Searchable\Searchable;
use App\Searchable\SearchResult;

/**
 * Class Address
 * 
 * @property int $address_id
 * @property int $address_location_id
 * @property string|null $address_value
 * @property string|null $address_lat
 * @property string|null $address_lon
 * 
 * @property Location $location
 * @property Collection|Account[] $accounts
 * @property Collection|Device[] $devices
 * @property Collection|DeviceSite[] $deviceSites
 *
 * @package App\Models
 */
class Address extends Model implements Searchable
{
	protected $table = 'addresses';
	protected $primaryKey = 'address_id';
	public $timestamps = false;

	protected $casts = [
		'address_location_id' => 'int'
	];
    protected $appends = ['in_one_line'];

	protected $fillable = [
		'address_location_id',
		'address_value',
		'address_lat',
		'address_lon'
	];

	public function location()
	{
		return $this->belongsTo(Location::class, 'address_location_id');
	}

	public function accounts()
	{
		return $this->hasMany(Account::class, 'account_address_id');
	}

//	public function devices()
//	{
//		return $this->hasMany(Device::class, 'device_address_id');
//	}

    public function device_sites()
    {
        return $this->hasMany(DeviceSite::class, 'ds_address_id');
    }

    public function getInOneLineAttribute()
    {
        $addressParts = [];

        if (!empty($this->address_value)) {
            $addressParts[] = $this->address_value;
        }
        if (!empty($this->location?->location_postcode)) {
            $addressParts[] = $this->location->location_postcode;
        }
        if (!empty($this->location?->location_value)) {
            $addressParts[] = $this->location->location_value;
        }
        if (!empty($this->location?->country?->name)) {
            $addressParts[] = $this->location->country->name;
        }

        return !empty($addressParts) ? implode(', ', $addressParts) : null;
    }

    public function inOneLine()
    {
        if(!empty($this->address_value)){
            return $this->address_value . ', ' . $this->location?->location_postcode . ' ' . $this->location?->location_value . ' (' . $this->location?->country?->name . ')';
        } else {
            return null;
        }
    }

    public function getSearchResult(): SearchResult
    {
        return new \App\Searchable\SearchResult(
            $this,
            $this->address_value ?? '', // TODO: null coalescing was not needed for dashboard - also dashboard alarm filtering works faster - check this for performance
            // TODO: another revelation - alert filters works much faster also on equipment but on all tab - works slow on active tab however - check this for performance
        );
    }

    public static function addData(string $address, null|string|int $locationId, bool $save = true)
    {
        $firstOrAction = $save ? 'firstOrCreate' : 'firstOrNew';

        return Address::{$firstOrAction}([
            'address_value' => trim($address),
            'address_location_id' => $locationId,
        ]);
    }
}
