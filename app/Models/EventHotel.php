<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventHotel extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id', 'hotel_id', 'currency_id', 'iss_percent', 'service_percent', 'iva_percent', 'invoice', 'internal_observation', 'customer_observation', 'sended_mail_link', 'token_budget',
        'iof',
        'service_charge'
    ];
    protected $table = 'event_hotel';


    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $id = 'id';


    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $event_id = 'event_id';


    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $hotel_id = 'hotel_id';


    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $currency_id = 'currency_id';
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $iss_percent = 'iss_percent';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $service_percent = 'service_percent';


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $iva_percent = 'iva_percent';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $invoice = 'invoice';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $internal_observation = 'internal_observation';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $customer_observation = 'customer_observation';


    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $sended_mail_link = 'sended_mail_link';


    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $token_budget = 'token_budget';

    public function event()
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }

    public function hotel()
    {
        return $this->hasOne(Provider::class,  'id', 'hotel_id');
    }

    public function currency()
    {
        return $this->hasOne(Currency::class,  'id', 'currency_id');
    }

    public function eventHotelsOpt()
    {
        return $this->hasMany(EventHotelOpt::class);
    }

    public function providerBudget()
    {
        return $this->hasMany(ProviderBudget::class, 'event_hotel_id', 'id');
    }
}
