<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Regime extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];
    protected $table = 'regime';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $id = 'id';



    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtoupper($value);
    }

    public function event_hotels_opt()
    {
        return $this->belongsTo(EventHotelOpt::class);
    }
}
