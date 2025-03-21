<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventAdd extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'add_id',
        'currency_id',
        'iss_percent',
        'service_percent',
        'iva_percent',
        'invoice',
        'internal_observation',
        'customer_observation',
        'sended_mail_link',
        'token_budget',
        'iof',
        'taxa_4bts',
        'service_charge',
        'deadline_date',
        'order'
    ];
    protected $table = 'event_add';


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
    protected $add_id = 'add_id';


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

    public function add()
    {
        return $this->hasOne(ProviderServices::class,  'id', 'add_id');
    }

    public function currency()
    {
        return $this->hasOne(Currency::class,  'id', 'currency_id');
    }

    public function eventAddOpts()
    {
        return $this->hasMany(EventAddOpt::class, 'event_add_id', 'id');
    }

    public function providerBudget()
    {
        return $this->hasMany(ProviderBudget::class, 'event_add_id', 'id');
    }

    public function status_his()
    {
        return $this->hasMany(StatusHistory::class, 'table_id', 'id')
            ->where('table', 'event_adds')
            ->orderByDesc('created_at');
    }
}
