<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventAB extends Model
{
    use SoftDeletes;

    protected $fillable = ['event_id', 'ab_id', 'currency_id', 'iss_percent', 'service_percent', 'iva_percent', 'invoice', 'internal_observation', 'customer_observation', 'sended_mail_link', 'token_budget'];
    protected $table = 'event_ab';


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
    protected $ab_id = 'ab_id';


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


    public function event()
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }

    public function ab()
    {
        return $this->hasOne(Provider::class,  'id', 'ab_id');
    }

    public function currency()
    {
        return $this->hasOne(Currency::class,  'id', 'currency_id');
    }

    public function eventAbOpts()
    {
        return $this->hasMany(EventABOpt::class, 'event_ab_id', 'id');
    }

    public function providerBudget()
    {
        return $this->hasMany(ProviderBudget::class, 'event_ab_id', 'id');
    }
}
