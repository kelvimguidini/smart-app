<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class BrokerTransport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'city_id',
        'contact',
        'phone',
        'email',
        'national'
    ];
    protected $table = 'broker_transport';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $id = 'id';


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $name = 'name';


    public function event_transports_opt()
    {
        return $this->belongsTo(EventTransportOpt::class);
    }

    public function city()
    {
        return $this->hasOne(City::class,  'id', 'city_id');
    }
}
