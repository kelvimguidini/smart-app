<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventAirfare extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'airfare_id',
        'currency_id',
        'iss_percent',
        'service_percent',
        'iva_percent',
        'iof',
        'taxa_4bts',
        'service_charge',
        'invoice',
        'internal_observation',
        'customer_observation',
        'sended_mail',
        'sended_mail_link',
        'token_budget',
        'deadline_date',
        'payment_method',
        'order'
    ];

    protected $table = 'event_airfare';

    protected $id = 'id';
    protected $event_id = 'event_id';
    protected $airfare_id = 'airfare_id';
    protected $currency_id = 'currency_id';
    protected $iss_percent = 'iss_percent';
    protected $service_percent = 'service_percent';
    protected $iva_percent = 'iva_percent';
    protected $iof = 'iof';
    protected $taxa_4bts = 'taxa_4bts';
    protected $service_charge = 'service_charge';
    protected $invoice = 'invoice';
    protected $internal_observation = 'internal_observation';
    protected $customer_observation = 'customer_observation';
    protected $sended_mail = 'sended_mail';
    protected $sended_mail_link = 'sended_mail_link';
    protected $token_budget = 'token_budget';
    protected $deadline_date = 'deadline_date';
    protected $payment_method = 'payment_method';
    protected $order = 'order';

    public function event()
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }

    public function provider()
    {
        return $this->hasOne(ProviderAirfare::class, 'id', 'airfare_id');
    }

    public function currency()
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id');
    }

    public function eventAirfareOpts()
    {
        return $this->hasMany(EventAirfareOpt::class, 'event_airfare_id', 'id');
    }

    public function passengers()
    {
        return $this->hasMany(EventAirfarePassenger::class, 'event_airfare_id', 'id');
    }

    public function status_his()
    {
        return $this->hasMany(StatusHistory::class, 'table_id', 'id')
            ->where('table', 'event_airfares')
            ->orderByDesc('created_at');
    }
}
