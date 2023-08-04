<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventStatus extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'event_id',

        'observation_hotel',
        'observation_transport',

        'request_hotel',
        'provider_order_hotel',
        'briefing_hotel',
        'response_hotel',
        'pricing_hotel',
        'custumer_send_hotel',
        'change_hotel',
        'done_hotel',
        'cancelment_hotel',
        'aproved_hotel',
        'status_u_hotel',

        'status_hotel',

        'request_transport',
        'provider_order_transport',
        'briefing_transport',
        'response_transport',
        'pricing_transport',
        'custumer_send_transport',
        'change_transport',
        'done_transport',
        'cancelment_transport',

        'status_transport',
        'aproved_transport',
        'status_u_transport'
    ];
    protected $table = 'event_status';

    public function event()
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }
}
