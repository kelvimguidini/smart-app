<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class AptoHotel extends Model
{
    // use SoftDeletes;

    protected $table = 'apto_hotel';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $id = 'id';


    public function hotel()
    {
        return $this->hasOne(Provider::class, 'id', 'hotel_id');
    }


    public function apto()
    {
        // return $this->belongsToMany(Apto::class);
        return $this->hasOne(Apto::class, 'id', 'apto_id');
    }
}
