<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Exceptions\UcpException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Searchable\Searchable;
use App\Searchable\SearchResult;

/**
 * Class Number
 * 
 * @property int $number_id
 * @property int $number_nt_id
 * @property int $number_account_id
 * @property int|null $number_ds_id
 * @property string $number_value
 * @property Carbon $number_created
 * 
 * @property DeviceSite|null $device_site
 * @property Number $number
 * @property Collection|Number[] $numbers
 *
 * @package App\Models
 */
class Number extends Model implements Searchable
{
	protected $table      = 'numbers';
	protected $primaryKey = 'number_id';
	protected $with       = ['number_type'];
	public $timestamps    = false;

	protected $casts = [
		'number_nt_id' => 'int',
		'number_ds_id' => 'int',
		'number_created' => 'datetime'
	];

	protected $fillable = [
		'number_account_id',
		'number_nt_id',
		'number_ds_id',
		'number_value',
		'number_created'
	];

	public function device_site()
	{
		return $this->belongsTo(DeviceSite::class, 'number_ds_id');
	}

	public function number_type()
	{
		return $this->belongsTo(NumberType::class, 'number_nt_id');
	}


    public function scopePstn($query)
    {
        return $query->whereHas('number_type', function($q){
        	$q->where('number_types.nt_type', '=', 'pstn');
        });
    }

    public function scopePbx($query)
    {
        return $query->whereHas('number_type', function($q){
        	$q->where('number_types.nt_type', '=', 'pbx');
        });
    }

    public function scopeSim($query)
    {
        return $query->whereHas('number_type', function($q){
        	$q->where('number_types.nt_type', '=', 'sim');
        });
    }

    public function scopeSip($query)
    {
        return $query->whereHas('number_type', function($q){
        	$q->where('number_types.nt_type', '=', 'sip');
        });
    }

    public function getIdAttribute()
    {
        return $this->number_id;
    }

    public function getNameAttribute()
    {
        return $this->number_value;
    }

    public function addData($data)
    {
        try{
            $number = Number::query()
                ->where('number_nt_id','=',$data['type'])
                ->where('number_ds_id','=',$data['site_id'])
                ->where('number_value','=',$data['number'])
                ->first();

            if ($number == null) {
                $this->number_ds_id = $data['site_id'];
                $this->number_nt_id = $data['type'];
                $this->number_value = $data['number'];
                $this->save();
                return $this;
            } else {
                return $number;
            }
        } catch(\Throwable $e){
            session()->flash('error','storing data for number failed');
        }
    }

    public function getSearchResult(): SearchResult
    {
        return new \App\Searchable\SearchResult(
            $this,
            $this->number_value,
        );
    }
}
