<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class Language
 * 
 * @property int $language_id
 * @property int|null $language_default_id
 * @property string $language_code
 * @property int $language_enabled
 * 
 * @property Locale|null $locale
 * @property Collection|Locale[] $locales
 * @property Collection|Route[] $routes
 *
 * @package App\Models
 */
class Language extends Model
{
	protected $table = 'languages';
	protected $primaryKey = 'language_id';
	public $timestamps = false;
	public $with = ['locale'];

	protected $casts = [
		'language_default_id' => 'int',
		'language_enabled' => 'boolean'
	];

	protected $fillable = [
		'language_default_id',
		'language_code',
		'language_enabled'
	];
	protected $appends = ['flag'];

	public function locale()
	{
		return $this->belongsTo(Locale::class, 'language_default_id');
	}

	public function locales()
	{
		return $this->hasMany(Locale::class, 'locale_language_id');
	}

	public function routes()
	{
		return $this->hasMany(Route::class, 'route_language_id');
	}

	public function getFlagAttribute()
	{
		return strtolower( Str::after($this->locale?->locale_code, '_') ?: $this->language_code );
	}

}
