<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Country
 * 
 * @property int $country_id
 * @property string $country_iso
 * 
 * @property Collection|AccountsCountry[] $accounts_countries
 * @property Collection|Locale[] $locales
 * @property Collection|Location[] $locations
 *
 * @package App\Models
 */
class Country extends Model
{
	protected $table = 'countries';
	protected $primaryKey = 'country_id';
	public $timestamps = false;

    protected $appends = ['name'];

	protected $fillable = [
		'country_iso'
	];

	public function accounts_countries()
	{
		return $this->hasMany(AccountsCountry::class, 'ac_country_id');
	}

	public function locales()
	{
		return $this->hasMany(Locale::class, 'locale_country_id');
	}

	public function locations()
	{
		return $this->hasMany(Location::class, 'location_country_id');
	}

    public function getNameAttribute()
    {
        $locale = session('locale', 'en');
        return locale_get_display_region('-'.$this->country_iso,$locale);
    }

}
