<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventAirfarePassenger extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_airfare_id',
        'name',
        'document',
        'passport_validity',
        'birth_date',
        'outbound_date',
        'outbound_origin',
        'outbound_destination',
        'outbound_departure',
        'outbound_arrival',
        'inbound_date',
        'inbound_origin',
        'inbound_destination',
        'inbound_departure',
        'inbound_arrival'
    ];

    protected $table = 'event_airfare_passengers';

    protected $id = 'id';

    public function event_airfare()
    {
        return $this->belongsTo(EventAirfare::class, 'event_airfare_id', 'id');
    }
}
