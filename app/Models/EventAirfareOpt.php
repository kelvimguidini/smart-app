<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventAirfareOpt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_airfare_id',
        'outbound_airline_id',
        'outbound_flight_number',
        'outbound_date',
        'outbound_origin',
        'outbound_destination',
        'outbound_departure_time',
        'outbound_arrival_time',
    ];

    protected $table = 'event_airfare_opt';

    protected $id = 'id';

    public function event_airfare()
    {
        return $this->belongsTo(EventAirfare::class, 'event_airfare_id', 'id');
    }

    public function outbound_airline()
    {
        return $this->hasOne(AirfareAirline::class, 'id', 'outbound_airline_id');
    }
}
