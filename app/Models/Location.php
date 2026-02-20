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
 * Class Location
 * 
 * @property int $location_id
 * @property int $location_country_id
 * @property string $location_value
 * @property string|null $location_postcode
 * @property string|null $location_lat
 * @property string|null $location_lon
 * 
 * @property Country $country
 * @property Collection|Address[] $addresses
 *
 * @package App\Models
 */
class Location extends Model implements Searchable
{
	protected $table = 'locations';
	protected $primaryKey = 'location_id';
	public $timestamps = false;

	protected $casts = [
		'location_country_id' => 'int'
	];

	protected $fillable = [
		'location_country_id',
		'location_value',
		'location_postcode',
		'location_lat',
		'location_lon'
	];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'location_country_id', 'country_id');
    }

	public function addresses()
	{
		return $this->hasMany(Address::class, 'address_location_id');
	}

    public function getSearchResult(): SearchResult
    {
        return new \App\Searchable\SearchResult(
            $this,
            $this->location_postcode,
            $this->location_value,
        );
    }

    public static function addData(string $location, string $postcode, string|int $countryId, bool $save = true)
    {
        $firstOrAction = $save ? 'firstOrCreate' : 'firstOrNew';

        return Location::{$firstOrAction}([
            'location_value' => trim($location),
            'location_postcode' => trim($postcode),
            'location_country_id' => trim($countryId),
        ]);
    }
}
