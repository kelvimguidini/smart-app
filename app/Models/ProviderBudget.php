<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ProviderBudget extends Model
{
    use SoftDeletes;

    protected $table = 'provider_budget';

    protected $fillable = [
        'hosting_fee_hotel',
        'iss_fee_hotel',
        'iva_fee_hotel',
        'comment_hotel',
        'event_hotel_id',

        'hosting_fee_ab',
        'iss_fee_ab',
        'iva_fee_ab',
        'comment_ab',
        'event_ab_id',

        'hosting_fee_add',
        'iss_fee_add',
        'iva_fee_add',
        'comment_add',
        'event_add_id',

        'hosting_fee_hall',
        'iss_fee_hall',
        'iva_fee_hall',
        'comment_hall',
        'event_hall_id',


        'hosting_fee_transport',
        'iss_fee_transport',
        'iva_fee_transport',
        'comment_transport',
        'event_transport_id',

        'evaluated',
        'approved'
    ];

    public function eventHotel()
    {
        return $this->belongsTo(EventHotel::class);
    }

    public function eventAb()
    {
        return $this->belongsTo(EventAb::class);
    }

    public function eventAdd()
    {
        return $this->belongsTo(EventAdd::class);
    }

    public function eventHall()
    {
        return $this->belongsTo(EventHall::class);
    }

    public function eventTransport()
    {
        return $this->belongsTo(EventTransport::class);
    }

    public function providerBudgetItems()
    {
        return $this->hasMany(ProviderBudgetItem::class);
    }

    public function user()
    {
        return $this->hasOne(User::class,  'id', 'user_id');
    }
}
