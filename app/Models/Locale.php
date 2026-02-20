<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Locale
 * 
 * @property int $locale_id
 * @property int|null $locale_parent_id
 * @property int $locale_language_id
 * @property int $locale_country_id
 * @property string $locale_code
 * 
 * @property Country $country
 * @property Language $language
 * @property Locale|null $locale
 * @property Collection|Language[] $languages
 * @property Collection|Locale[] $locales
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Locale extends Model
{
	protected $table = 'locales';
	protected $primaryKey = 'locale_id';
	public $timestamps = false;

	protected $casts = [
		'locale_parent_id' => 'int',
		'locale_language_id' => 'int',
		'locale_country_id' => 'int'
	];

	protected $fillable = [
		'locale_parent_id',
		'locale_language_id',
		'locale_country_id',
		'locale_code'
	];

	public function country()
	{
		return $this->belongsTo(Country::class, 'locale_country_id');
	}

	public function language()
	{
		return $this->belongsTo(Language::class, 'locale_language_id');
	}

	public function locale()
	{
		return $this->belongsTo(Locale::class, 'locale_parent_id');
	}

	public function languages()
	{
		return $this->hasMany(Language::class, 'language_default_id');
	}

	public function locales()
	{
		return $this->hasMany(Locale::class, 'locale_parent_id');
	}

	public function users()
	{
		return $this->hasMany(User::class, 'user_locale_id');
	}
}
