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
        'outbound_class',
        'outbound_date',
        'outbound_origin',
        'outbound_destination',
        'outbound_departure_time',
        'outbound_arrival_time',
        'outbound_connection_details',
        'inbound_airline_id',
        'inbound_flight_number',
        'inbound_class',
        'inbound_date',
        'inbound_origin',
        'inbound_destination',
        'inbound_departure_time',
        'inbound_arrival_time',
        'inbound_connection_details',
        'currency_id',
        'received_proposal',
        'received_proposal_percent',
        'kickback',
        'compare_website',
        'compare_client',
        'count',
        'baggage_id',
        'cabin_id',
        'status',
        'observation',
        'order'
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

    public function inbound_airline()
    {
        return $this->hasOne(AirfareAirline::class, 'id', 'inbound_airline_id');
    }

    public function currency()
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id');
    }

    public function baggage()
    {
        return $this->hasOne(AirfareBaggage::class, 'id', 'baggage_id');
    }

    public function cabin()
    {
        return $this->hasOne(AirfareCabin::class, 'id', 'cabin_id');
    }
}
